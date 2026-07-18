<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscription extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_TRIAL = 'trial';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_PAST_DUE = 'past_due';
    public const STATUS_GRACE_PERIOD = 'grace_period';
    public const STATUS_PAUSED = 'paused';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_REFUNDED = 'refunded';
    public const STATUS_PAYMENT_FAILED = 'payment_failed';

    /** Statuses under which the student should keep classroom access. */
    public const ACCESS_GRANTING_STATUSES = [
        self::STATUS_TRIAL,
        self::STATUS_ACTIVE,
        self::STATUS_PAST_DUE,
        self::STATUS_GRACE_PERIOD,
    ];

    protected $fillable = [
        'user_id', 'course_id', 'status', 'monthly_price', 'currency', 'gateway',
        'gateway_customer_id', 'gateway_subscription_id', 'coupon_id',
        'current_period_starts_at', 'current_period_ends_at', 'trial_ends_at',
        'cancel_at_period_end', 'cancelled_at', 'cancellation_reason', 'next_payment_at',
        'failed_payment_count', 'grace_period_ends_at',
    ];

    protected function casts(): array
    {
        return [
            'monthly_price' => 'decimal:2',
            'current_period_starts_at' => 'datetime',
            'current_period_ends_at' => 'datetime',
            'trial_ends_at' => 'datetime',
            'cancel_at_period_end' => 'boolean',
            'cancelled_at' => 'datetime',
            'next_payment_at' => 'datetime',
            'grace_period_ends_at' => 'datetime',
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

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function grantsAccess(): bool
    {
        return in_array($this->status, self::ACCESS_GRANTING_STATUSES, true);
    }
}
