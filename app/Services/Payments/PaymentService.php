<?php

namespace App\Services\Payments;

use App\Models\AuditLog;
use App\Models\Coupon;
use App\Models\CouponRedemption;
use App\Models\Course;
use App\Models\Enrolment;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Refund;
use App\Models\Setting;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use App\Notifications\SubscriptionPaymentFailed;
use App\Notifications\SubscriptionPaymentReceived;
use App\Services\Finance\CommissionService;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Orchestrates the subscription lifecycle across any PaymentGatewayContract
 * driver. Every state change that matters (activation, failure, cancellation,
 * refund) is idempotent and funnels through here, whether it was triggered by
 * a gateway webhook or the sandbox driver's local "simulate payment" step.
 */
class PaymentService
{
    public function __construct(protected PaymentGatewayManager $gateways, protected CommissionService $commissions) {}

    /**
     * Begin a subscription checkout: validates eligibility, prices the course
     * (applying a coupon if given), and creates pending subscription/transaction
     * rows before handing off to the configured gateway to start checkout.
     */
    public function initiateSubscription(User $user, Course $course, ?Coupon $coupon = null, ?string $gatewayName = null): array
    {
        if ($course->status !== Course::STATUS_PUBLISHED) {
            throw new RuntimeException(__('This course is not open for subscriptions.'));
        }

        if ($course->monthly_price === null) {
            throw new RuntimeException(__('This course does not have a subscription price.'));
        }

        if ($course->subscriptions()->where('user_id', $user->id)->whereIn('status', Subscription::ACCESS_GRANTING_STATUSES)->exists()) {
            throw new RuntimeException(__('You already have an active subscription to this course.'));
        }

        $amount = (float) $course->monthly_price;
        $discount = 0.0;

        if ($coupon) {
            if ($error = $coupon->validationErrorFor($user, $course)) {
                throw new RuntimeException($error);
            }

            $discount = $coupon->discountFor($amount);
        }

        $gatewayName ??= $this->gateways->activeDriverName();

        return DB::transaction(function () use ($user, $course, $coupon, $amount, $discount, $gatewayName) {
            $subscription = Subscription::create([
                'user_id' => $user->id,
                'course_id' => $course->id,
                'status' => Subscription::STATUS_PENDING,
                'monthly_price' => $amount,
                'currency' => $course->currency,
                'gateway' => $gatewayName,
                'coupon_id' => $coupon?->id,
            ]);

            $transaction = Transaction::create([
                'user_id' => $user->id,
                'course_id' => $course->id,
                'subscription_id' => $subscription->id,
                'type' => Transaction::TYPE_SUBSCRIPTION_PAYMENT,
                'amount' => $amount - $discount,
                'discount_amount' => $discount,
                'currency' => $course->currency,
                'status' => Transaction::STATUS_PENDING,
                'gateway' => $gatewayName,
                'coupon_id' => $coupon?->id,
            ]);

            $redirectUrl = $this->gateways->driver($gatewayName)->startCheckout($subscription);

            return ['subscription' => $subscription, 'transaction' => $transaction, 'redirectUrl' => $redirectUrl];
        });
    }

    /**
     * Confirm the outcome of a checkout attempt. Safe to call more than once
     * for the same transaction (e.g. a redelivered webhook) — anything past
     * the pending state is a no-op.
     */
    public function confirmPayment(Transaction $transaction, bool $succeeded, ?string $gatewayTransactionId = null, ?string $failureReason = null): void
    {
        if ($transaction->status !== Transaction::STATUS_PENDING) {
            return;
        }

        DB::transaction(function () use ($transaction, $succeeded, $gatewayTransactionId, $failureReason) {
            $subscription = $transaction->subscription;

            if ($succeeded) {
                $this->activateSubscription($subscription, $transaction, $gatewayTransactionId);
            } else {
                $transaction->update([
                    'status' => Transaction::STATUS_FAILED,
                    'failure_reason' => $failureReason,
                    'gateway_transaction_id' => $gatewayTransactionId,
                ]);

                // A subscription that has already been through at least one billing
                // period is a renewal failure: keep access for a grace period so the
                // student can update their payment method (spec section 11). A
                // subscription that never activated (its first payment was declined)
                // never had access to begin with, so it fails outright.
                if ($subscription->current_period_ends_at !== null) {
                    $subscription->update([
                        'status' => Subscription::STATUS_GRACE_PERIOD,
                        'failed_payment_count' => $subscription->failed_payment_count + 1,
                        'grace_period_ends_at' => now()->addDays((int) Setting::get('platform.payment_grace_period_days', config('platform.payment_grace_period_days'))),
                    ]);

                    AuditLog::record('subscription.grace_period_started', subject: $subscription, reason: $failureReason, new: [
                        'grace_period_ends_at' => $subscription->grace_period_ends_at,
                    ]);

                    $subscription->user->notify(new SubscriptionPaymentFailed($subscription, gracePeriod: true, reason: $failureReason));
                } else {
                    $subscription->update([
                        'status' => Subscription::STATUS_PAYMENT_FAILED,
                        'failed_payment_count' => $subscription->failed_payment_count + 1,
                    ]);

                    AuditLog::record('subscription.payment_failed', subject: $subscription, reason: $failureReason);

                    $subscription->user->notify(new SubscriptionPaymentFailed($subscription, gracePeriod: false, reason: $failureReason));
                }
            }
        });
    }

