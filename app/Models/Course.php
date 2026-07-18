<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Course extends Model
{
    use SoftDeletes;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_UNDER_REVIEW = 'under_review';
    public const STATUS_REVISION_REQUESTED = 'revision_requested';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_UNPUBLISHED = 'unpublished';
    public const STATUS_SUSPENDED = 'suspended';
    public const STATUS_ARCHIVED = 'archived';

    public const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_UNDER_REVIEW,
        self::STATUS_REVISION_REQUESTED,
        self::STATUS_APPROVED,
        self::STATUS_PUBLISHED,
        self::STATUS_UNPUBLISHED,
        self::STATUS_SUSPENDED,
        self::STATUS_ARCHIVED,
    ];

    public const LEVELS = ['beginner', 'intermediate', 'advanced'];
    public const DELIVERY_FORMATS = ['live', 'recorded', 'blended'];

    protected $fillable = [
        'title', 'slug', 'short_description', 'full_description', 'image_path',
        'promotional_video_url', 'category_id', 'level', 'teaching_language',
        'delivery_format', 'monthly_price', 'one_time_price', 'currency',
        'trial_period_days', 'max_students', 'enrolment_opens_at', 'enrolment_closes_at',
        'starts_at', 'ends_at', 'certificate_available', 'status', 'revision_notes',
        'created_by', 'submitted_at', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'monthly_price' => 'decimal:2',
            'one_time_price' => 'decimal:2',
            'certificate_available' => 'boolean',
            'enrolment_opens_at' => 'datetime',
            'enrolment_closes_at' => 'datetime',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'submitted_at' => 'datetime',
            'published_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Course $course) {
            if (empty($course->slug)) {
                $course->slug = static::uniqueSlug($course->title);
            }
        });
    }

    public static function uniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'course';
        $slug = $base;
        $i = 1;

        while (static::withTrashed()->where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function lecturers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'course_lecturers')
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    public function reviewers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'course_reviewers')
            ->withPivot(['decision', 'comments', 'reviewed_at'])
            ->withTimestamps();
    }

    public function reviewAssignments(): HasMany
    {
        return $this->hasMany(CourseReviewer::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(CourseSection::class)->orderBy('position');
    }

    public function liveClasses(): HasMany
    {
        return $this->hasMany(LiveClass::class);
    }

    public function enrolments(): HasMany
    {
        return $this->hasMany(Enrolment::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    public function isEditableBy(User $user): bool
    {
        if ($user->can('review courses') || $user->can('publish courses')) {
            return true;
        }

        return $this->created_by === $user->id
            && in_array($this->status, [self::STATUS_DRAFT, self::STATUS_REVISION_REQUESTED], true);
    }

    /**
     * Whether the user teaches this course (creator or co-lecturer) — used to
     * gate curriculum management, distinct from isEditableBy's status-aware check.
     */
    public function isTaughtBy(User $user): bool
    {
        return $this->created_by === $user->id || $this->lecturers()->where('users.id', $user->id)->exists();
    }

    /**
     * Whether the user currently has classroom access to this course:
     * an active enrolment, or being one of its lecturers/admins.
     */
    public function isAccessibleBy(User $user): bool
    {
        if ($this->isTaughtBy($user) || $user->can('publish courses')) {
            return true;
        }

        return $this->enrolments()
            ->where('user_id', $user->id)
            ->where('status', Enrolment::STATUS_ACTIVE)
            ->exists();
    }

    /**
     * Percentage (0-100) of this course's video lessons the given user has
     * completed. Reading lessons aren't counted — there's no completion
     * signal for them yet. Courses with no video lessons return null.
     */
    public function videoProgressPercentFor(User $user): ?int
    {
        $videoIds = $this->sections()
            ->with('lessons.video')
            ->get()
            ->flatMap->lessons
            ->pluck('video')
            ->filter()
            ->pluck('id');

        if ($videoIds->isEmpty()) {
            return null;
        }

        $completed = VideoProgress::where('user_id', $user->id)
            ->whereIn('recorded_video_id', $videoIds)
            ->whereNotNull('completed_at')
            ->count();

        return (int) round(($completed / $videoIds->count()) * 100);
    }
}
