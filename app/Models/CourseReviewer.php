<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseReviewer extends Model
{
    protected $fillable = ['course_id', 'user_id', 'decision', 'comments', 'reviewed_at'];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
