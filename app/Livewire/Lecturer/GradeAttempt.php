<?php

namespace App\Livewire\Lecturer;

use App\Models\QuizAttempt;
use App\Models\QuizAttemptAnswer;
use App\Notifications\QuizGraded;
use App\Services\Assessments\CourseCompletionService;
use App\Services\Assessments\QuizGradingService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class GradeAttempt extends Component
{
    public QuizAttempt $attempt;

    /** @var array<int, string> keyed by answer id */
    public array $scores = [];

    /** @var array<int, string> keyed by answer id */
    public array $feedback = [];

    public function mount(QuizAttempt $attempt): void
    {
        abort_unless($attempt->quiz->course->isTaughtBy(Auth::user()), 403);

        $this->attempt = $attempt->load('user', 'quiz', 'answers.question.options');

        foreach ($this->attempt->answers as $answer) {
            if ($answer->question->isAutoGraded()) {
                continue;
            }

            $this->scores[$answer->id] = $answer->points_awarded !== null ? (string) $answer->points_awarded : '';
            $this->feedback[$answer->id] = (string) $answer->grader_feedback;
        }
    }

    public function gradeAnswer(int $answerId, QuizGradingService $grading, CourseCompletionService $completion): void
    {
        $answer = $this->attempt->answers()->with('question')->findOrFail($answerId);

        $this->validate([
            "scores.{$answerId}" => ['required', 'numeric', 'min:0', 'max:'.$answer->question->points],
            "feedback.{$answerId}" => ['nullable', 'string', 'max:2000'],
        ]);

        $wasAlreadyGraded = $this->attempt->status === QuizAttempt::STATUS_GRADED;

        $answer->update([
            'points_awarded' => $this->scores[$answerId],
            'grader_feedback' => $this->feedback[$answerId] ?: null,
            'graded_by' => Auth::id(),
            'graded_at' => now(),
        ]);

        $grading->finalizeIfFullyGraded($this->attempt);

        $this->attempt->refresh();

        if ($this->attempt->status === QuizAttempt::STATUS_GRADED) {
            if (! $wasAlreadyGraded) {
                $this->attempt->user->notify(new QuizGraded($this->attempt));
            }

            if ($this->attempt->passed) {
                $completion->checkAndRecordCompletion($this->attempt->user, $this->attempt->quiz->course);
            }
        }
    }

    public function render()
    {
        return view('livewire.lecturer.grade-attempt', [
            'answers' => $this->attempt->answers()->with('question.options')->get()
                ->sortBy(fn (QuizAttemptAnswer $a) => $a->question->position),
        ]);
    }
}
