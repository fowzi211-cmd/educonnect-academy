<?php

namespace Tests\Feature;

use App\Livewire\Student\Classroom;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Enrolment;
use App\Models\Lesson;
use App\Models\LessonResource;
use App\Models\RecordedVideo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ProtectedContentTest extends TestCase
{
    use RefreshDatabase;

    private function setUpCourse(): array
    {
        Storage::fake('local');

        $lecturer = User::factory()->create();
        $student = User::factory()->create(['name' => 'Sara Student', 'email' => 'sara@example.com']);

        $course = Course::create([
            'title' => 'Anatomy', 'slug' => 'anatomy', 'created_by' => $lecturer->id,
            'status' => Course::STATUS_PUBLISHED,
        ]);
        $section = CourseSection::create(['course_id' => $course->id, 'title' => 'Week 1']);
        $lesson = Lesson::create([
            'course_section_id' => $section->id, 'title' => 'Bones', 'content_type' => 'video',
        ]);

        Enrolment::create([
            'user_id' => $student->id, 'course_id' => $course->id,
            'status' => Enrolment::STATUS_ACTIVE, 'source' => 'manual', 'enrolled_at' => now(),
        ]);

        return [$student, $course, $lesson];
    }

    private function resource(Lesson $lesson, string $name): LessonResource
    {
        Storage::disk('local')->put('lesson-resources/'.$name, 'file-bytes');

        return LessonResource::create([
            'lesson_id' => $lesson->id, 'original_name' => $name, 'file_path' => 'lesson-resources/'.$name,
        ]);
    }

    public function test_enrolled_student_can_view_a_pdf_inline_without_caching(): void
    {
        [$student, , $lesson] = $this->setUpCourse();
        $resource = $this->resource($lesson, 'notes.pdf');

        $response = $this->actingAs($student)
            ->withHeaders(['Sec-Fetch-Dest' => 'empty'])
            ->get(route('lesson-resources.view', $resource));

        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('inline', $response->headers->get('Content-Disposition'));
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $this->assertSame('nosniff', $response->headers->get('X-Content-Type-Options'));
    }

    public function test_opening_the_media_address_directly_is_refused(): void
    {
        [$student, , $lesson] = $this->setUpCourse();
        $resource = $this->resource($lesson, 'notes.pdf');

        foreach (['document', 'iframe', 'embed', 'object'] as $destination) {
            $this->actingAs($student)
                ->withHeaders(['Sec-Fetch-Dest' => $destination])
                ->get(route('lesson-resources.view', $resource))
                ->assertForbidden();
        }
    }

    public function test_video_stream_cannot_be_opened_directly_either(): void
    {
        [$student, , $lesson] = $this->setUpCourse();
        Storage::disk('local')->put('videos/a.mp4', 'video-bytes');
        $video = RecordedVideo::create(['lesson_id' => $lesson->id, 'video_path' => 'videos/a.mp4']);

        $this->actingAs($student)
            ->withHeaders(['Sec-Fetch-Dest' => 'document'])
            ->get(route('video.stream', $video))
            ->assertForbidden();

        $this->actingAs($student)
            ->withHeaders(['Sec-Fetch-Dest' => 'video'])
            ->get(route('video.stream', $video))
            ->assertOk();
    }

    public function test_students_outside_the_course_cannot_view_its_resources(): void
    {
        [, , $lesson] = $this->setUpCourse();
        $resource = $this->resource($lesson, 'notes.pdf');

        $this->actingAs(User::factory()->create())
            ->withHeaders(['Sec-Fetch-Dest' => 'empty'])
            ->get(route('lesson-resources.view', $resource))
            ->assertForbidden();
    }

    public function test_file_types_that_cannot_be_shown_on_screen_are_not_served(): void
    {
        [$student, , $lesson] = $this->setUpCourse();
        $resource = $this->resource($lesson, 'slides.docx');

        $this->actingAs($student)
            ->withHeaders(['Sec-Fetch-Dest' => 'empty'])
            ->get(route('lesson-resources.view', $resource))
            ->assertStatus(415);
    }

    public function test_the_old_download_address_no_longer_exists(): void
    {
        [$student, , $lesson] = $this->setUpCourse();
        $resource = $this->resource($lesson, 'notes.pdf');

        $this->actingAs($student)
            ->get('/lesson-resources/'.$resource->id.'/download')
            ->assertNotFound();
    }

    public function test_classroom_shows_materials_view_only_with_a_watermark_and_no_download_link(): void
    {
        [$student, $course, $lesson] = $this->setUpCourse();
        $this->resource($lesson, 'notes.pdf');
        $this->resource($lesson, 'lecture.mp3');
        $this->resource($lesson, 'slides.docx');

        Storage::disk('local')->put('videos/a.mp4', 'video-bytes');
        RecordedVideo::create(['lesson_id' => $lesson->id, 'video_path' => 'videos/a.mp4']);

        $html = Livewire::actingAs($student)
            ->test(Classroom::class, ['course' => $course])
            ->call('selectLesson', $lesson->id)
            ->html();

        $this->assertStringNotContainsString('/download', $html);
        $this->assertStringContainsString('nodownload', $html);
        $this->assertStringContainsString('disablePictureInPicture', $html);
        $this->assertStringContainsString('protectedArea', $html);
        $this->assertStringContainsString('pdfViewer', $html);
        $this->assertStringContainsString('cannot be viewed on screen', $html);
        // The watermark carries the viewer's identity (URL-encoded inside the SVG data URI).
        $this->assertStringContainsString(rawurlencode('Sara Student'), $html);
        $this->assertStringContainsString(rawurlencode('sara@example.com'), $html);
    }
}
