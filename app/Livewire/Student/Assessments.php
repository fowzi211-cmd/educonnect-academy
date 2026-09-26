<?php

namespace App\Livewire\Student;

use App\Models\Assignment;
use App\Models\Quiz;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Assessments extends Component
{
    public function render()
    {
        $courseIds = Auth::user()->enrolledCourses()->pluck('courses.id');

        $quizzes = Quiz::whereIn('course_id', $courseIds)
            ->where('is_published', true)
            ->with('course')
            ->get()
            ->map(function (Quiz $quiz) {
                $attempt = $quiz->attempts()
                    ->where('user_id', Auth::id())
                    ->latest('started_at')
                    ->first();

                return [
                    'quiz' => $quiz,
                    'attempt' => $attempt,
                ];
            })
            ->sortBy(fn ($row) => $row['quiz']->closes_at ?? $row['quiz']->opens_at ?? $row['quiz']->created_at)
            ->values();

        $assignments = Assignment::whereIn('course_id', $courseIds)
            ->where('is_published', true)
            ->with('course')
            ->get()
            ->map(function (Assignment $assignment) {
                $submission = $assignment->submissions()
                    ->where('user_id', Auth::id())
                    ->first();

                return [
                    'assignment' => $assignment,
                    'submission' => $submission,
                ];
            })
            ->sortBy(fn ($row) => $row['assignment']->due_at ?? $row['assignment']->created_at)
            ->values();

        return view('livewire.student.assessments', [
            'quizzes' => $quizzes,
            'assignments' => $assignments,
        ]);
    }
}
