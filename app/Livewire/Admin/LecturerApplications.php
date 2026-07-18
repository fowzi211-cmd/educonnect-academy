<?php

namespace App\Livewire\Admin;

use App\Models\LecturerProfile;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class LecturerApplications extends Component
{
    use WithPagination;

    #[Url]
    public string $status = LecturerProfile::STATUS_PENDING;

    public function render()
    {
        $applications = LecturerProfile::query()
            ->with('user')
            ->when($this->status !== 'all', fn ($query) => $query->where('status', $this->status))
            ->latest()
            ->paginate(15);

        return view('livewire.admin.lecturer-applications', [
            'applications' => $applications,
        ]);
    }
}
