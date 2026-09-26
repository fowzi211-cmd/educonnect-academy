<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseCompletion extends Model
{
    protected $fillable = ['user_id', 'course_id', 'completed_at', 'criteria_snapshot'];

    protected function casts(): array
    {
        return [
            'completed_at' => 'datetime',
            'criteria_snapshot' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}
