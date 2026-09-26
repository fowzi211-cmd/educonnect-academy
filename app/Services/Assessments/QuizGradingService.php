<?php

namespace App\Services\Assessments;

use App\Models\AuditLog;
use App\Models\QuizAttempt;
use App\Models\QuizAttemptAnswer;
use App\Models\QuizQuestion;
use Illuminate\Support\Facades\DB;

/**
 * Grades quiz answers. Auto-gradable question types are graded the instant
 * an answer is recorded; essay and file-upload questions are left ungraded
 * (points_awarded = null) until a lecturer grades them manually. An attempt
 * is only finalised (status = graded, score/pass computed) once every
 * question has a non-null points_awarded — see finalizeIfFullyGraded().
 */
class QuizGradingService
{
    /**
     * Grade a single auto-gradable question against submitted data and
     * return ['is_correct' => bool, 'points_awarded' => float]. Returns
     * nulls for question types that require manual grading.
     */
    public function gradeAnswer(QuizQuestion $question, mixed $submitted, bool $negativeMarking): array
    {
        if (! $question->isAutoGraded()) {
            return ['is_correct' => null, 'points_awarded' => null];
        }

        $isCorrect = match ($question->type) {
            QuizQuestion::TYPE_MCQ_SINGLE, QuizQuestion::TYPE_TRUE_FALSE => $this->gradeSingleChoice($question, $submitted),
            QuizQuestion::TYPE_MCQ_MULTI => $this->gradeMultiChoice($question, $submitted),
            QuizQuestion::TYPE_MATCHING => $this->gradeMatching($question, $submitted),
            QuizQuestion::TYPE_SHORT_ANSWER => $this->gradeShortAnswer($question, $submitted),
            QuizQuestion::TYPE_NUMERICAL => $this->gradeNumerical($question, $submitted),
            default => false,
        };

        $wasAnswered = $submitted !== null && $submitted !== '' && $submitted !== [];

        $points = match (true) {
            $isCorrect => (float) $question->points,
            $wasAnswered && $negativeMarking => -(float) $question->points,
            default => 0.0,
        };

        return ['is_correct' => $isCorrect, 'points_awarded' => $points];
    }

    protected function gradeSingleChoice(QuizQuestion $question, mixed $submitted): bool
    {
        if (! is_array($submitted) || count($submitted) !== 1) {
            return false;
        }

        $correctId = $question->options->firstWhere('is_correct', true)?->id;

        return $correctId !== null && (int) $submitted[0] === $correctId;
    }

    protected function gradeMultiChoice(QuizQuestion $question, mixed $submitted): bool
    {
        $submittedIds = collect(is_array($submitted) ? $submitted : [])->map(fn ($v) => (int) $v)->sort()->values();
        $correctIds = $question->options->where('is_correct', true)->pluck('id')->sort()->values();

        return $submittedIds->all() === $correctIds->all();
    }

    protected function gradeMatching(QuizQuestion $question, mixed $submitted): bool
    {
        if (! is_array($submitted)) {
            return false;
        }

        foreach ($question->matching_pairs ?? [] as $pair) {
            if (($submitted[$pair['left']] ?? null) !== $pair['right']) {
                return false;
            }
        }

        return true;
    }

    protected function gradeShortAnswer(QuizQuestion $question, mixed $submitted): bool
    {
        if (! is_string($submitted) || $question->correct_short_answer === null) {
            return false;
        }

        return trim(mb_strtolower($submitted)) === trim(mb_strtolower($question->correct_short_answer));
    }

    protected function gradeNumerical(QuizQuestion $question, mixed $submitted): bool
    {
        if (! is_numeric($submitted) || $question->correct_numerical === null) {
            return false;
        }

        return abs((float) $submitted - (float) $question->correct_numerical) <= (float) $question->numerical_tolerance;
    }

    /**
     * Recompute the attempt's totals and mark it graded once every answer
     * has a points_awarded value (i.e. nothing is still pending manual
     * grading). Safe to call after every grading action, auto or manual.
     */
    public function finalizeIfFullyGraded(QuizAttempt $attempt): void
    {
        DB::transaction(function () use ($attempt) {
            $attempt->refresh();

            if ($attempt->hasUngraded()) {
                return;
            }

            $scorePoints = (float) $attempt->answers()->sum('points_awarded');
            $maxPoints = (float) $attempt->max_points;
            $percent = $maxPoints > 0 ? round((max(0, $scorePoints) / $maxPoints) * 100, 2) : 0.0;

            $attempt->update([
                'status' => QuizAttempt::STATUS_GRADED,
                'score_points' => $scorePoints,
                'score_percent' => $percent,
                'passed' => $attempt->quiz->pass_mark_percent !== null
                    ? $percent >= (float) $attempt->quiz->pass_mark_percent
                    : null,
                'graded_at' => now(),
            ]);

            AuditLog::record('quiz_attempt.graded', subject: $attempt, new: [
                'score_points' => $scorePoints,
                'score_percent' => $percent,
                'passed' => $attempt->passed,
            ]);
        });
    }

    public function gradeAndStoreAnswer(QuizAttempt $attempt, QuizQuestion $question, mixed $submitted): QuizAttemptAnswer
    {
        $result = $this->gradeAnswer($question, $submitted, $attempt->quiz->negative_marking);

        return QuizAttemptAnswer::updateOrCreate(
            ['quiz_attempt_id' => $attempt->id, 'quiz_question_id' => $question->id],
            $this->answerPayload($question, $submitted) + $result
        );
    }

    protected function answerPayload(QuizQuestion $question, mixed $submitted): array
    {
        return match ($question->type) {
            QuizQuestion::TYPE_MCQ_SINGLE, QuizQuestion::TYPE_MCQ_MULTI, QuizQuestion::TYPE_TRUE_FALSE => [
                'selected_option_ids' => is_array($submitted) ? array_map('intval', $submitted) : null,
            ],
            QuizQuestion::TYPE_MATCHING => ['matching_answer' => is_array($submitted) ? $submitted : null],
            QuizQuestion::TYPE_SHORT_ANSWER, QuizQuestion::TYPE_ESSAY => ['short_answer_text' => is_string($submitted) ? $submitted : null],
            QuizQuestion::TYPE_NUMERICAL => ['numerical_answer' => is_numeric($submitted) ? $submitted : null],
            default => [],
        };
    }
}
