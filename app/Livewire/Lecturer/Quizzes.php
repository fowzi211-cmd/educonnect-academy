<?php

namespace App\Livewire\Lecturer;

use App\Models\AuditLog;
use App\Models\Course;
use App\Models\Quiz;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Quizzes extends Component
{
    public Course $course;

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $title = '';

    public string $description = '';

    public string $type = Quiz::TYPE_QUIZ;

    public string $time_limit_minutes = '';

    public string $max_attempts = '';

    public string $pass_mark_percent = '';

    public bool $shuffle_questions = false;

    public bool $shuffle_options = false;

    public bool $negative_marking = false;

    public string $result_visibility = Quiz::RESULT_IMMEDIATE;

    public string $correct_answer_visibility = Quiz::ANSWERS_AFTER_SUBMIT;

    public string $feedback_release_at = '';

    public string $opens_at = '';

    public string $closes_at = '';

    public string $integrity_acknowledgement_text = '';

    public function mount(Course $course): void
    {
        abort_unless($course->isTaughtBy(Auth::user()), 403);
        $this->course = $course;
    }

    public function newForm(): void
    {
        $this->reset([
            'editingId', 'title', 'description', 'time_limit_minutes', 'max_attempts',
            'pass_mark_percent', 'shuffle_questions', 'shuffle_options', 'negative_marking',
            'result_visibility', 'correct_answer_visibility', 'feedback_release_at',
            'opens_at', 'closes_at', 'integrity_acknowledgement_text',
        ]);
        $this->type = Quiz::TYPE_QUIZ;
        $this->result_visibility = Quiz::RESULT_IMMEDIATE;
        $this->correct_answer_visibility = Quiz::ANSWERS_AFTER_SUBMIT;
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $quiz = $this->course->quizzes()->findOrFail($id);

        $this->editingId = $quiz->id;
        $this->title = $quiz->title;
        $this->description = (string) $quiz->description;
        $this->type = $quiz->type;
        $this->time_limit_minutes = (string) ($quiz->time_limit_minutes ?? '');
        $this->max_attempts = (string) ($quiz->max_attempts ?? '');
        $this->pass_mark_percent = (string) ($quiz->pass_mark_percent ?? '');
        $this->shuffle_questions = $quiz->shuffle_questions;
        $this->shuffle_options = $quiz->shuffle_options;
        $this->negative_marking = $quiz->negative_marking;
        $this->result_visibility = $quiz->result_visibility;
        $this->correct_answer_visibility = $quiz->correct_answer_visibility;
        $this->feedback_release_at = $quiz->feedback_release_at?->format('Y-m-d\TH:i') ?? '';
        $this->opens_at = $quiz->opens_at?->format('Y-m-d\TH:i') ?? '';
        $this->closes_at = $quiz->closes_at?->format('Y-m-d\TH:i') ?? '';
        $this->integrity_acknowledgement_text = (string) $quiz->integrity_acknowledgement_text;
        $this->showForm = true;
    }

    public function cancelForm(): void
    {
        $this->showForm = false;
    }

    protected function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'type' => ['required', 'in:'.implode(',', Quiz::TYPES)],
            'time_limit_minutes' => ['nullable', 'integer', 'min:1'],
            'max_attempts' => ['nullable', 'integer', 'min:1'],
            'pass_mark_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'shuffle_questions' => ['boolean'],
            'shuffle_options' => ['boolean'],
            'negative_marking' => ['boolean'],
            'result_visibility' => ['required', 'in:immediate,after_feedback_release,manual'],
            'correct_answer_visibility' => ['required', 'in:never,after_submit,after_close,after_feedback_release'],
            'feedback_release_at' => ['nullable', 'date'],
            'opens_at' => ['nullable', 'date'],
            // "after:opens_at" only makes sense once opens_at actually has a
            // value — with it blank the rule fails to parse an empty date.
            'closes_at' => array_filter(['nullable', 'date', $this->opens_at ? 'after:opens_at' : null]),
            'integrity_acknowledgement_text' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function save(): void
    {
        $validated = $this->validate();
        $validated['time_limit_minutes'] = $validated['time_limit_minutes'] ?: null;
        $validated['max_attempts'] = $validated['max_attempts'] ?: null;
        $validated['pass_mark_percent'] = $validated['pass_mark_percent'] !== null && $validated['pass_mark_percent'] !== '' ? $validated['pass_mark_percent'] : null;
        $validated['feedback_release_at'] = $validated['feedback_release_at'] ?: null;
        $validated['opens_at'] = $validated['opens_at'] ?: null;
        $validated['closes_at'] = $validated['closes_at'] ?: null;
        $validated['integrity_acknowledgement_text'] = $validated['integrity_acknowledgement_text'] ?: null;

        if ($this->editingId) {
            $quiz = $this->course->quizzes()->findOrFail($this->editingId);
            $old = $quiz->only(array_keys($validated));
            $quiz->update($validated);
            AuditLog::record('quiz.updated', subject: $quiz, old: $old, new: $validated);
        } else {
            $quiz = $this->course->quizzes()->create(array_merge($validated, [
                'created_by' => Auth::id(),
            ]));
            AuditLog::record('quiz.created', subject: $quiz, new: $validated);
        }

        $this->showForm = false;
    }

    public function togglePublish(int $id): void
    {
        $quiz = $this->course->quizzes()->withCount('questions')->findOrFail($id);

        if (! $quiz->is_published && $quiz->questions_count === 0) {
            $this->addError('publish', __('Add at least one question before publishing this assessment.'));

            return;
        }

        $quiz->update(['is_published' => ! $quiz->is_published]);

        AuditLog::record($quiz->is_published ? 'quiz.published' : 'quiz.unpublished', subject: $quiz);
    }

    public function delete(int $id): void
    {
        $quiz = $this->course->quizzes()->withCount('attempts')->findOrFail($id);

        if ($quiz->attempts_count > 0) {
            $this->addError('delete', __('This assessment already has student attempts and cannot be deleted. Unpublish it instead.'));

            return;
        }

        AuditLog::record('quiz.deleted', subject: $quiz, old: ['title' => $quiz->title]);

        $quiz->delete();
    }

    public function render()
    {
        return view('livewire.lecturer.quizzes', [
            'quizzes' => $this->course->quizzes()->withCount(['questions', 'attempts'])->latest()->get(),
        ]);
    }
}
