<?php

namespace App\Livewire\Lecturer;

use App\Models\Assignment;
use App\Models\AuditLog;
use App\Models\Course;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class Assignments extends Component
{
    use WithFileUploads;

    public Course $course;

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $title = '';

    public string $description = '';

    public string $max_points = '100';

    public string $opens_at = '';

    public string $due_at = '';

    public string $late_policy = Assignment::LATE_NOT_ALLOWED;

    public string $late_penalty_percent_per_day = '';

    public string $allowed_file_types = '';

    public string $max_file_size_kb = '10240';

    public $attachmentFile = null;

    public ?string $editingAttachmentName = null;

    public bool $removeAttachment = false;

    public function mount(Course $course): void
    {
        abort_unless($course->isTaughtBy(Auth::user()), 403);
        $this->course = $course;
    }

    public function newForm(): void
    {
        $this->reset([
            'editingId', 'title', 'description', 'opens_at', 'due_at',
            'late_penalty_percent_per_day', 'allowed_file_types',
            'attachmentFile', 'editingAttachmentName', 'removeAttachment',
        ]);
        $this->max_points = '100';
        $this->late_policy = Assignment::LATE_NOT_ALLOWED;
        $this->max_file_size_kb = '10240';
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $assignment = $this->course->assignments()->findOrFail($id);

        $this->editingId = $assignment->id;
        $this->title = $assignment->title;
        $this->description = (string) $assignment->description;
        $this->max_points = (string) $assignment->max_points;
        $this->opens_at = $assignment->opens_at?->format('Y-m-d\TH:i') ?? '';
        $this->due_at = $assignment->due_at?->format('Y-m-d\TH:i') ?? '';
        $this->late_policy = $assignment->late_policy;
        $this->late_penalty_percent_per_day = (string) ($assignment->late_penalty_percent_per_day ?? '');
        $this->allowed_file_types = (string) $assignment->allowed_file_types;
        $this->max_file_size_kb = (string) $assignment->max_file_size_kb;
        $this->attachmentFile = null;
        $this->editingAttachmentName = $assignment->attachment_name;
        $this->removeAttachment = false;
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
            'description' => ['nullable', 'string', 'max:5000'],
            'max_points' => ['required', 'numeric', 'min:1'],
            'opens_at' => ['nullable', 'date'],
            // "after:opens_at" only makes sense once opens_at actually has a
            // value — with it blank the rule fails to parse an empty date.
            'due_at' => array_filter(['nullable', 'date', $this->opens_at ? 'after:opens_at' : null]),
            'late_policy' => ['required', 'in:not_allowed,allowed_with_penalty,allowed_no_penalty'],
            'late_penalty_percent_per_day' => ['nullable', 'numeric', 'min:0', 'max:100', 'required_if:late_policy,allowed_with_penalty'],
            'allowed_file_types' => ['nullable', 'string', 'max:255'],
            'max_file_size_kb' => ['required', 'integer', 'min:1', 'max:102400'],
            'attachmentFile' => ['nullable', 'file', 'max:10240'],
        ];
    }

    public function save(): void
    {
        $validated = $this->validate();
        $validated['opens_at'] = $validated['opens_at'] ?: null;
        $validated['due_at'] = $validated['due_at'] ?: null;
        $validated['late_penalty_percent_per_day'] = $validated['late_penalty_percent_per_day'] ?: null;
        $validated['allowed_file_types'] = $validated['allowed_file_types'] ? strtolower(str_replace(' ', '', $validated['allowed_file_types'])) : null;
        unset($validated['attachmentFile']);

        if ($this->editingId) {
            $assignment = $this->course->assignments()->findOrFail($this->editingId);
        } else {
            $assignment = null;
        }

        if ($this->attachmentFile) {
            if ($assignment?->attachment_path) {
                Storage::disk('local')->delete($assignment->attachment_path);
            }

            $validated['attachment_path'] = $this->attachmentFile->store('assignment-attachments', 'local');
            $validated['attachment_name'] = $this->attachmentFile->getClientOriginalName();
        } elseif ($this->removeAttachment && $assignment?->attachment_path) {
            Storage::disk('local')->delete($assignment->attachment_path);
            $validated['attachment_path'] = null;
            $validated['attachment_name'] = null;
        }

        if ($assignment) {
            $old = $assignment->only(array_keys($validated));
            $assignment->update($validated);
            AuditLog::record('assignment.updated', subject: $assignment, old: $old, new: $validated);
        } else {
            $assignment = $this->course->assignments()->create(array_merge($validated, [
                'created_by' => Auth::id(),
            ]));
            AuditLog::record('assignment.created', subject: $assignment, new: $validated);
        }

        $this->attachmentFile = null;
        $this->removeAttachment = false;
        $this->showForm = false;
    }

    public function togglePublish(int $id): void
    {
        $assignment = $this->course->assignments()->findOrFail($id);
        $assignment->update(['is_published' => ! $assignment->is_published]);

        AuditLog::record($assignment->is_published ? 'assignment.published' : 'assignment.unpublished', subject: $assignment);
    }

    public function delete(int $id): void
    {
        $assignment = $this->course->assignments()->withCount('submissions')->findOrFail($id);

        if ($assignment->submissions_count > 0) {
            $this->addError('delete', __('This assignment already has student submissions and cannot be deleted. Unpublish it instead.'));

            return;
        }

        if ($assignment->attachment_path) {
            Storage::disk('local')->delete($assignment->attachment_path);
        }

        AuditLog::record('assignment.deleted', subject: $assignment, old: ['title' => $assignment->title]);

        $assignment->delete();
    }

    public function render()
    {
        return view('livewire.lecturer.assignments', [
            'assignments' => $this->course->assignments()->withCount('submissions')->latest()->get(),
        ]);
    }
}
