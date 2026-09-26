<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Course;
use App\Models\CourseCompletionCriteria;
use App\Models\DiscussionThread;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\User;
use App\Services\Assessments\CourseCompletionService;
use App\Services\Assessments\QuizGradingService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class Phase5DemoSeeder extends Seeder
{
    /**
     * Seeds assessments, assignments, completion tracking, announcements, and
     * discussions onto the demo courses from CurriculumDemoSeeder, so Phase 5
     * features are demoable out of the box rather than starting empty.
     */
    public function run(): void
    {
        $this->seedStatisticsCourse();
        $this->seedPythonCourse();
    }

    protected function seedStatisticsCourse(): void
    {
        $course = Course::where('title', 'Introduction to Statistics')->first();
        $lecturer = User::where('email', 'lecturer1@educonnect.test')->first();
        $student = User::where('email', 'student3@educonnect.test')->first();

        if (! $course || ! $lecturer || ! $student || $course->quizzes()->exists()) {
            return;
        }

        $course->update(['certificate_available' => true]);

        $quiz = Quiz::create([
            'course_id' => $course->id,
            'title' => 'Week 1 Quiz: Statistics Basics',
            'description' => 'A short check on this week\'s material.',
            'type' => Quiz::TYPE_QUIZ,
            'time_limit_minutes' => 20,
            'max_attempts' => 2,
            'pass_mark_percent' => 60,
            'result_visibility' => Quiz::RESULT_IMMEDIATE,
            'correct_answer_visibility' => Quiz::ANSWERS_AFTER_SUBMIT,
            'is_published' => true,
            'created_by' => $lecturer->id,
        ]);

        $mcq = $quiz->questions()->create(['type' => QuizQuestion::TYPE_MCQ_SINGLE, 'prompt' => 'Which measure of central tendency is most affected by outliers?', 'points' => 2, 'position' => 1]);
        $mcq->options()->createMany([
            ['text' => 'Mean', 'is_correct' => true, 'position' => 0],
            ['text' => 'Median', 'is_correct' => false, 'position' => 1],
            ['text' => 'Mode', 'is_correct' => false, 'position' => 2],
        ]);

        $trueFalse = $quiz->questions()->create(['type' => QuizQuestion::TYPE_TRUE_FALSE, 'prompt' => 'The standard deviation can be negative.', 'points' => 1, 'position' => 2]);
        $trueFalse->options()->createMany([
            ['text' => 'True', 'is_correct' => false, 'position' => 0],
            ['text' => 'False', 'is_correct' => true, 'position' => 1],
        ]);

        $numerical = $quiz->questions()->create([
            'type' => QuizQuestion::TYPE_NUMERICAL, 'prompt' => 'What is the median of 4, 8, 2?', 'points' => 2, 'position' => 3,
            'correct_numerical' => 4, 'numerical_tolerance' => 0,
        ]);

        $essay = $quiz->questions()->create(['type' => QuizQuestion::TYPE_ESSAY, 'prompt' => 'Briefly explain why the median is often preferred over the mean for skewed data.', 'points' => 5, 'position' => 4]);

        $grading = app(QuizGradingService::class);

        $attempt = $quiz->attempts()->create([
            'user_id' => $student->id,
            'attempt_number' => 1,
            'status' => 'in_progress',
            'started_at' => now()->subDay(),
            'max_points' => $quiz->totalPoints(),
        ]);

        $correctOptionId = $mcq->options()->where('is_correct', true)->value('id');
        $trueFalseCorrectId = $trueFalse->options()->where('is_correct', true)->value('id');

        $grading->gradeAndStoreAnswer($attempt, $mcq, [$correctOptionId]);
        $grading->gradeAndStoreAnswer($attempt, $trueFalse, [$trueFalseCorrectId]);
        $grading->gradeAndStoreAnswer($attempt, $numerical, 4);
        $grading->gradeAndStoreAnswer($attempt, $essay, 'The median ignores extreme values, so skewed distributions with outliers are better summarised by it than the mean, which extreme values pull away from the bulk of the data.');

        $attempt->update(['status' => 'submitted', 'submitted_at' => now()->subDay()]);

        // Lecturer grades the essay — the only manually-graded question.
        $essayAnswer = $attempt->answers()->where('quiz_question_id', $essay->id)->first();
        $essayAnswer->update([
            'points_awarded' => 4,
            'grader_feedback' => 'Good explanation — could mention robustness to outliers explicitly.',
            'graded_by' => $lecturer->id,
            'graded_at' => now()->subHours(20),
        ]);
        $grading->finalizeIfFullyGraded($attempt);

        // Completion criteria: passing the assessment is enough for this demo course.
        CourseCompletionCriteria::create([
            'course_id' => $course->id,
            'require_passing_assessments' => true,
            'require_payment_good_standing' => false,
        ]);

        app(CourseCompletionService::class)->checkAndRecordCompletion($student, $course);

        // Assignment: lecturer creates it, student4 submits, lecturer grades.
        $assignment = Assignment::create([
            'course_id' => $course->id,
            'title' => 'Problem Set 1: Descriptive Statistics',
            'description' => 'Solve the five problems in the handout and upload your work as a single PDF.',
            'max_points' => 20,
            'due_at' => now()->addWeek(),
            'late_policy' => Assignment::LATE_WITH_PENALTY,
            'late_penalty_percent_per_day' => 5,
            'allowed_file_types' => 'pdf',
            'is_published' => true,
            'created_by' => $lecturer->id,
        ]);

        $student4 = User::where('email', 'student4@educonnect.test')->first();

        if ($student4) {
            $path = "assignment-submissions/demo-{$assignment->id}-{$student4->id}.pdf";
            Storage::disk('local')->put($path, 'Demo submission content.');

            AssignmentSubmission::create([
                'assignment_id' => $assignment->id,
                'user_id' => $student4->id,
                'file_path' => $path,
                'file_name' => 'problem-set-1.pdf',
                'submitted_at' => now()->subDays(2),
                'is_late' => false,
                'status' => 'graded',
                'score' => 17,
                'feedback' => 'Well done — minor arithmetic slip on question 4.',
                'graded_by' => $lecturer->id,
                'graded_at' => now()->subDay(),
            ]);
        }

        // Announcement.
        Announcement::create([
            'course_id' => $course->id,
            'created_by' => $lecturer->id,
            'title' => 'Welcome to Introduction to Statistics!',
            'body' => 'Please complete the Week 1 quiz and Problem Set 1 by the end of the week. Looking forward to our live Q&A session!',
            'is_pinned' => true,
            'published_at' => now()->subDays(3),
        ]);

        // Discussion: a general thread with a lecturer reply, plus a lesson-specific question.
        $thread = DiscussionThread::create([
            'course_id' => $course->id,
            'user_id' => $student->id,
            'title' => 'Recommended reading for probability?',
            'body' => 'Does anyone have book recommendations to go deeper on probability theory?',
        ]);

        $thread->replies()->create([
            'user_id' => $lecturer->id,
            'body' => "I'd suggest \"Introduction to Probability\" by Blitzstein and Hwang — it's approachable and has great exercises.",
        ]);

        $firstLesson = $course->sections()->first()?->lessons()->first();

        if ($firstLesson && $student4) {
            DiscussionThread::create([
                'course_id' => $course->id,
                'lesson_id' => $firstLesson->id,
                'user_id' => $student4->id,
                'title' => 'Where can I find the slides?',
                'body' => 'Is the slide deck for this lesson available for download anywhere?',
            ]);
        }
    }

    protected function seedPythonCourse(): void
    {
        $course = Course::where('title', 'Python for Beginners')->first();
        $lecturer = User::where('email', 'lecturer2@educonnect.test')->first();
        $student = User::where('email', 'student5@educonnect.test')->first();

        if (! $course || ! $lecturer || ! $student || $course->quizzes()->exists()) {
            return;
        }

        $quiz = Quiz::create([
            'course_id' => $course->id,
            'title' => 'Python Basics Check',
            'type' => Quiz::TYPE_PRACTICE_TEST,
            'max_attempts' => null,
            'pass_mark_percent' => 50,
            'result_visibility' => Quiz::RESULT_IMMEDIATE,
            'correct_answer_visibility' => Quiz::ANSWERS_AFTER_SUBMIT,
            'is_published' => true,
            'created_by' => $lecturer->id,
        ]);

        $q1 = $quiz->questions()->create(['type' => QuizQuestion::TYPE_MCQ_SINGLE, 'prompt' => 'Which keyword defines a function in Python?', 'points' => 1, 'position' => 1]);
        $q1->options()->createMany([
            ['text' => 'func', 'is_correct' => false, 'position' => 0],
            ['text' => 'def', 'is_correct' => true, 'position' => 1],
            ['text' => 'function', 'is_correct' => false, 'position' => 2],
        ]);

        $grading = app(QuizGradingService::class);

        $attempt = $quiz->attempts()->create([
            'user_id' => $student->id,
            'attempt_number' => 1,
            'status' => 'in_progress',
            'started_at' => now()->subHours(5),
            'max_points' => $quiz->totalPoints(),
        ]);

        $correctId = $q1->options()->where('is_correct', true)->value('id');
        $grading->gradeAndStoreAnswer($attempt, $q1, [$correctId]);
        $attempt->update(['status' => 'submitted', 'submitted_at' => now()->subHours(5)]);
        $grading->finalizeIfFullyGraded($attempt);

        // Assignment left unsubmitted, to demo the "not started" state too.
        Assignment::create([
            'course_id' => $course->id,
            'title' => 'Mini Project: Temperature Converter',
            'description' => 'Write a small script that converts between Celsius and Fahrenheit.',
            'max_points' => 10,
            'due_at' => now()->addWeeks(2),
            'late_policy' => Assignment::LATE_NO_PENALTY,
            'allowed_file_types' => 'py,txt',
            'is_published' => true,
            'created_by' => $lecturer->id,
        ]);

        // Draft announcement, to demo the unpublished state in the lecturer view.
        Announcement::create([
            'course_id' => $course->id,
            'created_by' => $lecturer->id,
            'title' => 'Upcoming: guest speaker session',
            'body' => 'Draft — confirm the date with the guest speaker before publishing.',
            'published_at' => null,
        ]);
    }
}
