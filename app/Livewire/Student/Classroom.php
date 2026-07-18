<?php

namespace App\Livewire\Student;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\LiveClassAttendance;
use App\Models\VideoProgress;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Classroom extends Component
{
    public Course $course;

    public string $activeTab = 'content';
    public ?int $selectedLessonId = null;

    public function mount(Course $course): void
    {
        abort_unless($course->isAccessibleBy(Auth::user()), 403);

        $this->course = $course->load('sections.lessons.video', 'sections.lessons.resources', 'lecturers');

        $firstLesson = $this->course->sections->flatMap->lessons->first();
        $this->selectedLessonId = $firstLesson?->id;
    }

    public function selectLesson(int $lessonId): void
    {
        $this->selectedLessonId = $lessonId;
    }

    public function updateProgress(int $videoId, int $watchedSeconds, int $duration): void
    {
        $video = $this->course->sections->flatMap->lessons->pluck('video')->filter()->firstWhere('id', $videoId);

        if (! $video) {
            return;
        }

        $progress = VideoProgress::firstOrNew([
            'user_id' => Auth::id(),
            'recorded_video_id' => $videoId,
        ]);

        $progress->watched_seconds = max($progress->watched_seconds ?? 0, $watchedSeconds);
        $percentage = $duration > 0 ? min(100, (int) round(($progress->watched_seconds / $duration) * 100)) : 0;
        $progress->percentage = max($progress->percentage ?? 0, $percentage);
        $progress->last_watched_at = now();

        if ($progress->percentage >= 90 && ! $progress->completed_at) {
            $progress->completed_at = now();
        }

        $progress->save();
    }

    protected function videoProgressFor(int $videoId): ?VideoProgress
    {
        return VideoProgress::where('user_id', Auth::id())->where('recorded_video_id', $videoId)->first();
    }

    public function render()
    {
        $selectedLesson = $this->selectedLessonId
            ? Lesson::with('video', 'resources')->find($this->selectedLessonId)
            : null;

        $attendance = LiveClassAttendance::where('user_id', Auth::id())
            ->whereIn('live_class_id', $this->course->liveClasses->pluck('id'))
            ->get()
            ->keyBy('live_class_id');

        return view('livewire.student.classroom', [
            'selectedLesson' => $selectedLesson,
            'selectedVideoProgress' => $selectedLesson?->video ? $this->videoProgressFor($selectedLesson->video->id) : null,
            'liveClasses' => $this->course->liveClasses()->orderBy('starts_at')->get(),
            'attendance' => $attendance,
        ]);
    }
}
