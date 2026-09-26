<?php

namespace App\Livewire\Discussions;

use App\Models\AuditLog;
use App\Models\Course;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Index extends Component
{
    public Course $course;

    public bool $showForm = false;

    public string $title = '';

    public string $body = '';

    public string $lessonId = '';

    public function mount(Course $course): void
    {
        abort_unless($course->isAccessibleBy(Auth::user()), 403);
        $this->course = $course->load('sections.lessons');
    }

    public function newForm(): void
    {
        $this->reset(['title', 'body', 'lessonId']);
        $this->showForm = true;
    }

    public function cancelForm(): void
    {
        $this->showForm = false;
    }

    public function create(): void
    {
        $validated = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:5000'],
            'lessonId' => ['nullable', 'integer'],
        ]);

        $thread = $this->course->discussionThreads()->create([
            'lesson_id' => $validated['lessonId'] ?: null,
            'user_id' => Auth::id(),
            'title' => $validated['title'],
            'body' => $validated['body'],
        ]);

        AuditLog::record('discussion_thread.created', subject: $thread);

        $this->showForm = false;

        $this->redirect(route('courses.discussions.show', $thread), navigate: true);
    }

    public function render()
    {
        $isModerator = $this->course->isTaughtBy(Auth::user()) || Auth::user()->can('publish courses');

        $threads = $this->course->discussionThreads()
            ->with('user', 'lesson', 'replies')
            ->when(! $isModerator, fn ($q) => $q->where('is_hidden', false))
            ->latest('is_pinned')
            ->latest('created_at')
            ->get();

        return view('livewire.discussions.index', [
            'threads' => $threads,
            'isModerator' => $isModerator,
        ]);
    }
}
