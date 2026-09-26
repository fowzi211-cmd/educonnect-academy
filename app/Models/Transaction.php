<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Transaction extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_SUCCEEDED = 'succeeded';

    public const STATUS_FAILED = 'failed';

    public const TYPE_SUBSCRIPTION_PAYMENT = 'subscription_payment';

    public const TYPE_REFUND = 'refund';

    protected $fillable = [
        'user_id', 'course_id', 'subscription_id', 'type', 'amount', 'discount_amount',
        'currency', 'status', 'gateway', 'gateway_transaction_id', 'coupon_id',
        'failure_reason', 'paid_at', 'bank_account_id', 'receipt_path', 'receipt_name',
        'receipt_hash', 'declared_amount', 'bank_reference_number', 'receipt_reference_number',
        'reviewed_by', 'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'declared_amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    /**
     * A unique, student-facing tracking code assigned when a bank-transfer
     * receipt is submitted — separate from the bank's own reference number,
     * which the student supplies and cannot be trusted to be unique across
     * banks. Mirrors Invoice::nextInvoiceNumber()'s per-year sequence.
     */
    public static function nextReceiptReferenceNumber(): string
    {
        $year = now()->format('Y');
        $count = static::whereYear('created_at', $year)->whereNotNull('receipt_reference_number')->count() + 1;

        return sprintf('RCPT-%s-%05d', $year, $count);
    }

    /**
     * Whether the student's self-declared transfer amount disagrees with
     * what's actually owed — surfaced to the admin as a warning, not a
     * block, since a small bank-fee deduction can legitimately cause this.
     */
    public function hasAmountMismatch(): bool
    {
        if ($this->declared_amount === null) {
            return false;
        }

        return round((float) $this->declared_amount, 2) !== round((float) $this->amount, 2);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }

    public function totalRefunded(): float
    {
        return (float) $this->refunds()->sum('amount');
    }

    public function remainingRefundable(): float
    {
        return max(0, (float) $this->amount - $this->totalRefunded());
    }
}
