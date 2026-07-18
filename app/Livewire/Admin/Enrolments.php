<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use App\Models\Course;
use App\Models\Enrolment;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Enrolments extends Component
{
    use WithPagination;

    public string $studentEmail = '';
    public ?int $courseId = null;

    public ?int $cancellingId = null;
    public string $cancelReason = '';

    public function enrol(): void
    {
        $validated = $this->validate([
            'studentEmail' => ['required', 'email', 'exists:users,email'],
            'courseId' => ['required', 'exists:courses,id'],
        ]);

        $student = User::where('email', $validated['studentEmail'])->firstOrFail();

        if (Enrolment::where('user_id', $student->id)->where('course_id', $validated['courseId'])->where('status', Enrolment::STATUS_ACTIVE)->exists()) {
            $this->addError('studentEmail', __('This student is already actively enrolled in that course.'));

            return;
        }

        $enrolment = Enrolment::create([
            'user_id' => $student->id,
            'course_id' => $validated['courseId'],
            'status' => Enrolment::STATUS_ACTIVE,
            'source' => 'admin',
            'granted_by' => Auth::id(),
            'enrolled_at' => now(),
        ]);

        AuditLog::record('enrolment.granted', subject: $enrolment, new: [
            'user_id' => $student->id,
            'course_id' => $validated['courseId'],
        ]);

        $this->reset(['studentEmail', 'courseId']);
    }

    public function startCancel(int $id): void
    {
        $this->cancellingId = $id;
        $this->cancelReason = '';
    }

    public function confirmCancel(): void
    {
        $this->validate(['cancelReason' => ['required', 'string', 'max:1000']]);

        $enrolment = Enrolment::findOrFail($this->cancellingId);
        $enrolment->update(['status' => Enrolment::STATUS_CANCELLED]);

        AuditLog::record('enrolment.cancelled', subject: $enrolment, reason: $this->cancelReason);

        $this->cancellingId = null;
    }

    public function render()
    {
        return view('livewire.admin.enrolments', [
            'enrolments' => Enrolment::with('user', 'course')->latest('enrolled_at')->paginate(15),
            'courses' => Course::published()->orderBy('title')->get(),
        ]);
    }
}
