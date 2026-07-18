<?php

namespace App\Livewire;

use App\Models\LecturerDocument;
use App\Models\LecturerProfile;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class LecturerApplicationForm extends Component
{
    use WithFileUploads;

    public string $headline = '';
    public string $biography = '';
    public string $qualifications = '';
    public string $areas_of_expertise = '';

    public $photo;
    public $documents = [];

    public ?LecturerProfile $existingProfile = null;

    public bool $submitted = false;

    public function mount(): void
    {
        $this->existingProfile = Auth::user()->lecturerProfile;

        if ($this->existingProfile && $this->existingProfile->status === LecturerProfile::STATUS_REJECTED) {
            $this->headline = $this->existingProfile->headline ?? '';
            $this->biography = $this->existingProfile->biography ?? '';
            $this->qualifications = $this->existingProfile->qualifications ?? '';
            $this->areas_of_expertise = $this->existingProfile->areas_of_expertise ?? '';
        }
    }

    public function apply(): void
    {
        $validated = $this->validate([
            'headline' => ['required', 'string', 'max:255'],
            'biography' => ['required', 'string', 'max:5000'],
            'qualifications' => ['required', 'string', 'max:5000'],
            'areas_of_expertise' => ['required', 'string', 'max:1000'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'documents.*' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        $user = Auth::user();

        $profile = LecturerProfile::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'headline' => $validated['headline'],
                'biography' => $validated['biography'],
                'qualifications' => $validated['qualifications'],
                'areas_of_expertise' => $validated['areas_of_expertise'],
                'status' => LecturerProfile::STATUS_PENDING,
                'rejection_reason' => null,
                'reviewed_by' => null,
                'reviewed_at' => null,
            ]
        );

        if ($this->photo) {
            $profile->update(['photo_path' => $this->photo->store('lecturer-photos', 'public')]);
        }

        foreach ($this->documents as $document) {
            $path = $document->store('lecturer-documents', 'local');

            LecturerDocument::create([
                'lecturer_profile_id' => $profile->id,
                'original_name' => $document->getClientOriginalName(),
                'file_path' => $path,
            ]);
        }

        $this->existingProfile = $profile->fresh();
        $this->submitted = true;
    }

    public function render()
    {
        return view('livewire.lecturer-application-form');
    }
}
