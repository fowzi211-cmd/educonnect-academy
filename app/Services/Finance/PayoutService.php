<?php

namespace App\Services\Finance;

use App\Models\AuditLog;
use App\Models\CommissionEarning;
use App\Models\PayoutRequest;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Bundles a lecturer's unpaid commission earnings into a payout request and
 * carries it through finance review (spec section 22). Earnings stay
 * "unpaid" through approval — only marking the request paid flips them, so
 * the lecturer's statement always reflects money actually sent.
 */
class PayoutService
{
    public function unpaidEarningsQuery(User $lecturer)
    {
        return CommissionEarning::where('user_id', $lecturer->id)
            ->where('status', CommissionEarning::STATUS_UNPAID)
            ->whereNull('payout_request_id');
    }

    public function availableBalance(User $lecturer): float
    {
        return (float) $this->unpaidEarningsQuery($lecturer)->get()->sum->netAmount();
    }

    public function requestPayout(User $lecturer): PayoutRequest
    {
        $earnings = $this->unpaidEarningsQuery($lecturer)->get();

        if ($earnings->isEmpty()) {
            throw new RuntimeException(__('You have no unpaid earnings to request a payout for.'));
        }

        $currency = $earnings->first()->currency;
        $eligible = $earnings->where('currency', $currency);
        $amount = round((float) $eligible->sum->netAmount(), 2);

        $threshold = (float) Setting::get('commission.payout_threshold', 0);

        if ($amount < $threshold) {
            throw new RuntimeException(__('Your unpaid balance of :amount is below the minimum payout threshold of :threshold.', [
                'amount' => number_format($amount, 2).' '.$currency,
                'threshold' => number_format($threshold, 2).' '.$currency,
            ]));
        }

        return DB::transaction(function () use ($lecturer, $eligible, $currency, $amount) {
            $request = PayoutRequest::create([
                'user_id' => $lecturer->id,
                'amount' => $amount,
                'currency' => $currency,
                'status' => PayoutRequest::STATUS_REQUESTED,
                'requested_at' => now(),
            ]);

            CommissionEarning::whereIn('id', $eligible->pluck('id'))->update(['payout_request_id' => $request->id]);

            AuditLog::record('payout_request.created', subject: $request, new: [
                'amount' => $amount,
                'currency' => $currency,
                'earnings_count' => $eligible->count(),
            ]);

            return $request;
        });
    }

    public function approve(PayoutRequest $request, User $reviewer): void
    {
        if ($request->status !== PayoutRequest::STATUS_REQUESTED) {
            throw new RuntimeException(__('Only a requested payout can be approved.'));
        }

        $request->update([
            'status' => PayoutRequest::STATUS_APPROVED,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
        ]);

        AuditLog::record('payout_request.approved', subject: $request);
    }

    public function reject(PayoutRequest $request, User $reviewer, string $reason): void
    {
        if (! in_array($request->status, [PayoutRequest::STATUS_REQUESTED, PayoutRequest::STATUS_APPROVED], true)) {
            throw new RuntimeException(__('This payout request can no longer be rejected.'));
        }

        DB::transaction(function () use ($request, $reviewer, $reason) {
            $request->update([
                'status' => PayoutRequest::STATUS_REJECTED,
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
                'rejection_reason' => $reason,
            ]);

            // Release the earnings back to the available pool so the lecturer
            // can include them in a future payout request.
            $request->earnings()->update(['payout_request_id' => null]);

            AuditLog::record('payout_request.rejected', subject: $request, reason: $reason);
        });
    }

    public function markPaid(PayoutRequest $request, User $reviewer, string $reference): void
    {
        if ($request->status !== PayoutRequest::STATUS_APPROVED) {
            throw new RuntimeException(__('Only an approved payout can be marked as paid.'));
        }

        DB::transaction(function () use ($request, $reviewer, $reference) {
            $request->update([
                'status' => PayoutRequest::STATUS_PAID,
                'payout_reference' => $reference,
                'paid_at' => now(),
                'reviewed_by' => $reviewer->id,
            ]);

            $request->earnings()->update(['status' => CommissionEarning::STATUS_PAID]);

            AuditLog::record('payout_request.paid', subject: $request, new: ['payout_reference' => $reference]);
        });
    }
}
