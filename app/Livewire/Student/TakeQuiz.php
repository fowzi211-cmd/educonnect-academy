<?php

namespace App\Livewire\Student;

use App\Models\AuditLog;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizQuestion;
use App\Services\Assessments\QuizGradingService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class TakeQuiz extends Component
{
    use WithFileUploads;

    public Quiz $quiz;

    public ?QuizAttempt $attempt = null;

    public bool $integrityAccepted = false;

    /** @var array<int, mixed> keyed by question id */
    public array $answers = [];

    /** @var array<int, mixed> keyed by question id, for file_upload questions */
    public array $files = [];

    public function mount(Quiz $quiz): void
    {
        abort_unless($quiz->course->isAccessibleBy(Auth::user()), 403);
        abort_unless($quiz->is_published, 404);

        $this->quiz = $quiz->load('questions.options');

        $this->attempt = $this->quiz->attempts()
            ->where('user_id', Auth::id())
            ->latest('started_at')
            ->first();

        $this->autoSubmitIfExpired();

        if ($this->attempt && $this->attempt->status === QuizAttempt::STATUS_IN_PROGRESS) {
            foreach ($this->attempt->answers as $answer) {
                $this->answers[$answer->quiz_question_id] = $this->answerToFormValue($answer);
            }
        }
    }

    protected function answerToFormValue($answer): mixed
    {
        return $answer->selected_option_ids
            ?? $answer->matching_answer
            ?? $answer->numerical_answer
            ?? $answer->short_answer_text;
    }

    public function checkExpiry(): void
    {
        $this->autoSubmitIfExpired();
    }

    protected function autoSubmitIfExpired(): void
    {
        if ($this->attempt && $this->attempt->status === QuizAttempt::STATUS_IN_PROGRESS && $this->attempt->isExpired()) {
            $this->submit(app(QuizGradingService::class), auto: true);
        }
    }

    public function startAttempt(): void
    {
        abort_unless($this->quiz->canBeAttemptedBy(Auth::user()), 403);

        if ($this->quiz->integrity_acknowledgement_text && ! $this->integrityAccepted) {
            $this->addError('integrityAccepted', __('You must accept the academic integrity statement to begin.'));

            return;
        }

        $questionIds = $this->quiz->questions->pluck('id');

        if ($this->quiz->shuffle_questions) {
            $questionIds = $questionIds->shuffle();
        }

        $now = now();

        $this->attempt = $this->quiz->attempts()->create([
            'user_id' => Auth::id(),
            'attempt_number' => $this->quiz->attemptsUsedBy(Auth::user()) + 1,
            'status' => QuizAttempt::STATUS_IN_PROGRESS,
            'question_order' => $questionIds->values()->all(),
            'started_at' => $now,
            'time_limit_expires_at' => $this->quiz->time_limit_minutes ? $now->copy()->addMinutes($this->quiz->time_limit_minutes) : null,
            'integrity_acknowledged_at' => $this->quiz->integrity_acknowledgement_text ? $now : null,
            'max_points' => $this->quiz->totalPoints(),
        ]);

        $this->answers = [];

        AuditLog::record('quiz_attempt.started', subject: $this->attempt);
    }

    public function selectSingle(int $questionId, int $optionId): void
    {
        $this->answers[$questionId] = [$optionId];
    }

    public function toggleMulti(int $questionId, int $optionId): void
    {
        $selected = collect($this->answers[$questionId] ?? []);

        $this->answers[$questionId] = $selected->contains($optionId)
            ? $selected->reject(fn ($id) => $id === $optionId)->values()->all()
            : $selected->push($optionId)->all();
    }

    public function setMatch(int $questionId, string $left, string $right): void
    {
        $this->answers[$questionId][$left] = $right;
    }

    public function submit(QuizGradingService $grading, bool $auto = false): void
    {
        if (! $this->attempt || $this->attempt->status !== QuizAttempt::STATUS_IN_PROGRESS) {
            return;
        }

        if ($this->attempt->user_id !== Auth::id()) {
            abort(403);
        }

        foreach ($this->quiz->questions as $question) {
            if ($question->type === QuizQuestion::TYPE_FILE_UPLOAD) {
                $this->storeFileAnswer($question);

                continue;
            }

            $grading->gradeAndStoreAnswer($this->attempt, $question, $this->answers[$question->id] ?? null);
        }

        $this->attempt->update([
            'status' => QuizAttempt::STATUS_SUBMITTED,
            'submitted_at' => now(),
        ]);

        $grading->finalizeIfFullyGraded($this->attempt);

        AuditLog::record($auto ? 'quiz_attempt.auto_submitted' : 'quiz_attempt.submitted', subject: $this->attempt);

        $this->attempt->refresh();
    }

    protected function storeFileAnswer(QuizQuestion $question): void
    {
        $file = $this->files[$question->id] ?? null;

        $existing = $this->attempt->answers()->where('quiz_question_id', $question->id)->first();

        if (! $file) {
            if (! $existing) {
                $this->attempt->answers()->create(['quiz_question_id' => $question->id]);
            }

            return;
        }

        $path = $file->store('quiz-submissions', 'local');

        $this->attempt->answers()->updateOrCreate(
            ['quiz_question_id' => $question->id],
            ['file_path' => $path, 'file_name' => $file->getClientOriginalName()]
        );
    }

    public function render()
    {
        $orderedQuestions = $this->orderedQuestions();

        return view('livewire.student.take-quiz', [
            'orderedQuestions' => $orderedQuestions,
            'attemptsUsed' => $this->quiz->attemptsUsedBy(Auth::user()),
            'canAttempt' => $this->quiz->canBeAttemptedBy(Auth::user()),
            'pastAttempts' => $this->quiz->attempts()
                ->where('user_id', Auth::id())
                ->where('status', '!=', QuizAttempt::STATUS_IN_PROGRESS)
                ->latest('started_at')
                ->get(),
        ]);
    }

    protected function orderedQuestions()
    {
        if (! $this->attempt || ! $this->attempt->question_order) {
            return $this->quiz->questions;
        }

        $byId = $this->quiz->questions->keyBy('id');

        return collect($this->attempt->question_order)
            ->map(fn ($id) => $byId->get($id))
            ->filter()
            ->map(function (QuizQuestion $question) {
                if ($this->quiz->shuffle_options && $question->options->isNotEmpty()) {
                    $question->setRelation('options', $question->options->shuffle($this->attempt->id + $question->id));
                }

                return $question;
            })
            ->values();
    }
}
