<?php

namespace App\Services\Payments;

use App\Contracts\PaymentGatewayContract;
use App\Models\Subscription;
use App\Models\Transaction;
use Illuminate\Support\Str;

/**
 * Self-contained sandbox gateway: no external account, no API keys, no
 * network calls. Checkout is a local page where the tester explicitly
 * simulates a success or failure, which then runs through the exact same
 * PaymentService::confirmPayment() path a real gateway's webhook would use.
 *
 * This is what "default_gateway" resolves to until real credentials are
 * configured — see PaymentGatewayManager and StripeGateway.
 */
class TestGateway implements PaymentGatewayContract
{
    public function name(): string
    {
        return 'test';
    }

    public function startCheckout(Subscription $subscription): string
    {
        return route('checkout.test.show', $subscription);
    }

    public function cancelSubscription(Subscription $subscription, bool $immediate): void
    {
        // Nothing external to cancel — the sandbox never created a remote subscription.
    }

    public function resumeSubscription(Subscription $subscription): void
    {
        // Nothing external to resume — see cancelSubscription().
    }

    public function refund(Transaction $transaction, float $amount): string
    {
        return 'test_refund_'.Str::random(16);
    }
}
