<?php

namespace App\Livewire\Student;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\AuditLog;
use App\Services\Assessments\CourseCompletionService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class SubmitAssignment extends Component
{
    use WithFileUploads;

    public Assignment $assignment;

    public $file;

    public function mount(Assignment $assignment): void
    {
        abort_unless($assignment->course->isAccessibleBy(Auth::user()), 403);
        abort_unless($assignment->is_published, 404);

        $this->assignment = $assignment;
    }

    public function submit(CourseCompletionService $completion): void
    {
        abort_unless($this->assignment->isOpen(), 403);

        $existing = $this->currentSubmission();

        if ($existing && $existing->status === AssignmentSubmission::STATUS_GRADED) {
            $this->addError('file', __('This assignment has already been graded and can no longer be resubmitted.'));

            return;
        }

        $rules = ['file' => ['required', 'file', 'max:'.$this->assignment->max_file_size_kb]];

        if ($extensions = $this->assignment->allowedExtensions()) {
            $rules['file'][] = 'mimes:'.implode(',', $extensions);
        }

        $this->validate($rules);

        $path = $this->file->store('assignment-submissions', 'local');

        $submission = AssignmentSubmission::updateOrCreate(
            ['assignment_id' => $this->assignment->id, 'user_id' => Auth::id()],
            [
                'file_path' => $path,
                'file_name' => $this->file->getClientOriginalName(),
                'submitted_at' => now(),
                'is_late' => $this->assignment->isPastDue(),
                'status' => AssignmentSubmission::STATUS_SUBMITTED,
                'score' => null,
                'feedback' => null,
                'graded_by' => null,
                'graded_at' => null,
            ]
        );

        AuditLog::record('assignment_submission.submitted', subject: $submission, new: [
            'is_late' => $submission->is_late,
        ]);

        $completion->checkAndRecordCompletion(Auth::user(), $this->assignment->course);

        $this->file = null;
    }

    protected function currentSubmission(): ?AssignmentSubmission
    {
        return $this->assignment->submissions()->where('user_id', Auth::id())->first();
    }

    public function render()
    {
        return view('livewire.student.submit-assignment', [
            'submission' => $this->currentSubmission(),
        ]);
    }
}