    protected function activateSubscription(Subscription $subscription, Transaction $transaction, ?string $gatewayTransactionId): void
    {
        $now = now();
        $periodEnd = $now->copy()->addMonth();

        $transaction->update([
            'status' => Transaction::STATUS_SUCCEEDED,
            'gateway_transaction_id' => $gatewayTransactionId,
            'paid_at' => $now,
        ]);

        $subscription->update([
            'status' => Subscription::STATUS_ACTIVE,
            'current_period_starts_at' => $now,
            'current_period_ends_at' => $periodEnd,
            'next_payment_at' => $periodEnd,
            'failed_payment_count' => 0,
        ]);

        $invoice = $this->createInvoice($transaction);

        if ($subscription->coupon_id) {
            $subscription->coupon()->increment('times_redeemed');

            CouponRedemption::create([
                'coupon_id' => $subscription->coupon_id,
                'user_id' => $subscription->user_id,
                'subscription_id' => $subscription->id,
                'redeemed_at' => $now,
            ]);
        }

        Enrolment::updateOrCreate(
            ['user_id' => $subscription->user_id, 'course_id' => $subscription->course_id],
            ['status' => Enrolment::STATUS_ACTIVE, 'source' => 'subscription', 'enrolled_at' => $now, 'ends_at' => null]
        );

        $this->commissions->recordEarning($transaction);

        AuditLog::record('subscription.activated', subject: $subscription, new: [
            'transaction_id' => $transaction->id,
            'invoice_id' => $invoice->id,
        ]);

        $subscription->user->notify(new SubscriptionPaymentReceived($subscription));
    }

    protected function createInvoice(Transaction $transaction): Invoice
    {
        $invoice = Invoice::create([
            'invoice_number' => Invoice::nextInvoiceNumber(),
            'user_id' => $transaction->user_id,
            'transaction_id' => $transaction->id,
            'subtotal' => $transaction->amount + $transaction->discount_amount,
            'discount_amount' => $transaction->discount_amount,
            'tax_amount' => 0,
            'total' => $transaction->amount,
            'currency' => $transaction->currency,
            'status' => Invoice::STATUS_PAID,
            'issued_at' => now(),
            'paid_at' => now(),
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'description' => __('Monthly subscription: :course', ['course' => $transaction->course->title]),
            'amount' => $transaction->amount + $transaction->discount_amount,
            'quantity' => 1,
        ]);

        return $invoice;
    }

    /**
     * Cancel a subscription. By default access continues until the paid
     * period ends (spec section 11); pass $immediate to end it right away.
     */
    public function cancelSubscription(Subscription $subscription, string $reason, bool $immediate = false): void
    {
        $this->gateways->driverFor($subscription->gateway)->cancelSubscription($subscription, $immediate);

        DB::transaction(function () use ($subscription, $reason, $immediate) {
            $subscription->update([
                'cancel_at_period_end' => ! $immediate,
                'cancelled_at' => now(),
                'cancellation_reason' => $reason,
                'status' => $immediate ? Subscription::STATUS_CANCELLED : $subscription->status,
            ]);

            if ($immediate) {
                Enrolment::where('user_id', $subscription->user_id)
                    ->where('course_id', $subscription->course_id)
                    ->update(['status' => Enrolment::STATUS_CANCELLED]);
            }

            AuditLog::record('subscription.cancelled', subject: $subscription, reason: $reason, new: ['immediate' => $immediate]);
        });
    }

    /**
     * Undo a scheduled (not-yet-effective) cancellation so the subscription
     * keeps auto-renewing. Only meaningful for cancel_at_period_end=true —
     * a subscription already fully cancelled cannot be resumed this way.
     */
    public function resumeSubscription(Subscription $subscription): void
    {
        if (! $subscription->cancel_at_period_end) {
            throw new RuntimeException(__('This subscription is not scheduled to cancel.'));
        }

        $this->gateways->driverFor($subscription->gateway)->resumeSubscription($subscription);

        $subscription->update([
            'cancel_at_period_end' => false,
            'cancelled_at' => null,
            'cancellation_reason' => null,
        ]);

        AuditLog::record('subscription.resumed', subject: $subscription);
    }

