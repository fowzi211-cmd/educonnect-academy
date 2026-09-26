<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizAttemptAnswer extends Model
{
    protected $fillable = [
        'quiz_attempt_id', 'quiz_question_id', 'selected_option_ids', 'short_answer_text',
        'numerical_answer', 'matching_answer', 'file_path', 'file_name',
        'is_correct', 'points_awarded', 'grader_feedback', 'graded_by', 'graded_at',
    ];

    protected function casts(): array
    {
        return [
            'selected_option_ids' => 'array',
            'matching_answer' => 'array',
            'numerical_answer' => 'decimal:4',
            'is_correct' => 'boolean',
            'points_awarded' => 'decimal:2',
            'graded_at' => 'datetime',
        ];
    }

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(QuizAttempt::class, 'quiz_attempt_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(QuizQuestion::class, 'quiz_question_id');
    }

    public function grader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }
}
