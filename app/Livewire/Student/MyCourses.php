<?php

namespace App\Livewire\Student;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class MyCourses extends Component
{
    public function render()
    {
        $courses = Auth::user()->enrolledCourses()->with('category', 'lecturers')->get();

        return view('livewire.student.my-courses', [
            'courses' => $courses,
        ]);
    }
}
