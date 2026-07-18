<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiveClassAttendance extends Model
{
    protected $table = 'live_class_attendance';

    public const STATUS_UNMARKED = 'unmarked';
    public const STATUS_PRESENT = 'present';
    public const STATUS_ABSENT = 'absent';
    public const STATUS_EXCUSED = 'excused';

    protected $fillable = [
        'live_class_id', 'user_id', 'status', 'joined_at', 'left_at', 'marked_by', 'note',
    ];

    protected function casts(): array
    {
        return [
            'joined_at' => 'datetime',
            'left_at' => 'datetime',
        ];
    }

    public function liveClass(): BelongsTo
    {
        return $this->belongsTo(LiveClass::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function markedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'marked_by');
    }
}
