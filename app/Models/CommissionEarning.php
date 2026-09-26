<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommissionEarning extends Model
{
    public const STATUS_UNPAID = 'unpaid';

    public const STATUS_PAID = 'paid';

    protected $fillable = [
        'user_id', 'course_id', 'transaction_id', 'gross_amount', 'rate_type', 'rate_value',
        'amount', 'refunded_amount', 'currency', 'status', 'payout_request_id',
    ];

    protected function casts(): array
    {
        return [
            'gross_amount' => 'decimal:2',
            'rate_value' => 'decimal:2',
            'amount' => 'decimal:2',
            'refunded_amount' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function payoutRequest(): BelongsTo
    {
        return $this->belongsTo(PayoutRequest::class);
    }

    public function netAmount(): float
    {
        return max(0, (float) $this->amount - (float) $this->refunded_amount);
    }
}
