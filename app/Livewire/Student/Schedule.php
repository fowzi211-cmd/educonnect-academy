<?php

namespace App\Livewire\Student;

use App\Models\LiveClass;
use App\Models\Quiz;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Schedule extends Component
{
    public function render()
    {
        $user = Auth::user();
        $courseIds = $user->enrolledCourses()->pluck('courses.id');

        $sessions = LiveClass::whereIn('course_id', $courseIds)
            ->where('status', LiveClass::STATUS_SCHEDULED)
            ->where('starts_at', '>=', now())
            ->with('course')
            ->orderBy('starts_at')
            ->get()
            ->map(fn (LiveClass $liveClass) => [
                'type' => 'live_class',
                'when' => $liveClass->starts_at,
                'course' => $liveClass->course,
                'title' => $liveClass->title,
                'model' => $liveClass,
            ]);

        $examWindows = Quiz::whereIn('course_id', $courseIds)
            ->where('is_published', true)
            ->where(function ($query) {
                $query->whereNotNull('opens_at')->orWhereNotNull('closes_at');
            })
            ->with('course')
            ->get()
            ->filter(function (Quiz $quiz) use ($user) {
                // Window already closed: nothing left to schedule.
                if ($quiz->closes_at && $quiz->closes_at->isPast()) {
                    return false;
                }

                // Window not yet open: always worth showing, regardless of attempt history.
                if ($quiz->opens_at && $quiz->opens_at->isFuture()) {
                    return true;
                }

                // Window is open now: only show if the student still has an attempt left.
                return $quiz->max_attempts === null || $quiz->attemptsUsedBy($user) < $quiz->max_attempts;
            })
            ->map(function (Quiz $quiz) {
                $notYetOpen = $quiz->opens_at && $quiz->opens_at->isFuture();

                return [
                    'type' => $notYetOpen ? 'exam_opens' : 'exam_closes',
                    'when' => $notYetOpen ? $quiz->opens_at : $quiz->closes_at,
                    'course' => $quiz->course,
                    'title' => $quiz->title,
                    'model' => $quiz,
                ];
            })
            ->filter(fn (array $row) => $row['when'] !== null && $row['when']->isFuture());

        $events = $sessions->concat($examWindows)->sortBy('when')->values();

        return view('livewire.student.schedule', [
            'events' => $events,
        ]);
    }
}
