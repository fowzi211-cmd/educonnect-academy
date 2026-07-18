<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LiveClass extends Model
{
    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_COMPLETED = 'completed';

    public const PROVIDERS = ['zoom', 'google_meet', 'teams', 'jitsi', 'other'];

    protected $fillable = [
        'course_id', 'host_id', 'title', 'description', 'starts_at', 'ends_at',
        'provider', 'meeting_link', 'host_link', 'status', 'cancellation_reason',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_id');
    }

    public function attendance(): HasMany
    {
        return $this->hasMany(LiveClassAttendance::class);
    }

    /**
     * Whether the student join link should be visible right now
     * (spec section 9: released only shortly before the class starts).
     */
    public function isJoinLinkReleased(): bool
    {
        $releaseAt = $this->starts_at->copy()->subMinutes(config('platform.live_class_link_release_minutes'));

        return now()->between($releaseAt, $this->ends_at);
    }
}
