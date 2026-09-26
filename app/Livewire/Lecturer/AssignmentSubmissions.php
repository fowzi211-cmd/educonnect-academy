<?php

namespace App\Livewire\Lecturer;

use App\Models\Assignment;
use App\Models\AuditLog;
use App\Notifications\AssignmentGraded;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class AssignmentSubmissions extends Component
{
    public Assignment $assignment;

    public ?int $gradingId = null;

    public string $score = '';

    public string $feedback = '';

    public function mount(Assignment $assignment): void
    {
        abort_unless($assignment->course->isTaughtBy(Auth::user()), 403);
        $this->assignment = $assignment;
    }

    public function startGrade(int $submissionId): void
    {
        $submission = $this->assignment->submissions()->findOrFail($submissionId);

        $this->gradingId = $submission->id;
        $this->score = $submission->score !== null ? (string) $submission->score : '';
        $this->feedback = (string) $submission->feedback;
    }

    public function cancelGrade(): void
    {
        $this->gradingId = null;
    }

    public function saveGrade(): void
    {
        $this->validate([
            'score' => ['required', 'numeric', 'min:0', 'max:'.$this->assignment->max_points],
            'feedback' => ['nullable', 'string', 'max:2000'],
        ]);

        $submission = $this->assignment->submissions()->with('user')->findOrFail($this->gradingId);

        $submission->update([
            'score' => $this->score,
            'feedback' => $this->feedback ?: null,
            'status' => 'graded',
            'graded_by' => Auth::id(),
            'graded_at' => now(),
        ]);

        AuditLog::record('assignment_submission.graded', subject: $submission, new: [
            'score' => $this->score,
        ]);

        $submission->user->notify(new AssignmentGraded($submission));

        $this->gradingId = null;
    }

    public function render()
    {
        return view('livewire.lecturer.assignment-submissions', [
            'submissions' => $this->assignment->submissions()->with('user')->latest('submitted_at')->get(),
        ]);
    }
}
