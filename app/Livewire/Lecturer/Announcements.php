<?php

namespace App\Livewire\Lecturer;

use App\Models\AuditLog;
use App\Models\Course;
use App\Notifications\AnnouncementPublished;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Announcements extends Component
{
    public Course $course;

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $title = '';

    public string $body = '';

    public bool $is_pinned = false;

    public function mount(Course $course): void
    {
        abort_unless($course->isTaughtBy(Auth::user()), 403);
        $this->course = $course;
    }

    public function newForm(): void
    {
        $this->reset(['editingId', 'title', 'body', 'is_pinned']);
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $announcement = $this->course->announcements()->findOrFail($id);

        $this->editingId = $announcement->id;
        $this->title = $announcement->title;
        $this->body = $announcement->body;
        $this->is_pinned = $announcement->is_pinned;
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
            'body' => ['required', 'string', 'max:5000'],
            'is_pinned' => ['boolean'],
        ];
    }

    public function save(): void
    {
        $validated = $this->validate();

        if ($this->editingId) {
            $announcement = $this->course->announcements()->findOrFail($this->editingId);
            $old = $announcement->only(array_keys($validated));
            $announcement->update($validated);
            AuditLog::record('announcement.updated', subject: $announcement, old: $old, new: $validated);
        } else {
            $announcement = $this->course->announcements()->create(array_merge($validated, [
                'created_by' => Auth::id(),
            ]));
            AuditLog::record('announcement.created', subject: $announcement, new: $validated);
        }

        $this->showForm = false;
    }

    public function togglePublish(int $id): void
    {
        $announcement = $this->course->announcements()->findOrFail($id);
        $wasPublished = $announcement->published_at !== null;

        $announcement->update([
            'published_at' => $wasPublished ? null : now(),
        ]);

        AuditLog::record($announcement->published_at ? 'announcement.published' : 'announcement.unpublished', subject: $announcement);

        if (! $wasPublished && $announcement->published_at) {
            $students = $this->course->enrolments()->where('status', 'active')->with('user')->get()->pluck('user');

            if ($students->isNotEmpty()) {
                Notification::send($students, new AnnouncementPublished($announcement));
            }
        }
    }

    public function delete(int $id): void
    {
        $announcement = $this->course->announcements()->findOrFail($id);

        AuditLog::record('announcement.deleted', subject: $announcement, old: ['title' => $announcement->title]);

        $announcement->delete();
    }

    public function render()
    {
        return view('livewire.lecturer.announcements', [
            'announcements' => $this->course->announcements()->latest('is_pinned')->latest('created_at')->get(),
        ]);
    }
}
