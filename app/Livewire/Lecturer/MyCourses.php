<?php

namespace App\Livewire\Lecturer;

use App\Models\Course;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class MyCourses extends Component
{
    public function render()
    {
        $courses = Course::query()
            ->where('created_by', Auth::id())
            ->latest()
            ->get();

        return view('livewire.lecturer.my-courses', [
            'courses' => $courses,
        ]);
    }
}
