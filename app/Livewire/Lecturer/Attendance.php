<?php

namespace App\Livewire\Lecturer;

use App\Models\AuditLog;
use App\Models\LiveClass;
use App\Models\LiveClassAttendance;
use App\Models\User;
use App\Services\Assessments\CourseCompletionService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Attendance extends Component
{
    public LiveClass $liveClass;

    public array $notes = [];

    public function mount(LiveClass $liveClass): void
    {
        abort_unless($liveClass->course->isTaughtBy(Auth::user()), 403);
        $this->liveClass = $liveClass;
    }

    public function mark(int $userId, string $status, CourseCompletionService $completion): void
    {
        abort_unless(in_array($status, [
            LiveClassAttendance::STATUS_PRESENT,
            LiveClassAttendance::STATUS_ABSENT,
            LiveClassAttendance::STATUS_EXCUSED,
        ], true), 422);

        $record = LiveClassAttendance::updateOrCreate(
            ['live_class_id' => $this->liveClass->id, 'user_id' => $userId],
            [
                'status' => $status,
                'marked_by' => Auth::id(),
                'note' => $this->notes[$userId] ?? null,
            ]
        );

        AuditLog::record('attendance.marked', subject: $record, new: ['status' => $status]);

        if ($status === LiveClassAttendance::STATUS_PRESENT && ($student = User::find($userId))) {
            $completion->checkAndRecordCompletion($student, $this->liveClass->course);
        }
    }

    public function render()
    {
        $students = $this->liveClass->course->enrolments()
            ->where('status', 'active')
            ->with('user')
            ->get()
            ->pluck('user');

        $attendance = LiveClassAttendance::where('live_class_id', $this->liveClass->id)
            ->get()
            ->keyBy('user_id');

        return view('livewire.lecturer.attendance', [
            'students' => $students,
            'attendance' => $attendance,
        ]);
    }
}
