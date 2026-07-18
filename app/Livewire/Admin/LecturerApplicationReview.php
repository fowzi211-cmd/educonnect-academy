<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use App\Models\LecturerProfile;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class LecturerApplicationReview extends Component
{
    public LecturerProfile $lecturerProfile;

    public string $rejection_reason = '';

    public function mount(LecturerProfile $lecturerProfile): void
    {
        $this->lecturerProfile = $lecturerProfile->load('user', 'documents');
    }

    public function approve(): void
    {
        $old = ['status' => $this->lecturerProfile->status];

        $this->lecturerProfile->update([
            'status' => LecturerProfile::STATUS_APPROVED,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'rejection_reason' => null,
        ]);

        $this->lecturerProfile->user->assignRole('lecturer');

        AuditLog::record(
            'lecturer_application.approved',
            subject: $this->lecturerProfile,
            old: $old,
            new: ['status' => LecturerProfile::STATUS_APPROVED],
        );

        $this->lecturerProfile->refresh();
    }

    public function reject(): void
    {
        $this->validate([
            'rejection_reason' => ['required', 'string', 'max:1000'],
        ]);

        $old = ['status' => $this->lecturerProfile->status];

        $this->lecturerProfile->update([
            'status' => LecturerProfile::STATUS_REJECTED,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'rejection_reason' => $this->rejection_reason,
        ]);

        AuditLog::record(
            'lecturer_application.rejected',
            subject: $this->lecturerProfile,
            old: $old,
            new: ['status' => LecturerProfile::STATUS_REJECTED],
            reason: $this->rejection_reason,
        );

        $this->lecturerProfile->refresh();
    }

    public function render()
    {
        return view('livewire.admin.lecturer-application-review');
    }
}
