<?php

namespace App\Services\Payments;

use App\Contracts\PaymentGatewayContract;
use App\Models\Subscription;
use App\Models\Transaction;

/**
 * Manual gateway: checkout is a local page where the student uploads a
 * transfer receipt against one of the platform's bank accounts, then an
 * admin reviews it and confirms payment via PaymentService::confirmPayment(),
 * exactly like a webhook would for an automated gateway.
 */
class BankTransferGateway implements PaymentGatewayContract
{
    public function name(): string
    {
        return 'bank_transfer';
    }

    public function startCheckout(Subscription $subscription): string
    {
        return route('checkout.bank-transfer.show', $subscription);
    }

    public function cancelSubscription(Subscription $subscription, bool $immediate): void
    {
        // Nothing external to cancel — no remote subscription was ever created.
    }

    public function resumeSubscription(Subscription $subscription): void
    {
        // Nothing external to resume — see cancelSubscription().
    }

    public function refund(Transaction $transaction, float $amount): string
    {
        return 'Manual refund required: transfer the amount back to the student\'s bank account.';
    }
}
