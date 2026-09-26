<?php

namespace App\Services\Payments;

use App\Contracts\PaymentGatewayContract;
use App\Models\Subscription;
use App\Models\Transaction;
use RuntimeException;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

/**
 * Real Stripe integration (test or live mode, depending on which secret key
 * is configured). Inactive until STRIPE_KEY/STRIPE_SECRET are set — see
 * config/services.php — at which point PaymentGatewayManager can select it
 * as the active driver with no other code changes.
 *
 * Checkout runs through Stripe Checkout in subscription mode; the actual
 * activation happens when StripeWebhookController receives and verifies a
 * `checkout.session.completed` event, never from the browser redirect alone.
 */
class StripeGateway implements PaymentGatewayContract
{
    protected StripeClient $client;

    public function __construct()
    {
        $secret = config('services.stripe.secret');

        if (! $secret) {
            throw new RuntimeException(
                'Stripe is not configured. Set STRIPE_KEY and STRIPE_SECRET in .env, '
                .'or switch the active payment gateway back to "test" in Admin > Settings.'
            );
        }

        $this->client = new StripeClient($secret);
    }

    public function name(): string
    {
        return 'stripe';
    }

    public function startCheckout(Subscription $subscription): string
    {
        $course = $subscription->course;

        $session = $this->client->checkout->sessions->create([
            'mode' => 'subscription',
            'customer_email' => $subscription->user->email,
            'client_reference_id' => (string) $subscription->id,
            'line_items' => [[
                'quantity' => 1,
                'price_data' => [
                    'currency' => strtolower($subscription->currency),
                    'unit_amount' => (int) round((float) $subscription->monthly_price * 100),
                    'recurring' => ['interval' => 'month'],
                    'product_data' => [
                        'name' => $course->title,
                    ],
                ],
            ]],
            'metadata' => [
                'subscription_id' => $subscription->id,
                'transaction_id' => $subscription->transactions()->latest()->value('id'),
            ],
            'success_url' => route('checkout.complete', $subscription).'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('courses.show', $course->slug),
        ]);

        return $session->url;
    }

    public function cancelSubscription(Subscription $subscription, bool $immediate): void
    {
        if (! $subscription->gateway_subscription_id) {
            return;
        }

        try {
            if ($immediate) {
                $this->client->subscriptions->cancel($subscription->gateway_subscription_id);
            } else {
                // Stripe stops billing and cancels the subscription itself once
                // the current period ends — no separate job needs to call back
                // in to finish the job, unlike PayPal.
                $this->client->subscriptions->update($subscription->gateway_subscription_id, [
                    'cancel_at_period_end' => true,
                ]);
            }
        } catch (ApiErrorException $e) {
            // Best-effort: the remote subscription may already be gone. The local
            // status change in PaymentService is authoritative either way.
            report($e);
        }
    }

    public function resumeSubscription(Subscription $subscription): void
    {
        if (! $subscription->gateway_subscription_id) {
            return;
        }

        try {
            $this->client->subscriptions->update($subscription->gateway_subscription_id, [
                'cancel_at_period_end' => false,
            ]);
        } catch (ApiErrorException $e) {
            report($e);
        }
    }

    public function refund(Transaction $transaction, float $amount): string
    {
        if (! $transaction->gateway_transaction_id) {
            throw new RuntimeException('Cannot refund a transaction with no Stripe payment reference.');
        }

        $refund = $this->client->refunds->create([
            'payment_intent' => $transaction->gateway_transaction_id,
            'amount' => (int) round($amount * 100),
        ]);

        return $refund->id;
    }
}
