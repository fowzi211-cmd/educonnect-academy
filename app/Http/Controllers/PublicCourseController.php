<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\LecturerProfile;
use App\Models\User;
use Illuminate\View\View;

class PublicCourseController extends Controller
{
    public function show(string $slug): View
    {
        $course = Course::query()
            ->published()
            ->with('category', 'lecturers.lecturerProfile')
            ->where('slug', $slug)
            ->firstOrFail();

        return view('public.course-details', [
            'course' => $course,
            'title' => $course->title,
            'description' => $course->short_description,
        ]);
    }

    public function lecturerProfile(User $user): View
    {
        $profile = LecturerProfile::query()
            ->where('user_id', $user->id)
            ->where('status', LecturerProfile::STATUS_APPROVED)
            ->with('user')
            ->firstOrFail();

        $courses = $user->coursesTeaching()->published()->get();

        return view('public.lecturer-profile', [
            'profile' => $profile,
            'courses' => $courses,
            'title' => $user->name,
        ]);
    }
}
