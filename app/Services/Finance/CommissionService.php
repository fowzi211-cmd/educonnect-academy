<?php

namespace App\Services\Finance;

use App\Models\CommissionEarning;
use App\Models\Course;
use App\Models\LecturerCommissionRate;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\User;

/**
 * Calculates and records the lecturer's share of each confirmed transaction
 * (spec section 22). The rate that applies is resolved in priority order:
 * a course-specific override, then a lecturer-wide override, then the
 * platform default — so an admin can tune pay for one course without
 * touching the lecturer's other courses.
 */
class CommissionService
{
    public function resolveRateFor(User $lecturer, Course $course): array
    {
        $rate = LecturerCommissionRate::where('user_id', $lecturer->id)->where('course_id', $course->id)->first()
            ?? LecturerCommissionRate::where('user_id', $lecturer->id)->whereNull('course_id')->first();

        if ($rate) {
            return ['type' => $rate->type, 'value' => (float) $rate->value];
        }

        return [
            'type' => Setting::get('commission.default_rate_type', LecturerCommissionRate::TYPE_PERCENTAGE),
            'value' => (float) Setting::get('commission.default_rate_value', 70),
        ];
    }

    public function calculateAmount(float $gross, string $type, float $value): float
    {
        $amount = $type === LecturerCommissionRate::TYPE_FIXED
            ? $value
            : $gross * ($value / 100);

        return round(max(0, min($amount, $gross)), 2);
    }

    /**
     * Record the lecturer's earning for a just-confirmed transaction.
     * Safe to call more than once — a second call for the same transaction
     * is a no-op thanks to the unique constraint on commission_earnings.transaction_id.
     */
    public function recordEarning(Transaction $transaction): ?CommissionEarning
    {
        if (CommissionEarning::where('transaction_id', $transaction->id)->exists()) {
            return null;
        }

        $course = $transaction->course;
        $lecturer = $this->primaryLecturerFor($course);

        if (! $lecturer) {
            return null;
        }

        $rate = $this->resolveRateFor($lecturer, $course);
        $amount = $this->calculateAmount((float) $transaction->amount, $rate['type'], $rate['value']);

        return CommissionEarning::create([
            'user_id' => $lecturer->id,
            'course_id' => $course->id,
            'transaction_id' => $transaction->id,
            'gross_amount' => $transaction->amount,
            'rate_type' => $rate['type'],
            'rate_value' => $rate['value'],
            'amount' => $amount,
            'currency' => $transaction->currency,
            'status' => CommissionEarning::STATUS_UNPAID,
        ]);
    }

    /**
     * Deduct the lecturer's share of a refund from their earning for that
     * transaction, proportional to how much of the transaction was refunded.
     * Earnings already paid out are left untouched — refund clawback against
     * a completed payout is out of scope for this platform.
     */
    public function adjustForRefund(Transaction $transaction, float $refundAmount): void
    {
        $earning = CommissionEarning::where('transaction_id', $transaction->id)
            ->where('status', CommissionEarning::STATUS_UNPAID)
            ->first();

        if (! $earning || (float) $transaction->amount <= 0) {
            return;
        }

        $deduction = round($earning->amount * ($refundAmount / (float) $transaction->amount), 2);

        $earning->update([
            'refunded_amount' => min($earning->amount, $earning->refunded_amount + $deduction),
        ]);
    }

    protected function primaryLecturerFor(Course $course): ?User
    {
        $course->loadMissing('lecturers', 'creator');

        return $course->lecturers->firstWhere('pivot.is_primary', true)
            ?? $course->lecturers->first()
            ?? $course->creator;
    }
}
