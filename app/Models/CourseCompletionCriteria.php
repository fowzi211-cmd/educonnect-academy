<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseCompletionCriteria extends Model
{
    protected $table = 'course_completion_criteria';

    protected $fillable = [
        'course_id', 'min_lessons_percent', 'min_video_watch_percent', 'min_attendance_percent',
        'require_assignments', 'require_passing_assessments', 'require_payment_good_standing',
    ];

    protected function casts(): array
    {
        return [
            'require_assignments' => 'boolean',
            'require_passing_assessments' => 'boolean',
            'require_payment_good_standing' => 'boolean',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}
