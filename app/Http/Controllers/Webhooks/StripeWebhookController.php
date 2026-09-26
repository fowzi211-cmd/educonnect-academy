<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\WebhookEvent;
use App\Services\Payments\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Stripe\Event;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

/**
 * Verified, idempotent handling of Stripe webhook events. This is the only
 * place a subscription may be activated for the Stripe driver — the browser
 * redirect after checkout is never trusted on its own (spec section 11: "Do
 * not trust payment-success information sent only from the browser").
 *
 * Untestable without a real Stripe account and a way to receive webhooks
 * (e.g. the Stripe CLI forwarding to this URL), so this is a known
 * limitation until real credentials are configured — see StripeGateway.
 */
class StripeWebhookController extends Controller
{
    public function handle(Request $request, PaymentService $payments): Response
    {
        $secret = config('services.stripe.webhook_secret');

        if (! $secret) {
            report(new \RuntimeException('Received a Stripe webhook but STRIPE_WEBHOOK_SECRET is not configured.'));

            return response('Webhook secret not configured', 500);
        }

        try {
            $event = Webhook::constructEvent(
                $request->getContent(),
                $request->header('Stripe-Signature', ''),
                $secret,
            );
        } catch (SignatureVerificationException|\UnexpectedValueException $e) {
            report($e);

            return response('Invalid signature', 400);
        }

        // Idempotency: a redelivered event is a no-op (spec section 11).
        $webhookEvent = WebhookEvent::firstOrCreate(
            ['gateway' => 'stripe', 'event_id' => $event->id],
            ['type' => $event->type, 'payload' => $event->toArray()]
        );

        if ($webhookEvent->processed_at) {
            return response('Already processed', 200);
        }

        match ($event->type) {
            'checkout.session.completed' => $this->handleCheckoutCompleted($event, $payments),
            'invoice.payment_failed' => $this->handlePaymentFailed($event, $payments),
            default => null,
        };

        $webhookEvent->update(['processed_at' => now()]);

        return response('OK', 200);
    }

    protected function handleCheckoutCompleted(Event $event, PaymentService $payments): void
    {
        $session = $event->data->object;
        $transactionId = $session->metadata->transaction_id ?? null;

        if (! $transactionId || ! ($transaction = Transaction::find($transactionId))) {
            report(new \RuntimeException("Stripe checkout.session.completed with no matching transaction (id: {$transactionId})."));

            return;
        }

        if ($subscriptionGatewayId = $session->subscription ?? null) {
            $transaction->subscription?->update(['gateway_subscription_id' => $subscriptionGatewayId]);
        }

        $payments->confirmPayment(
            $transaction,
            succeeded: true,
            gatewayTransactionId: $session->payment_intent ?? $session->id,
        );
    }

    protected function handlePaymentFailed(Event $event, PaymentService $payments): void
    {
        $invoice = $event->data->object;
        $gatewaySubscriptionId = $invoice->subscription ?? null;

        if (! $gatewaySubscriptionId) {
            return;
        }

        $subscription = Subscription::where('gateway_subscription_id', $gatewaySubscriptionId)->first();
        $transaction = $subscription?->transactions()->where('status', Transaction::STATUS_PENDING)->latest()->first();

        if (! $transaction) {
            return;
        }

        $payments->confirmPayment(
            $transaction,
            succeeded: false,
            failureReason: __('Stripe reported the payment as failed.'),
        );
    }
}
