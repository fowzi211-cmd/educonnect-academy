<?php

namespace App\Livewire\Admin;

use App\Models\Course;
use App\Models\CourseCompletion;
use App\Models\Enrolment;
use App\Models\LiveClass;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app')]
class Reports extends Component
{
    public const REPORTS = ['registrations', 'enrolments', 'completions', 'attendance', 'assessments', 'lecturer_activity'];

    #[Url]
    public string $report = 'registrations';

    #[Url]
    public string $dateFrom = '';

    #[Url]
    public string $dateTo = '';

    public function mount(): void
    {
        $this->dateFrom = $this->dateFrom ?: now()->subDays(30)->format('Y-m-d');
        $this->dateTo = $this->dateTo ?: now()->format('Y-m-d');
    }

    public function selectReport(string $report): void
    {
        if (in_array($report, self::REPORTS, true)) {
            $this->report = $report;
        }
    }

    protected function rangeStart(): Carbon
    {
        return Carbon::parse($this->dateFrom)->startOfDay();
    }

    protected function rangeEnd(): Carbon
    {
        return Carbon::parse($this->dateTo)->endOfDay();
    }

    protected function registrationsData()
    {
        return User::whereBetween('created_at', [$this->rangeStart(), $this->rangeEnd()])
            ->with('roles')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (User $user) => [
                'Name' => $user->name,
                'Email' => $user->email,
                'Role(s)' => $user->roles->pluck('name')->implode(', '),
                'Registered At' => $user->created_at->format('Y-m-d H:i'),
            ]);
    }

    protected function enrolmentsData()
    {
        return Enrolment::whereBetween('enrolled_at', [$this->rangeStart(), $this->rangeEnd()])
            ->with('user', 'course')
            ->orderByDesc('enrolled_at')
            ->get()
            ->map(fn (Enrolment $enrolment) => [
                'Student' => $enrolment->user->name,
                'Course' => $enrolment->course->title,
                'Source' => $enrolment->source,
                'Status' => $enrolment->status,
                'Enrolled At' => $enrolment->enrolled_at?->format('Y-m-d H:i'),
            ]);
    }

    protected function completionsData()
    {
        return Course::query()
            ->withCount(['enrolments as enrolled_count' => fn ($q) => $q->where('status', Enrolment::STATUS_ACTIVE)])
            ->get()
            ->map(function (Course $course) {
                $completed = CourseCompletion::where('course_id', $course->id)
                    ->whereBetween('completed_at', [$this->rangeStart(), $this->rangeEnd()])
                    ->count();

                $rate = $course->enrolled_count > 0 ? round(($completed / $course->enrolled_count) * 100, 1) : 0.0;

                return [
                    'Course' => $course->title,
                    'Currently Enrolled' => $course->enrolled_count,
                    'Completions in Range' => $completed,
                    'Completion Rate %' => $rate,
                ];
            })
            ->filter(fn ($row) => $row['Currently Enrolled'] > 0 || $row['Completions in Range'] > 0)
            ->values();
    }

    protected function attendanceData()
    {
        return LiveClass::whereBetween('starts_at', [$this->rangeStart(), $this->rangeEnd()])
            ->with('course', 'attendance')
            ->orderByDesc('starts_at')
            ->get()
            ->map(function (LiveClass $liveClass) {
                $counts = $liveClass->attendance->countBy('status');
                $total = $liveClass->attendance->count();
                $present = $counts->get('present', 0);

                return [
                    'Live Class' => $liveClass->title,
                    'Course' => $liveClass->course->title,
                    'Scheduled At' => $liveClass->starts_at->format('Y-m-d H:i'),
                    'Present' => $present,
                    'Absent' => $counts->get('absent', 0),
                    'Excused' => $counts->get('excused', 0),
                    'Unmarked' => $counts->get('unmarked', 0),
                    'Attendance Rate %' => $total > 0 ? round(($present / $total) * 100, 1) : 0.0,
                ];
            });
    }

    protected function assessmentsData()
    {
        return Quiz::with('course')
            ->get()
            ->map(function (Quiz $quiz) {
                $attempts = QuizAttempt::where('quiz_id', $quiz->id)
                    ->where('status', QuizAttempt::STATUS_GRADED)
                    ->whereBetween('graded_at', [$this->rangeStart(), $this->rangeEnd()])
                    ->get();

                $count = $attempts->count();

                return [
                    'Quiz' => $quiz->title,
                    'Course' => $quiz->course->title,
                    'Graded Attempts' => $count,
                    'Average Score %' => $count > 0 ? round($attempts->avg('score_percent'), 1) : 0.0,
                    'Pass Rate %' => $count > 0 ? round(($attempts->where('passed', true)->count() / $count) * 100, 1) : 0.0,
                ];
            })
            ->filter(fn ($row) => $row['Graded Attempts'] > 0)
            ->values();
    }

    protected function lecturerActivityData()
    {
        return User::role('lecturer')
            ->with('createdCourses', 'coursesTeaching')
            ->get()
            ->map(function (User $lecturer) {
                $courseIds = $lecturer->createdCourses->pluck('id')
                    ->merge($lecturer->coursesTeaching->pluck('id'))
                    ->unique();

                $enrolledStudents = Enrolment::whereIn('course_id', $courseIds)
                    ->where('status', Enrolment::STATUS_ACTIVE)
                    ->distinct('user_id')
                    ->count('user_id');

                $liveClasses = LiveClass::where('host_id', $lecturer->id)
                    ->whereBetween('created_at', [$this->rangeStart(), $this->rangeEnd()])
                    ->count();

                $quizzesCreated = Quiz::whereIn('course_id', $courseIds)
                    ->whereBetween('created_at', [$this->rangeStart(), $this->rangeEnd()])
                    ->count();

                return [
                    'Lecturer' => $lecturer->name,
                    'Courses' => $courseIds->count(),
                    'Enrolled Students' => $enrolledStudents,
                    'Live Classes Scheduled in Range' => $liveClasses,
                    'Quizzes Created in Range' => $quizzesCreated,
                ];
            })
            ->filter(fn ($row) => $row['Courses'] > 0)
            ->values();
    }

    protected function currentReportData()
    {
        return match ($this->report) {
            'enrolments' => $this->enrolmentsData(),
            'completions' => $this->completionsData(),
            'attendance' => $this->attendanceData(),
            'assessments' => $this->assessmentsData(),
            'lecturer_activity' => $this->lecturerActivityData(),
            default => $this->registrationsData(),
        };
    }

    public function exportCsv()
    {
        $rows = $this->currentReportData();

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');

            if ($rows->isNotEmpty()) {
                fputcsv($handle, array_keys($rows->first()), escape: '\\');
            }

            foreach ($rows as $row) {
                fputcsv($handle, $row, escape: '\\');
            }

            fclose($handle);
        }, $this->report.'-'.$this->dateFrom.'-to-'.$this->dateTo.'.csv');
    }

    public function render()
    {
        return view('livewire.admin.reports', [
            'rows' => $this->currentReportData(),
        ]);
    }
}
