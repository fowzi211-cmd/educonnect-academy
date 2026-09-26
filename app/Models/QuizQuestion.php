<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuizQuestion extends Model
{
    public const TYPE_MCQ_SINGLE = 'mcq_single';

    public const TYPE_MCQ_MULTI = 'mcq_multi';

    public const TYPE_TRUE_FALSE = 'true_false';

    public const TYPE_MATCHING = 'matching';

    public const TYPE_SHORT_ANSWER = 'short_answer';

    public const TYPE_ESSAY = 'essay';

    public const TYPE_NUMERICAL = 'numerical';

    public const TYPE_FILE_UPLOAD = 'file_upload';

    public const TYPES = [
        self::TYPE_MCQ_SINGLE, self::TYPE_MCQ_MULTI, self::TYPE_TRUE_FALSE, self::TYPE_MATCHING,
        self::TYPE_SHORT_ANSWER, self::TYPE_ESSAY, self::TYPE_NUMERICAL, self::TYPE_FILE_UPLOAD,
    ];

    /** Question types the server can grade automatically on submission. */
    public const AUTO_GRADED_TYPES = [
        self::TYPE_MCQ_SINGLE, self::TYPE_MCQ_MULTI, self::TYPE_TRUE_FALSE,
        self::TYPE_MATCHING, self::TYPE_SHORT_ANSWER, self::TYPE_NUMERICAL,
    ];

    /** Question types that need a lecturer to grade them. */
    public const MANUALLY_GRADED_TYPES = [self::TYPE_ESSAY, self::TYPE_FILE_UPLOAD];

    protected $fillable = [
        'quiz_id', 'type', 'prompt', 'points', 'position',
        'correct_short_answer', 'correct_numerical', 'numerical_tolerance',
        'matching_pairs', 'explanation',
    ];

    protected function casts(): array
    {
        return [
            'points' => 'decimal:2',
            'correct_numerical' => 'decimal:4',
            'numerical_tolerance' => 'decimal:4',
            'matching_pairs' => 'array',
        ];
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(QuizQuestionOption::class)->orderBy('position');
    }

    public function isAutoGraded(): bool
    {
        return in_array($this->type, self::AUTO_GRADED_TYPES, true);
    }
}
