<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use App\Models\Course;
use App\Models\CourseReviewer;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class CourseReview extends Component
{
    public Course $course;

    public string $comments = '';
    public string $statusNote = '';

    public function mount(Course $course): void
    {
        $this->course = $course->load('category', 'creator', 'lecturers');
    }

    protected function recordDecision(string $decision, ?string $comments = null): void
    {
        CourseReviewer::updateOrCreate(
            ['course_id' => $this->course->id, 'user_id' => Auth::id()],
            ['decision' => $decision, 'comments' => $comments, 'reviewed_at' => now()]
        );
    }

    public function approve(): void
    {
        abort_unless(Auth::user()->can('review courses'), 403);
        abort_unless($this->course->status === Course::STATUS_UNDER_REVIEW, 403);

        $old = ['status' => $this->course->status];
        $this->course->update(['status' => Course::STATUS_APPROVED, 'revision_notes' => null]);
        $this->recordDecision('approved', $this->comments ?: null);

        AuditLog::record('course.approved', subject: $this->course, old: $old, new: ['status' => Course::STATUS_APPROVED]);

        $this->comments = '';
        $this->course->refresh();
    }

    public function requestRevision(): void
    {
        abort_unless(Auth::user()->can('review courses'), 403);
        abort_unless($this->course->status === Course::STATUS_UNDER_REVIEW, 403);

        $this->validate(['comments' => ['required', 'string', 'max:2000']]);

        $old = ['status' => $this->course->status];
        $this->course->update(['status' => Course::STATUS_REVISION_REQUESTED, 'revision_notes' => $this->comments]);
        $this->recordDecision('revision_requested', $this->comments);

        AuditLog::record('course.revision_requested', subject: $this->course, old: $old, new: ['status' => Course::STATUS_REVISION_REQUESTED], reason: $this->comments);

        $this->comments = '';
        $this->course->refresh();
    }

    public function publish(): void
    {
        abort_unless(Auth::user()->can('publish courses'), 403);
        abort_unless($this->course->status === Course::STATUS_APPROVED, 403);

        $old = ['status' => $this->course->status];
        $this->course->update(['status' => Course::STATUS_PUBLISHED, 'published_at' => now()]);

        AuditLog::record('course.published', subject: $this->course, old: $old, new: ['status' => Course::STATUS_PUBLISHED]);

        $this->course->refresh();
    }

    public function unpublish(): void
    {
        abort_unless(Auth::user()->can('publish courses'), 403);
        abort_unless($this->course->status === Course::STATUS_PUBLISHED, 403);

        $old = ['status' => $this->course->status];
        $this->course->update(['status' => Course::STATUS_UNPUBLISHED]);

        AuditLog::record('course.unpublished', subject: $this->course, old: $old, new: ['status' => Course::STATUS_UNPUBLISHED]);

        $this->course->refresh();
    }

    public function suspend(): void
    {
        abort_unless(Auth::user()->can('publish courses'), 403);
        abort_unless(in_array($this->course->status, [Course::STATUS_PUBLISHED, Course::STATUS_APPROVED], true), 403);

        $this->validate(['statusNote' => ['required', 'string', 'max:2000']]);

        $old = ['status' => $this->course->status];
        $this->course->update(['status' => Course::STATUS_SUSPENDED, 'revision_notes' => $this->statusNote]);

        AuditLog::record('course.suspended', subject: $this->course, old: $old, new: ['status' => Course::STATUS_SUSPENDED], reason: $this->statusNote);

        $this->statusNote = '';
        $this->course->refresh();
    }

    public function archive(): void
    {
        abort_unless(Auth::user()->can('publish courses'), 403);
        abort_unless(in_array($this->course->status, [Course::STATUS_PUBLISHED, Course::STATUS_UNPUBLISHED, Course::STATUS_SUSPENDED], true), 403);

        $old = ['status' => $this->course->status];
        $this->course->update(['status' => Course::STATUS_ARCHIVED]);

        AuditLog::record('course.archived', subject: $this->course, old: $old, new: ['status' => Course::STATUS_ARCHIVED]);

        $this->course->refresh();
    }

    public function render()
    {
        return view('livewire.admin.course-review');
    }
}
