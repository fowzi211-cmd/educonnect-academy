<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Lesson extends Model
{
    public const TYPE_READING = 'reading';

    public const TYPE_VIDEO = 'video';

    protected $fillable = ['course_section_id', 'title', 'description', 'content_type', 'position'];

    public function section(): BelongsTo
    {
        return $this->belongsTo(CourseSection::class, 'course_section_id');
    }

    public function video(): HasOne
    {
        return $this->hasOne(RecordedVideo::class);
    }

    public function resources(): HasMany
    {
        return $this->hasMany(LessonResource::class);
    }

    public function progress(): HasMany
    {
        return $this->hasMany(LessonProgress::class);
    }
}
