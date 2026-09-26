<?php

namespace App\Livewire\Lecturer;

use App\Models\AuditLog;
use App\Models\Course;
use App\Models\CourseCompletionCriteria;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class CompletionCriteria extends Component
{
    public Course $course;

    public bool $enabled = false;

    public string $min_lessons_percent = '';

    public string $min_video_watch_percent = '';

    public string $min_attendance_percent = '';

    public bool $require_assignments = false;

    public bool $require_passing_assessments = false;

    public bool $require_payment_good_standing = true;

    public function mount(Course $course): void
    {
        abort_unless($course->isTaughtBy(Auth::user()), 403);
        $this->course = $course;

        if ($criteria = $course->completionCriteria) {
            $this->enabled = true;
            $this->min_lessons_percent = (string) ($criteria->min_lessons_percent ?? '');
            $this->min_video_watch_percent = (string) ($criteria->min_video_watch_percent ?? '');
            $this->min_attendance_percent = (string) ($criteria->min_attendance_percent ?? '');
            $this->require_assignments = $criteria->require_assignments;
            $this->require_passing_assessments = $criteria->require_passing_assessments;
            $this->require_payment_good_standing = $criteria->require_payment_good_standing;
        }
    }

    public function save(): void
    {
        if (! $this->enabled) {
            if ($this->course->completionCriteria) {
                $this->course->completionCriteria->delete();
                AuditLog::record('course.completion_criteria.disabled', subject: $this->course);
            }

            return;
        }

        $validated = $this->validate([
            'min_lessons_percent' => ['nullable', 'integer', 'min:0', 'max:100'],
            'min_video_watch_percent' => ['nullable', 'integer', 'min:0', 'max:100'],
            'min_attendance_percent' => ['nullable', 'integer', 'min:0', 'max:100'],
            'require_assignments' => ['boolean'],
            'require_passing_assessments' => ['boolean'],
            'require_payment_good_standing' => ['boolean'],
        ]);

        $validated['min_lessons_percent'] = $validated['min_lessons_percent'] ?: null;
        $validated['min_video_watch_percent'] = $validated['min_video_watch_percent'] ?: null;
        $validated['min_attendance_percent'] = $validated['min_attendance_percent'] ?: null;

        CourseCompletionCriteria::updateOrCreate(
            ['course_id' => $this->course->id],
            $validated
        );

        AuditLog::record('course.completion_criteria.saved', subject: $this->course, new: $validated);
    }

    public function render()
    {
        return view('livewire.lecturer.completion-criteria', [
            'completions' => $this->course->completions()->with('user')->latest('completed_at')->get(),
        ]);
    }
}
