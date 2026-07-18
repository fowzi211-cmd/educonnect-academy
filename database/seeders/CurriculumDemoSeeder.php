<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Enrolment;
use App\Models\Lesson;
use App\Models\LessonResource;
use App\Models\LiveClass;
use App\Models\RecordedVideo;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class CurriculumDemoSeeder extends Seeder
{
    /**
     * Adds curriculum (sections/lessons/videos/resources), a live class, and a
     * couple of enrolments to the published demo courses, so the classroom,
     * progress tracking, and attendance features are demoable out of the box.
     * The "video" files are placeholders — there's no real footage to seed —
     * but they exercise the same storage/streaming path as a real upload.
     */
    public function run(): void
    {
        $this->seedCourse(
            title: 'Introduction to Statistics',
            lecturerEmail: 'lecturer1@educonnect.test',
            sections: [
                'Week 1: Getting Started' => [
                    ['title' => 'Welcome and Course Overview', 'type' => 'video', 'resource' => 'syllabus.pdf'],
                    ['title' => 'What is Statistics?', 'type' => 'reading'],
                ],
                'Week 2: Probability' => [
                    ['title' => 'Introduction to Probability', 'type' => 'video'],
                ],
            ],
            liveClassTitle: 'Live Q&A: Statistics Basics',
            students: ['student3@educonnect.test', 'student4@educonnect.test'],
        );

        $this->seedCourse(
            title: 'Python for Beginners',
            lecturerEmail: 'lecturer2@educonnect.test',
            sections: [
                'Getting Set Up' => [
                    ['title' => 'Installing Python', 'type' => 'video'],
                    ['title' => 'Your First Program', 'type' => 'reading'],
                ],
                'Core Concepts' => [
                    ['title' => 'Variables and Data Types', 'type' => 'video', 'resource' => 'cheat-sheet.pdf'],
                ],
            ],
            liveClassTitle: 'Office Hours: Ask Me Anything',
            students: ['student5@educonnect.test', 'student6@educonnect.test'],
        );
    }

    protected function seedCourse(string $title, string $lecturerEmail, array $sections, string $liveClassTitle, array $students): void
    {
        $course = Course::where('title', $title)->first();
        $lecturer = User::where('email', $lecturerEmail)->first();

        if (! $course || ! $lecturer || $course->sections()->exists()) {
            return;
        }

        foreach ($sections as $sectionTitle => $lessons) {
            $section = CourseSection::create([
                'course_id' => $course->id,
                'title' => $sectionTitle,
                'position' => $course->sections()->count() + 1,
            ]);

            foreach ($lessons as $i => $lessonData) {
                $lesson = Lesson::create([
                    'course_section_id' => $section->id,
                    'title' => $lessonData['title'],
                    'content_type' => $lessonData['type'],
                    'position' => $i + 1,
                ]);

                if ($lessonData['type'] === 'video') {
                    $path = "lesson-videos/demo-{$lesson->id}.mp4";
                    Storage::disk('local')->put($path, 'DEMO PLACEHOLDER VIDEO CONTENT');

                    RecordedVideo::create([
                        'lesson_id' => $lesson->id,
                        'video_path' => $path,
                        'duration_seconds' => 300,
                    ]);
                }

                if (isset($lessonData['resource'])) {
                    $path = "lesson-resources/demo-{$lesson->id}-{$lessonData['resource']}";
                    Storage::disk('local')->put($path, 'Demo resource content.');

                    LessonResource::create([
                        'lesson_id' => $lesson->id,
                        'original_name' => $lessonData['resource'],
                        'file_path' => $path,
                    ]);
                }
            }
        }

        LiveClass::create([
            'course_id' => $course->id,
            'host_id' => $lecturer->id,
            'title' => $liveClassTitle,
            'description' => 'Bring your questions from this week\'s material.',
            'starts_at' => now()->addDays(3)->setTime(17, 0),
            'ends_at' => now()->addDays(3)->setTime(18, 0),
            'provider' => 'zoom',
            'meeting_link' => 'https://zoom.example.com/j/demo-'.$course->id,
            'host_link' => 'https://zoom.example.com/j/demo-'.$course->id.'?host_key=demo',
            'status' => LiveClass::STATUS_SCHEDULED,
        ]);

        foreach ($students as $email) {
            $student = User::where('email', $email)->first();

            if (! $student) {
                continue;
            }

            Enrolment::firstOrCreate(
                ['user_id' => $student->id, 'course_id' => $course->id],
                ['status' => Enrolment::STATUS_ACTIVE, 'source' => 'complimentary', 'enrolled_at' => now()]
            );
        }
    }
}
