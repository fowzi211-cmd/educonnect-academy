<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuizAttempt extends Model
{
    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_SUBMITTED = 'submitted';

    public const STATUS_GRADED = 'graded';

    protected $fillable = [
        'quiz_id', 'user_id', 'attempt_number', 'status', 'question_order',
        'started_at', 'time_limit_expires_at', 'integrity_acknowledged_at', 'submitted_at',
        'score_points', 'max_points', 'score_percent', 'passed', 'graded_at',
    ];

    protected function casts(): array
    {
        return [
            'question_order' => 'array',
            'started_at' => 'datetime',
            'time_limit_expires_at' => 'datetime',
            'integrity_acknowledged_at' => 'datetime',
            'submitted_at' => 'datetime',
            'graded_at' => 'datetime',
            'score_points' => 'decimal:2',
            'max_points' => 'decimal:2',
            'score_percent' => 'decimal:2',
            'passed' => 'boolean',
        ];
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(QuizAttemptAnswer::class);
    }

    public function isExpired(): bool
    {
        return $this->time_limit_expires_at !== null && now()->gte($this->time_limit_expires_at);
    }

    public function timeRemainingSeconds(): ?int
    {
        if ($this->time_limit_expires_at === null) {
            return null;
        }

        return max(0, now()->diffInSeconds($this->time_limit_expires_at, false));
    }

    public function hasUngraded(): bool
    {
        return $this->answers()->whereNull('points_awarded')->exists();
    }
}
