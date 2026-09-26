<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LessonResource extends Model
{
    /** Extensions that can be shown on screen, with the MIME type they are served as. */
    public const VIEWABLE = [
        'pdf' => ['kind' => 'pdf', 'mime' => 'application/pdf'],
        'jpg' => ['kind' => 'image', 'mime' => 'image/jpeg'],
        'jpeg' => ['kind' => 'image', 'mime' => 'image/jpeg'],
        'png' => ['kind' => 'image', 'mime' => 'image/png'],
        'webp' => ['kind' => 'image', 'mime' => 'image/webp'],
        'mp3' => ['kind' => 'audio', 'mime' => 'audio/mpeg'],
        'm4a' => ['kind' => 'audio', 'mime' => 'audio/mp4'],
        'wav' => ['kind' => 'audio', 'mime' => 'audio/wav'],
        'ogg' => ['kind' => 'audio', 'mime' => 'audio/ogg'],
        'mp4' => ['kind' => 'video', 'mime' => 'video/mp4'],
        'webm' => ['kind' => 'video', 'mime' => 'video/webm'],
    ];

    protected $fillable = ['lesson_id', 'original_name', 'file_path'];

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    private function extension(): string
    {
        return strtolower(pathinfo($this->original_name, PATHINFO_EXTENSION));
    }

    /** pdf | image | audio | video, or null when the file type cannot be shown on screen. */
    public function viewerKind(): ?string
    {
        return self::VIEWABLE[$this->extension()]['kind'] ?? null;
    }

    public function viewerMime(): ?string
    {
        return self::VIEWABLE[$this->extension()]['mime'] ?? null;
    }
}
