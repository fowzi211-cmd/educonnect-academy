<?php

namespace App\Livewire\Lecturer;

use App\Models\AuditLog;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Lesson;
use App\Models\LessonResource;
use App\Models\RecordedVideo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class CurriculumBuilder extends Component
{
    use WithFileUploads;

    public Course $course;

    public string $newSectionTitle = '';

    public ?int $editingSectionId = null;
    public string $editingSectionTitle = '';

    public ?int $addingLessonToSection = null;
    public string $newLessonTitle = '';
    public string $newLessonDescription = '';
    public string $newLessonType = Lesson::TYPE_READING;

    public ?int $expandedLessonId = null;
    public $video;
    public $resourceFiles = [];

    public function mount(Course $course): void
    {
        abort_unless($course->isTaughtBy(Auth::user()), 403);
        $this->course = $course;
    }

    protected function sections()
    {
        return $this->course->sections()->with('lessons.video', 'lessons.resources')->get();
    }

    public function addSection(): void
    {
        $this->validate(['newSectionTitle' => ['required', 'string', 'max:255']]);

        $position = ($this->course->sections()->max('position') ?? 0) + 1;

        $this->course->sections()->create([
            'title' => $this->newSectionTitle,
            'position' => $position,
        ]);

        $this->newSectionTitle = '';
    }

    public function deleteSection(int $sectionId): void
    {
        $section = $this->course->sections()->findOrFail($sectionId);
        $section->delete();
    }

    public function startEditSection(int $sectionId): void
    {
        $section = $this->course->sections()->findOrFail($sectionId);
        $this->editingSectionId = $section->id;
        $this->editingSectionTitle = $section->title;
    }

    public function saveSection(): void
    {
        $this->validate(['editingSectionTitle' => ['required', 'string', 'max:255']]);

        $section = $this->course->sections()->findOrFail($this->editingSectionId);
        $section->update(['title' => $this->editingSectionTitle]);

        $this->reset(['editingSectionId', 'editingSectionTitle']);
    }

    public function cancelEditSection(): void
    {
        $this->reset(['editingSectionId', 'editingSectionTitle']);
    }

    public function moveSection(int $sectionId, string $direction): void
    {
        $sections = $this->course->sections()->orderBy('position')->get();
        $this->swapPosition($sections, $sectionId, $direction);
    }

    public function showAddLesson(int $sectionId): void
    {
        $this->addingLessonToSection = $this->addingLessonToSection === $sectionId ? null : $sectionId;
        $this->reset(['newLessonTitle', 'newLessonDescription', 'newLessonType']);
    }

    public function addLesson(): void
    {
        $this->validate([
            'newLessonTitle' => ['required', 'string', 'max:255'],
            'newLessonDescription' => ['nullable', 'string', 'max:2000'],
            'newLessonType' => ['required', 'in:'.Lesson::TYPE_READING.','.Lesson::TYPE_VIDEO],
        ]);

        $section = $this->course->sections()->findOrFail($this->addingLessonToSection);
        $position = ($section->lessons()->max('position') ?? 0) + 1;

        $section->lessons()->create([
            'title' => $this->newLessonTitle,
            'description' => $this->newLessonDescription ?: null,
            'content_type' => $this->newLessonType,
            'position' => $position,
        ]);

        $this->addingLessonToSection = null;
        $this->reset(['newLessonTitle', 'newLessonDescription', 'newLessonType']);
    }

    public function deleteLesson(int $lessonId): void
    {
        $lesson = $this->findOwnedLesson($lessonId);
        $lesson->delete();

        if ($this->expandedLessonId === $lessonId) {
            $this->expandedLessonId = null;
        }
    }

    public function moveLesson(int $lessonId, string $direction): void
    {
        $lesson = $this->findOwnedLesson($lessonId);
        $siblings = $lesson->section->lessons()->orderBy('position')->get();
        $this->swapPosition($siblings, $lessonId, $direction);
    }

    public function toggleExpand(int $lessonId): void
    {
        $this->expandedLessonId = $this->expandedLessonId === $lessonId ? null : $lessonId;
        $this->reset(['video', 'resourceFiles']);
    }

    public function uploadVideo(): void
    {
        $lesson = $this->findOwnedLesson($this->expandedLessonId);

        $this->validate(['video' => ['required', 'file', 'mimetypes:video/mp4,video/quicktime,video/webm', 'max:512000']]);

        if ($lesson->video) {
            Storage::disk('local')->delete($lesson->video->video_path);
            $lesson->video->delete();
        }

        $path = $this->video->store('lesson-videos', 'local');

        RecordedVideo::create([
            'lesson_id' => $lesson->id,
            'video_path' => $path,
        ]);

        AuditLog::record('lesson.video_uploaded', subject: $lesson, new: ['video_path' => $path]);

        $this->video = null;
    }

    public function uploadResources(): void
    {
        $lesson = $this->findOwnedLesson($this->expandedLessonId);

        $this->validate(['resourceFiles.*' => ['required', 'file', 'max:10240']]);

        foreach ($this->resourceFiles as $file) {
            LessonResource::create([
                'lesson_id' => $lesson->id,
                'original_name' => $file->getClientOriginalName(),
                'file_path' => $file->store('lesson-resources', 'local'),
            ]);
        }

        $this->resourceFiles = [];
    }

    public function deleteResource(int $resourceId): void
    {
        $resource = LessonResource::whereHas('lesson.section.course', fn ($q) => $q->where('id', $this->course->id))
            ->findOrFail($resourceId);

        Storage::disk('local')->delete($resource->file_path);
        $resource->delete();
    }

    protected function findOwnedLesson(int $lessonId): Lesson
    {
        return Lesson::whereHas('section', fn ($q) => $q->where('course_id', $this->course->id))
            ->with('section')
            ->findOrFail($lessonId);
    }

    protected function swapPosition($collection, int $id, string $direction): void
    {
        $index = $collection->search(fn ($item) => $item->id === $id);

        if ($index === false) {
            return;
        }

        $targetIndex = $direction === 'up' ? $index - 1 : $index + 1;

        if (! isset($collection[$targetIndex])) {
            return;
        }

        $current = $collection[$index];
        $target = $collection[$targetIndex];

        [$current->position, $target->position] = [$target->position, $current->position];
        $current->save();
        $target->save();
    }

    public function render()
    {
        return view('livewire.lecturer.curriculum-builder', [
            'sections' => $this->sections(),
        ]);
    }
}