    /**
     * Give up waiting out a grace period and end access now, so the student
     * can start a fresh checkout instead (initiateSubscription() only blocks
     * re-subscribing while an access-granting subscription exists).
     */
    public function abandonGracePeriod(Subscription $subscription): void
    {
        if ($subscription->status !== Subscription::STATUS_GRACE_PERIOD) {
            throw new RuntimeException(__('This subscription is not in a grace period.'));
        }

        DB::transaction(function () use ($subscription) {
            $subscription->update(['status' => Subscription::STATUS_PAYMENT_FAILED]);

            Enrolment::where('user_id', $subscription->user_id)
                ->where('course_id', $subscription->course_id)
                ->update(['status' => Enrolment::STATUS_CANCELLED]);

            AuditLog::record('subscription.grace_period_abandoned', subject: $subscription);
        });
    }

    /**
     * Expire subscriptions whose period has ended with no renewal due
     * (cancel_at_period_end) — meant to run on a schedule.
     *
     * This is also where a gateway that couldn't natively schedule its own
     * cancellation (PayPal — see PayPalGateway::cancelSubscription()) finally
     * gets told to stop future billing, now that the paid period is actually
     * over. A no-op for gateways that already scheduled the cancellation
     * themselves (Stripe) or never had a remote subscription to begin with.
     */
    public function expireLapsedSubscriptions(): int
    {
        $subscriptions = Subscription::where('cancel_at_period_end', true)
            ->whereIn('status', Subscription::ACCESS_GRANTING_STATUSES)
            ->where('current_period_ends_at', '<=', now())
            ->get();

        foreach ($subscriptions as $subscription) {
            $this->gateways->driverFor($subscription->gateway)->cancelSubscription($subscription, immediate: true);

            DB::transaction(function () use ($subscription) {
                $subscription->update(['status' => Subscription::STATUS_EXPIRED]);

                Enrolment::where('user_id', $subscription->user_id)
                    ->where('course_id', $subscription->course_id)
                    ->update(['status' => Enrolment::STATUS_EXPIRED]);

                AuditLog::record('subscription.expired', subject: $subscription);
            });
        }

        return $subscriptions->count();
    }

    /**
     * Revoke access for subscriptions whose payment grace period has ended
     * with no successful renewal — meant to run on a schedule.
     */
    public function expireLapsedGracePeriods(): int
    {
        $subscriptions = Subscription::where('status', Subscription::STATUS_GRACE_PERIOD)
            ->where('grace_period_ends_at', '<=', now())
            ->get();

        foreach ($subscriptions as $subscription) {
            DB::transaction(function () use ($subscription) {
                $subscription->update(['status' => Subscription::STATUS_PAYMENT_FAILED]);

                Enrolment::where('user_id', $subscription->user_id)
                    ->where('course_id', $subscription->course_id)
                    ->update(['status' => Enrolment::STATUS_CANCELLED]);

                AuditLog::record('subscription.grace_period_expired', subject: $subscription);
            });
        }

        return $subscriptions->count();
    }

    public function processRefund(Transaction $transaction, float $amount, string $reason, User $processedBy, bool $endsAccessImmediately): Refund
    {
        if ($transaction->status !== Transaction::STATUS_SUCCEEDED) {
            throw new RuntimeException(__('Only a successful transaction can be refunded.'));
        }

        if ($amount <= 0 || $amount > $transaction->remainingRefundable()) {
            throw new RuntimeException(__('The refund amount must be greater than zero and cannot exceed the remaining refundable balance of :amount.', [
                'amount' => number_format($transaction->remainingRefundable(), 2),
            ]));
        }

        $gatewayReference = $this->gateways->driverFor($transaction->gateway)->refund($transaction, $amount);

        return DB::transaction(function () use ($transaction, $amount, $reason, $processedBy, $endsAccessImmediately, $gatewayReference) {
            $refund = Refund::create([
                'transaction_id' => $transaction->id,
                'amount' => $amount,
                'reason' => $reason,
                'status' => 'completed',
                'processed_by' => $processedBy->id,
                'ends_access_immediately' => $endsAccessImmediately,
                'processed_at' => now(),
            ]);

            if (($invoice = $transaction->invoice) && $transaction->remainingRefundable() <= 0) {
                $invoice->update(['status' => Invoice::STATUS_REFUNDED]);
            }

            $this->commissions->adjustForRefund($transaction, $amount);

            if ($endsAccessImmediately && $subscription = $transaction->subscription) {
                $subscription->update(['status' => Subscription::STATUS_REFUNDED]);

                Enrolment::where('user_id', $subscription->user_id)
                    ->where('course_id', $subscription->course_id)
                    ->update(['status' => Enrolment::STATUS_CANCELLED]);
            }

            AuditLog::record('transaction.refunded', subject: $transaction, reason: $reason, new: [
                'amount' => $amount,
                'gateway_reference' => $gatewayReference,
                'ends_access_immediately' => $endsAccessImmediately,
            ]);

            return $refund;
        });
    }
}
