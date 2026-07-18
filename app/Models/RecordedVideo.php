<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RecordedVideo extends Model
{
    protected $fillable = ['lesson_id', 'video_path', 'thumbnail_path', 'duration_seconds'];

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function progress(): HasMany
    {
        return $this->hasMany(VideoProgress::class);
    }
}
