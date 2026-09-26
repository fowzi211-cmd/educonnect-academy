<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Course;
use App\Models\Enrolment;
use App\Models\LiveClass;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AssessmentPagesTest extends TestCase
{
    use RefreshDatabase;

    private function enrolledStudent(): array
    {
        $lecturer = User::factory()->create();
        $student = User::factory()->create();

        $course = Course::create([
            'title' => 'Statistics 101',
            'slug' => 'statistics-101',
            'created_by' => $lecturer->id,
            'status' => Course::STATUS_PUBLISHED,
        ]);

        Enrolment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => Enrolment::STATUS_ACTIVE,
            'source' => 'manual',
            'enrolled_at' => now(),
        ]);

        return [$student, $course, $lecturer];
    }

    private function quiz(Course $course, User $creator, array $attrs = []): Quiz
    {
        return Quiz::create(array_merge([
            'course_id' => $course->id,
            'title' => 'Week 1 Quiz',
            'is_published' => true,
            'created_by' => $creator->id,
        ], $attrs));
    }

    public function test_public_assessments_page_renders_for_guests(): void
    {
        $this->get(route('assessments'))
            ->assertOk()
            ->assertSee('Assessments that actually measure learning')
            ->assertSee('File Upload');
    }

    public function test_public_assessments_page_is_translated_in_arabic(): void
    {
        $this->withSession(['locale' => 'ar'])
            ->get(route('assessments'))
            ->assertOk();

        app()->setLocale('ar');
        $this->assertSame('تقييماتي', __('My Assessments'));
        $this->assertSame('جدولي', __('My Schedule'));
    }

    public function test_login_page_links_to_registration(): void
    {
        $this->get('/login')->assertOk()->assertSee(route('register'), false);
    }

    public function test_student_pages_require_authentication(): void
    {
        $this->get(route('my-assessments.index'))->assertRedirect(route('login'));
        $this->get(route('my-schedule.index'))->assertRedirect(route('login'));
    }

    public function test_my_assessments_lists_quizzes_and_assignments_from_enrolled_courses_only(): void
    {
        [$student, $course, $lecturer] = $this->enrolledStudent();

        $otherCourse = Course::create(['title' => 'Other', 'slug' => 'other', 'created_by' => $lecturer->id, 'status' => 'published']);

        $this->quiz($course, $lecturer, ['title' => 'Visible Quiz']);
        $this->quiz($course, $lecturer, ['title' => 'Draft Quiz', 'is_published' => false]);
        $this->quiz($otherCourse, $lecturer, ['title' => 'Foreign Quiz']);

        Assignment::create([
            'course_id' => $course->id, 'title' => 'Visible Assignment',
            'is_published' => true, 'created_by' => $lecturer->id,
        ]);

        Livewire::actingAs($student)
            ->test(\App\Livewire\Student\Assessments::class)
            ->assertSee('Visible Quiz')
            ->assertSee('Visible Assignment')
            ->assertDontSee('Draft Quiz')
            ->assertDontSee('Foreign Quiz');
    }

    public function test_my_assessments_shows_attempt_and_submission_status(): void
    {
        [$student, $course, $lecturer] = $this->enrolledStudent();

        $quiz = $this->quiz($course, $lecturer, ['result_visibility' => Quiz::RESULT_IMMEDIATE]);
        QuizAttempt::create([
            'quiz_id' => $quiz->id, 'user_id' => $student->id, 'attempt_number' => 1,
            'status' => QuizAttempt::STATUS_GRADED, 'started_at' => now(),
            'score_percent' => 80, 'passed' => true,
        ]);

        $assignment = Assignment::create([
            'course_id' => $course->id, 'title' => 'Late Work', 'is_published' => true,
            'created_by' => $lecturer->id, 'due_at' => now()->subDay(),
        ]);

        Livewire::actingAs($student)
            ->test(\App\Livewire\Student\Assessments::class)
            ->assertSee('Passed')
            ->assertSee('Missed');

        AssignmentSubmission::create([
            'assignment_id' => $assignment->id, 'user_id' => $student->id,
            'file_path' => 'x.pdf', 'file_name' => 'x.pdf', 'submitted_at' => now(), 'is_late' => true,
            'status' => AssignmentSubmission::STATUS_SUBMITTED,
        ]);

        Livewire::actingAs($student)
            ->test(\App\Livewire\Student\Assessments::class)
            ->assertSee('Submitted late');
    }

    public function test_schedule_merges_upcoming_live_classes_and_exam_windows(): void
    {
        [$student, $course, $lecturer] = $this->enrolledStudent();

        LiveClass::create([
            'course_id' => $course->id, 'host_id' => $lecturer->id, 'title' => 'Upcoming Session',
            'starts_at' => now()->addDay(), 'ends_at' => now()->addDay()->addHour(),
            'meeting_link' => 'https://example.com/m', 'status' => LiveClass::STATUS_SCHEDULED,
        ]);
        LiveClass::create([
            'course_id' => $course->id, 'host_id' => $lecturer->id, 'title' => 'Past Session',
            'starts_at' => now()->subDay(), 'ends_at' => now()->subDay()->addHour(),
            'meeting_link' => 'https://example.com/m', 'status' => LiveClass::STATUS_SCHEDULED,
        ]);
        LiveClass::create([
            'course_id' => $course->id, 'host_id' => $lecturer->id, 'title' => 'Cancelled Session',
            'starts_at' => now()->addDays(2), 'ends_at' => now()->addDays(2)->addHour(),
            'meeting_link' => 'https://example.com/m', 'status' => LiveClass::STATUS_CANCELLED,
        ]);

        // Regression: an exam that has not opened yet must still be listed.
        $this->quiz($course, $lecturer, [
            'title' => 'Future Exam', 'opens_at' => now()->addDays(3), 'closes_at' => now()->addDays(4),
        ]);
        $this->quiz($course, $lecturer, [
            'title' => 'Open Now Quiz', 'opens_at' => now()->subHour(), 'closes_at' => now()->addHours(5),
        ]);
        $this->quiz($course, $lecturer, [
            'title' => 'Closed Quiz', 'opens_at' => now()->subDays(3), 'closes_at' => now()->subDay(),
        ]);
        $this->quiz($course, $lecturer, ['title' => 'Undated Quiz']);

        Livewire::actingAs($student)
            ->test(\App\Livewire\Student\Schedule::class)
            ->assertSee('Upcoming Session')
            ->assertSee('Future Exam')
            ->assertSee('Open Now Quiz')
            ->assertDontSee('Past Session')
            ->assertDontSee('Cancelled Session')
            ->assertDontSee('Closed Quiz')
            ->assertDontSee('Undated Quiz');
    }

    public function test_schedule_hides_open_quiz_when_attempts_are_exhausted(): void
    {
        [$student, $course, $lecturer] = $this->enrolledStudent();

        $quiz = $this->quiz($course, $lecturer, [
            'title' => 'One Shot', 'max_attempts' => 1, 'closes_at' => now()->addDay(),
        ]);
        QuizAttempt::create([
            'quiz_id' => $quiz->id, 'user_id' => $student->id, 'attempt_number' => 1,
            'status' => QuizAttempt::STATUS_SUBMITTED, 'started_at' => now(),
        ]);

        Livewire::actingAs($student)
            ->test(\App\Livewire\Student\Schedule::class)
            ->assertDontSee('One Shot');
    }

    public function test_schedule_shows_empty_state_when_not_enrolled(): void
    {
        $student = User::factory()->create();

        Livewire::actingAs($student)
            ->test(\App\Livewire\Student\Schedule::class)
            ->assertSee('You have no upcoming live classes or exam windows');
    }
}
