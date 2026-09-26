<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\WebhookEvent;
use App\Services\Payments\PaymentService;
use App\Services\Payments\PayPalClient;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use RuntimeException;

/**
 * Verified, idempotent handling of PayPal webhook events. This is the only
 * place a subscription may be activated for the PayPal driver — the browser
 * redirect after approval is never trusted on its own (spec section 11: "Do
 * not trust payment-success information sent only from the browser").
 *
 * Untestable without a real PayPal developer account and a way to receive
 * webhooks (e.g. ngrok forwarding to this URL configured against a sandbox
 * app), so this is a known limitation until real credentials are configured
 * — see PayPalGateway.
 */
class PayPalWebhookController extends Controller
{
    public function handle(Request $request, PayPalClient $client, PaymentService $payments): Response
    {
        $webhookId = config('services.paypal.webhook_id');

        if (! $webhookId) {
            report(new RuntimeException('Received a PayPal webhook but PAYPAL_WEBHOOK_ID is not configured.'));

            return response('Webhook ID not configured', 500);
        }

        $payload = $request->json()->all();
        $eventId = $payload['id'] ?? null;

        if (! $eventId) {
            return response('Missing event id', 400);
        }

        try {
            $verification = $client->http()->post('/v1/notifications/verify-webhook-signature', [
                'transmission_id' => $request->header('Paypal-Transmission-Id'),
                'transmission_time' => $request->header('Paypal-Transmission-Time'),
                'cert_url' => $request->header('Paypal-Cert-Url'),
                'auth_algo' => $request->header('Paypal-Auth-Algo'),
                'transmission_sig' => $request->header('Paypal-Transmission-Sig'),
                'webhook_id' => $webhookId,
                'webhook_event' => $payload,
            ])->throw()->json();
        } catch (\Throwable $e) {
            report($e);

            return response('Signature verification failed', 400);
        }

        if (($verification['verification_status'] ?? null) !== 'SUCCESS') {
            report(new RuntimeException("PayPal webhook signature verification failed for event {$eventId}."));

            return response('Invalid signature', 400);
        }

        // Idempotency: a redelivered event is a no-op (spec section 11).
        $webhookEvent = WebhookEvent::firstOrCreate(
            ['gateway' => 'paypal', 'event_id' => $eventId],
            ['type' => $payload['event_type'] ?? 'unknown', 'payload' => $payload]
        );

        if ($webhookEvent->processed_at) {
            return response('Already processed', 200);
        }

        match ($payload['event_type'] ?? null) {
            'PAYMENT.SALE.COMPLETED' => $this->handleSaleCompleted($payload, $payments),
            'BILLING.SUBSCRIPTION.ACTIVATED' => $this->handleSubscriptionActivated($payload, $payments),
            'BILLING.SUBSCRIPTION.PAYMENT.FAILED', 'PAYMENT.SALE.DENIED' => $this->handlePaymentFailed($payload, $payments),
            default => null,
        };

        $webhookEvent->update(['processed_at' => now()]);

        return response('OK', 200);
    }

    protected function handleSaleCompleted(array $payload, PaymentService $payments): void
    {
        $resource = $payload['resource'] ?? [];
        $billingAgreementId = $resource['billing_agreement_id'] ?? null;

        if (! $billingAgreementId) {
            return;
        }

        $transaction = $this->pendingTransactionForSubscription($billingAgreementId);

        if (! $transaction) {
            return;
        }

        $payments->confirmPayment($transaction, succeeded: true, gatewayTransactionId: $resource['id'] ?? null);
    }

    protected function handleSubscriptionActivated(array $payload, PaymentService $payments): void
    {
        $resource = $payload['resource'] ?? [];
        $transactionId = $resource['custom_id'] ?? null;
        $transaction = $transactionId ? Transaction::find($transactionId) : null;

        if (! $transaction && isset($resource['id'])) {
            $transaction = $this->pendingTransactionForSubscription($resource['id']);
        }

        if (! $transaction || $transaction->status !== Transaction::STATUS_PENDING) {
            return;
        }

        $payments->confirmPayment($transaction, succeeded: true, gatewayTransactionId: $resource['id'] ?? null);
    }

    protected function handlePaymentFailed(array $payload, PaymentService $payments): void
    {
        $resource = $payload['resource'] ?? [];
        $billingAgreementId = $resource['billing_agreement_id'] ?? $resource['id'] ?? null;

        if (! $billingAgreementId) {
            return;
        }

        $transaction = $this->pendingTransactionForSubscription($billingAgreementId);

        if (! $transaction) {
            return;
        }

        $payments->confirmPayment(
            $transaction,
            succeeded: false,
            failureReason: __('PayPal reported the payment as failed.'),
        );
    }

    protected function pendingTransactionForSubscription(string $gatewaySubscriptionId): ?Transaction
    {
        $subscription = Subscription::where('gateway_subscription_id', $gatewaySubscriptionId)->first();

        return $subscription?->transactions()->where('status', Transaction::STATUS_PENDING)->latest()->first();
    }
}
