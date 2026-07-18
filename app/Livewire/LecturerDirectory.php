<?php

namespace App\Livewire;

use App\Models\LecturerProfile;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.public')]
class LecturerDirectory extends Component
{
    use WithPagination;

    public function render()
    {
        $lecturers = LecturerProfile::query()
            ->where('status', LecturerProfile::STATUS_APPROVED)
            ->with('user')
            ->paginate(12);

        return view('livewire.lecturer-directory', [
            'lecturers' => $lecturers,
        ]);
    }
}
