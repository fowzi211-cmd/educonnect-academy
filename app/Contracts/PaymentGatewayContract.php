<?php

namespace App\Contracts;

use App\Models\Subscription;
use App\Models\Transaction;

/**
 * A payment provider capable of running a course subscription through checkout,
 * cancelling it, and refunding a transaction. Implementations must never let the
 * caller decide success/failure or price — that always comes from the gateway
 * (or, for the sandbox driver, from a deliberate simulation step) and is
 * confirmed server-side.
 */
interface PaymentGatewayContract
{
    /**
     * Machine name of this driver, e.g. "test", "stripe". Stored on the
     * subscription/transaction so it's traceable which gateway handled it.
     */
    public function name(): string;

    /**
     * Start a checkout for the given (already-persisted, pending) subscription
     * and return the URL to send the browser to — a gateway-hosted checkout
     * page for a real provider, or a local simulated-checkout route for the
     * sandbox driver.
     */
    public function startCheckout(Subscription $subscription): string;

    /**
     * Best-effort cancellation at the gateway. The local subscription status
     * change happens in PaymentService regardless of whether the gateway call
     * succeeds, so this should not throw for a gateway that has no matching
     * remote subscription (e.g. it already lapsed).
     */
    public function cancelSubscription(Subscription $subscription): void;

    /**
     * Refund all or part of a succeeded transaction at the gateway. Returns
     * the gateway's reference for the refund.
     */
    public function refund(Transaction $transaction, float $amount): string;
}
