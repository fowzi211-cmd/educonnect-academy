<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    public const TYPE_PERCENTAGE = 'percentage';

    public const TYPE_FIXED = 'fixed';

    protected $fillable = [
        'code', 'type', 'value', 'course_id', 'user_id',
        'max_redemptions', 'times_redeemed', 'expires_at', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function redemptions(): HasMany
    {
        return $this->hasMany(CouponRedemption::class);
    }

    /**
     * Whether this coupon can currently be redeemed by the given user for the
     * given course. Returns a human-readable reason on failure.
     */
    public function validationErrorFor(User $user, Course $course): ?string
    {
        if (! $this->is_active) {
            return __('This coupon is no longer active.');
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return __('This coupon has expired.');
        }

        if ($this->max_redemptions && $this->times_redeemed >= $this->max_redemptions) {
            return __('This coupon has reached its usage limit.');
        }

        if ($this->course_id && $this->course_id !== $course->id) {
            return __('This coupon is not valid for this course.');
        }

        if ($this->user_id && $this->user_id !== $user->id) {
            return __('This coupon is not valid for your account.');
        }

        return null;
    }

    public function discountFor(float $amount): float
    {
        $discount = $this->type === self::TYPE_PERCENTAGE
            ? $amount * ((float) $this->value / 100)
            : (float) $this->value;

        return round(min($discount, $amount), 2);
    }
}
