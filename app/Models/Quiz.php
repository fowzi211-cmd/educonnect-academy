<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quiz extends Model
{
    public const TYPE_QUIZ = 'quiz';

    public const TYPE_EXAM = 'exam';

    public const TYPE_PRACTICE_TEST = 'practice_test';

    public const TYPE_SURVEY = 'survey';

    public const TYPES = [self::TYPE_QUIZ, self::TYPE_EXAM, self::TYPE_PRACTICE_TEST, self::TYPE_SURVEY];

    public const RESULT_IMMEDIATE = 'immediate';

    public const RESULT_AFTER_FEEDBACK_RELEASE = 'after_feedback_release';

    public const RESULT_MANUAL = 'manual';

    public const ANSWERS_NEVER = 'never';

    public const ANSWERS_AFTER_SUBMIT = 'after_submit';

    public const ANSWERS_AFTER_CLOSE = 'after_close';

    public const ANSWERS_AFTER_FEEDBACK_RELEASE = 'after_feedback_release';

    protected $fillable = [
        'course_id', 'course_section_id', 'title', 'description', 'type',
        'time_limit_minutes', 'max_attempts', 'pass_mark_percent',
        'shuffle_questions', 'shuffle_options', 'negative_marking',
        'result_visibility', 'correct_answer_visibility', 'feedback_release_at',
        'opens_at', 'closes_at', 'integrity_acknowledgement_text', 'is_published', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'shuffle_questions' => 'boolean',
            'shuffle_options' => 'boolean',
            'negative_marking' => 'boolean',
            'is_published' => 'boolean',
            'pass_mark_percent' => 'decimal:2',
            'feedback_release_at' => 'datetime',
            'opens_at' => 'datetime',
            'closes_at' => 'datetime',
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

    public function questions(): HasMany
    {
        return $this->hasMany(QuizQuestion::class)->orderBy('position');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }

    public function isGraded(): bool
    {
        return $this->type !== self::TYPE_SURVEY;
    }

    public function isOpenForAttempts(): bool
    {
        $now = now();

        if ($this->opens_at && $now->lt($this->opens_at)) {
            return false;
        }

        if ($this->closes_at && $now->gt($this->closes_at)) {
            return false;
        }

        return true;
    }

    public function attemptsUsedBy(User $user): int
    {
        return $this->attempts()->where('user_id', $user->id)->count();
    }

    public function canBeAttemptedBy(User $user): bool
    {
        if (! $this->is_published || ! $this->isOpenForAttempts()) {
            return false;
        }

        if ($this->max_attempts === null) {
            return true;
        }

        return $this->attemptsUsedBy($user) < $this->max_attempts;
    }

    public function totalPoints(): float
    {
        return (float) $this->questions->sum('points');
    }

    public function resultsVisibleNow(): bool
    {
        return match ($this->result_visibility) {
            self::RESULT_IMMEDIATE => true,
            self::RESULT_AFTER_FEEDBACK_RELEASE => $this->feedback_release_at !== null && now()->gte($this->feedback_release_at),
            default => false,
        };
    }

    public function correctAnswersVisibleNow(): bool
    {
        return match ($this->correct_answer_visibility) {
            self::ANSWERS_NEVER => false,
            self::ANSWERS_AFTER_SUBMIT => true,
            self::ANSWERS_AFTER_CLOSE => $this->closes_at !== null && now()->gte($this->closes_at),
            self::ANSWERS_AFTER_FEEDBACK_RELEASE => $this->feedback_release_at !== null && now()->gte($this->feedback_release_at),
            default => false,
        };
    }
}
