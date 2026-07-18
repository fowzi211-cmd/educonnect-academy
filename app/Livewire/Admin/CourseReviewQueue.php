<?php

namespace App\Livewire\Admin;

use App\Models\Course;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class CourseReviewQueue extends Component
{
    use WithPagination;

    #[Url]
    public string $status = Course::STATUS_UNDER_REVIEW;

    public function render()
    {
        $courses = Course::query()
            ->with('category', 'creator')
            ->when($this->status !== 'all', fn ($query) => $query->where('status', $this->status))
            ->latest('submitted_at')
            ->paginate(15);

        return view('livewire.admin.course-review-queue', [
            'courses' => $courses,
        ]);
    }
}
