<?php

namespace App\Services\Assessments;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Course;
use App\Models\CourseCompletion;
use App\Models\LessonProgress;
use App\Models\LiveClassAttendance;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use App\Models\VideoProgress;

/**
 * Computes and records course completion against a course's configurable
 * criteria (spec section 17). A course with no CourseCompletionCriteria row
 * has no automatic completion tracking at all — lecturers opt in.
 */
class CourseCompletionService
{
    public function __construct(protected CertificateService $certificates) {}

    public function progressFor(User $user, Course $course): array
    {
        $criteria = $course->completionCriteria;

        $lessonsTotal = $course->sections->flatMap->lessons->count();
        $lessonsCompleted = $this->completedLessonsCount($user, $course);
        $lessonsPercent = $lessonsTotal > 0 ? (int) round(($lessonsCompleted / $lessonsTotal) * 100) : null;

        $videoPercent = $course->videoProgressPercentFor($user);

        $liveClassesTotal = $course->liveClasses()->count();
        $attended = LiveClassAttendance::where('user_id', $user->id)
            ->whereIn('live_class_id', $course->liveClasses()->pluck('id'))
            ->where('status', 'present')
            ->count();
        $attendancePercent = $liveClassesTotal > 0 ? (int) round(($attended / $liveClassesTotal) * 100) : null;

        $assignmentsMet = $this->requiredAssignmentsMet($user, $course);
        $assessmentsMet = $this->requiredAssessmentsPassed($user, $course);
        $paymentGoodStanding = $course->isAccessibleBy($user);

        return [
            'criteria' => $criteria,
            'lessons_percent' => $lessonsPercent,
            'video_watch_percent' => $videoPercent,
            'attendance_percent' => $attendancePercent,
            'assignments_met' => $assignmentsMet,
            'assessments_met' => $assessmentsMet,
            'payment_good_standing' => $paymentGoodStanding,
            'overall_met' => $criteria ? $this->meetsCriteria($criteria, $lessonsPercent, $videoPercent, $attendancePercent, $assignmentsMet, $assessmentsMet, $paymentGoodStanding) : false,
        ];
    }

    /**
     * Check the student against the course's criteria and record a
     * CourseCompletion the first time they meet it. Safe to call repeatedly
     * (idempotent) — does nothing once already recorded or if the course has
     * no configured criteria.
     */
    public function checkAndRecordCompletion(User $user, Course $course): ?CourseCompletion
    {
        if (! $course->completionCriteria) {
            return null;
        }

        $existing = CourseCompletion::where('user_id', $user->id)->where('course_id', $course->id)->first();

        if ($existing) {
            return $existing;
        }

        $progress = $this->progressFor($user, $course);

        if (! $progress['overall_met']) {
            return null;
        }

        $completion = CourseCompletion::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'completed_at' => now(),
            'criteria_snapshot' => [
                'lessons_percent' => $progress['lessons_percent'],
                'video_watch_percent' => $progress['video_watch_percent'],
                'attendance_percent' => $progress['attendance_percent'],
                'assignments_met' => $progress['assignments_met'],
                'assessments_met' => $progress['assessments_met'],
                'payment_good_standing' => $progress['payment_good_standing'],
            ],
        ]);

        $this->certificates->issueFor($completion);

        return $completion;
    }

    protected function meetsCriteria($criteria, ?int $lessonsPercent, ?int $videoPercent, ?int $attendancePercent, bool $assignmentsMet, bool $assessmentsMet, bool $paymentGoodStanding): bool
    {
        if ($criteria->min_lessons_percent !== null && ($lessonsPercent ?? 0) < $criteria->min_lessons_percent) {
            return false;
        }

        if ($criteria->min_video_watch_percent !== null && ($videoPercent ?? 0) < $criteria->min_video_watch_percent) {
            return false;
        }

        if ($criteria->min_attendance_percent !== null && ($attendancePercent ?? 0) < $criteria->min_attendance_percent) {
            return false;
        }

        if ($criteria->require_assignments && ! $assignmentsMet) {
            return false;
        }

        if ($criteria->require_passing_assessments && ! $assessmentsMet) {
            return false;
        }

        if ($criteria->require_payment_good_standing && ! $paymentGoodStanding) {
            return false;
        }

        return true;
    }

    protected function completedLessonsCount(User $user, Course $course): int
    {
        $lessons = $course->sections->flatMap->lessons;

        $videoIds = $lessons->where('content_type', 'video')->pluck('video.id')->filter();
        $readingLessonIds = $lessons->where('content_type', 'reading')->pluck('id');

        $completedVideos = VideoProgress::where('user_id', $user->id)
            ->whereIn('recorded_video_id', $videoIds)
            ->whereNotNull('completed_at')
            ->count();

        $completedReadings = LessonProgress::where('user_id', $user->id)
            ->whereIn('lesson_id', $readingLessonIds)
            ->whereNotNull('completed_at')
            ->count();

        return $completedVideos + $completedReadings;
    }

    protected function requiredAssignmentsMet(User $user, Course $course): bool
    {
        $assignmentIds = Assignment::where('course_id', $course->id)->where('is_published', true)->pluck('id');

        if ($assignmentIds->isEmpty()) {
            return true;
        }

        $submittedCount = AssignmentSubmission::where('user_id', $user->id)
            ->whereIn('assignment_id', $assignmentIds)
            ->count();

        return $submittedCount >= $assignmentIds->count();
    }

    protected function requiredAssessmentsPassed(User $user, Course $course): bool
    {
        $quizIds = Quiz::where('course_id', $course->id)
            ->where('is_published', true)
            ->where('type', '!=', Quiz::TYPE_SURVEY)
            ->pluck('id');

        if ($quizIds->isEmpty()) {
            return true;
        }

        $passedCount = QuizAttempt::whereIn('quiz_id', $quizIds)
            ->where('user_id', $user->id)
            ->where('passed', true)
            ->distinct('quiz_id')
            ->count('quiz_id');

        return $passedCount >= $quizIds->count();
    }
}
