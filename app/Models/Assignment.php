<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assignment extends Model
{
    public const LATE_NOT_ALLOWED = 'not_allowed';

    public const LATE_WITH_PENALTY = 'allowed_with_penalty';

    public const LATE_NO_PENALTY = 'allowed_no_penalty';

    protected $fillable = [
        'course_id', 'course_section_id', 'title', 'description', 'attachment_path', 'attachment_name', 'max_points',
        'opens_at', 'due_at', 'late_policy', 'late_penalty_percent_per_day',
        'allowed_file_types', 'max_file_size_kb', 'is_published', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'max_points' => 'decimal:2',
            'opens_at' => 'datetime',
            'due_at' => 'datetime',
            'late_penalty_percent_per_day' => 'decimal:2',
            'is_published' => 'boolean',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(CourseSection::class, 'course_section_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(AssignmentSubmission::class);
    }

    public function isOpen(): bool
    {
        $now = now();

        if ($this->opens_at && $now->lt($this->opens_at)) {
            return false;
        }

        if ($this->due_at && $now->gt($this->due_at) && $this->late_policy === self::LATE_NOT_ALLOWED) {
            return false;
        }

        return true;
    }

    public function isPastDue(): bool
    {
        return $this->due_at !== null && now()->gt($this->due_at);
    }

    public function allowedExtensions(): array
    {
        if (! $this->allowed_file_types) {
            return [];
        }

        return array_map('trim', explode(',', $this->allowed_file_types));
    }
}
