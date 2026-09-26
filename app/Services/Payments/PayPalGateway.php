<?php

namespace App\Services\Payments;

use App\Contracts\PaymentGatewayContract;
use App\Models\Setting;
use App\Models\Subscription;
use App\Models\Transaction;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Real PayPal integration via the Subscriptions REST API (test or live mode,
 * depending on which client credentials are configured). Inactive until
 * PAYPAL_CLIENT_ID/PAYPAL_CLIENT_SECRET are set — see config/services.php —
 * at which point PaymentGatewayManager can select it as the active driver
 * with no other code changes.
 *
 * PayPal has no ad-hoc "price_data" equivalent, so each checkout creates a
 * disposable Product + Plan for the course's current price before creating
 * the Subscription, mirroring what StripeGateway does inline with price_data.
 *
 * The actual activation happens when PayPalWebhookController receives and
 * verifies a subscription/payment event, never from the browser redirect
 * alone (spec section 11: "Do not trust payment-success information sent
 * only from the browser").
 */
class PayPalGateway implements PaymentGatewayContract
{
    public function __construct(protected PayPalClient $client) {}

    public function name(): string
    {
        return 'paypal';
    }

    public function startCheckout(Subscription $subscription): string
    {
        $course = $subscription->course;
        $transaction = $subscription->transactions()->latest()->firstOrFail();

        $product = $this->client->http()->post('/v1/catalogs/products', [
            'name' => Str::limit($course->title, 120, ''),
            'type' => 'SERVICE',
            'category' => 'EDUCATIONAL_AND_TEXTBOOKS',
        ])->throw()->json();

        $plan = $this->client->http()->post('/v1/billing/plans', [
            'product_id' => $product['id'],
            'name' => Str::limit($course->title.' — Monthly', 120, ''),
            'billing_cycles' => [[
                'frequency' => ['interval_unit' => 'MONTH', 'interval_count' => 1],
                'tenure_type' => 'REGULAR',
                'sequence' => 1,
                'total_cycles' => 0,
                'pricing_scheme' => [
                    'fixed_price' => [
                        'value' => number_format((float) $subscription->monthly_price, 2, '.', ''),
                        'currency_code' => $subscription->currency,
                    ],
                ],
            ]],
            'payment_preferences' => [
                'auto_bill_outstanding' => true,
                'payment_failure_threshold' => 1,
            ],
        ])->throw()->json();

        $paypalSubscription = $this->client->http()->post('/v1/billing/subscriptions', [
            'plan_id' => $plan['id'],
            'custom_id' => (string) $transaction->id,
            'subscriber' => ['email_address' => $subscription->user->email],
            'application_context' => [
                'brand_name' => Setting::get('branding.platform_name', config('app.name')),
                'return_url' => route('checkout.complete', $subscription),
                'cancel_url' => route('courses.show', $course->slug),
                'user_action' => 'SUBSCRIBE_NOW',
            ],
        ])->throw()->json();

        $subscription->update(['gateway_subscription_id' => $paypalSubscription['id']]);

        $approveLink = collect($paypalSubscription['links'] ?? [])->firstWhere('rel', 'approve')['href'] ?? null;

        if (! $approveLink) {
            throw new RuntimeException('PayPal did not return a subscription approval link.');
        }

        return $approveLink;
    }

    public function cancelSubscription(Subscription $subscription, bool $immediate): void
    {
        if (! $immediate) {
            // PayPal's Subscriptions API has no "cancel at period end" — cancelling
            // now would cut off access the student already paid for. Leave the
            // remote subscription running; PaymentService::expireLapsedSubscriptions()
            // calls back here with $immediate=true once the paid period actually
            // ends, which is when this really needs to stop future billing.
            return;
        }

        if (! $subscription->gateway_subscription_id) {
            return;
        }

        try {
            $this->client->http()
                ->post("/v1/billing/subscriptions/{$subscription->gateway_subscription_id}/cancel", [
                    'reason' => 'Cancelled by administrator',
                ])
                ->throw();
        } catch (\Throwable $e) {
            // Best-effort: the remote subscription may already be gone. The local
            // status change in PaymentService is authoritative either way.
            report($e);
        }
    }

    public function resumeSubscription(Subscription $subscription): void
    {
        // The matching cancelSubscription() call above never touched the
        // remote subscription for a scheduled (non-immediate) cancellation,
        // so there is nothing to restore here.
    }

    public function refund(Transaction $transaction, float $amount): string
    {
        if (! $transaction->gateway_transaction_id) {
            throw new RuntimeException('Cannot refund a transaction with no PayPal capture reference.');
        }

        $refund = $this->client->http()
            ->post("/v2/payments/captures/{$transaction->gateway_transaction_id}/refund", [
                'amount' => [
                    'value' => number_format($amount, 2, '.', ''),
                    'currency_code' => $transaction->currency,
                ],
            ])
            ->throw()
            ->json();

        return $refund['id'];
    }
}
