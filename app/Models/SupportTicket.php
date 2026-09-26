<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupportTicket extends Model
{
    public const CATEGORY_TECHNICAL = 'technical_issue';

    public const CATEGORY_PAYMENT = 'payment_issue';

    public const CATEGORY_COURSE_CONTENT = 'course_content_issue';

    public const CATEGORY_LECTURER = 'lecturer_issue';

    public const CATEGORY_ACCOUNT = 'account_issue';

    public const CATEGORY_CERTIFICATE = 'certificate_issue';

    public const CATEGORY_REFUND = 'refund_request';

    public const CATEGORY_OTHER = 'other';

    public const CATEGORIES = [
        self::CATEGORY_TECHNICAL, self::CATEGORY_PAYMENT, self::CATEGORY_COURSE_CONTENT,
        self::CATEGORY_LECTURER, self::CATEGORY_ACCOUNT, self::CATEGORY_CERTIFICATE,
        self::CATEGORY_REFUND, self::CATEGORY_OTHER,
    ];

    public const PRIORITIES = ['low', 'medium', 'high', 'urgent'];

    public const STATUS_OPEN = 'open';

    public const STATUS_ASSIGNED = 'assigned';

    public const STATUS_WAITING_FOR_USER = 'waiting_for_user';

    public const STATUS_WAITING_FOR_INTERNAL_ACTION = 'waiting_for_internal_action';

    public const STATUS_RESOLVED = 'resolved';

    public const STATUS_CLOSED = 'closed';

    public const STATUS_REOPENED = 'reopened';

    public const STATUSES = [
        self::STATUS_OPEN, self::STATUS_ASSIGNED, self::STATUS_WAITING_FOR_USER,
        self::STATUS_WAITING_FOR_INTERNAL_ACTION, self::STATUS_RESOLVED,
        self::STATUS_CLOSED, self::STATUS_REOPENED,
    ];

    /** Statuses that still need staff attention (not yet resolved or closed). */
    public const OPEN_STATUSES = [
        self::STATUS_OPEN, self::STATUS_ASSIGNED, self::STATUS_WAITING_FOR_USER,
        self::STATUS_WAITING_FOR_INTERNAL_ACTION, self::STATUS_REOPENED,
    ];

    protected $fillable = [
        'ticket_number', 'user_id', 'category', 'priority', 'subject', 'description',
        'attachment_path', 'attachment_name', 'assigned_to', 'status', 'last_response_at', 'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'last_response_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(SupportTicketReply::class)->orderBy('created_at');
    }

    public static function nextTicketNumber(): string
    {
        $year = now()->format('Y');
        $count = static::whereYear('created_at', $year)->count() + 1;

        return sprintf('TCK-%s-%06d', $year, $count);
    }

    public static function categoryLabel(string $category): string
    {
        return match ($category) {
            self::CATEGORY_TECHNICAL => __('Technical Issue'),
            self::CATEGORY_PAYMENT => __('Payment Issue'),
            self::CATEGORY_COURSE_CONTENT => __('Course Content Issue'),
            self::CATEGORY_LECTURER => __('Lecturer Issue'),
            self::CATEGORY_ACCOUNT => __('Account Issue'),
            self::CATEGORY_CERTIFICATE => __('Certificate Issue'),
            self::CATEGORY_REFUND => __('Refund Request'),
            default => __('Other'),
        };
    }

    public static function priorityLabel(string $priority): string
    {
        return match ($priority) {
            'low' => __('Low'),
            'high' => __('High'),
            'urgent' => __('Urgent'),
            default => __('Medium'),
        };
    }

    public static function statusLabel(string $status): string
    {
        return match ($status) {
            self::STATUS_OPEN => __('Open'),
            self::STATUS_ASSIGNED => __('Assigned'),
            self::STATUS_WAITING_FOR_USER => __('Waiting for User'),
            self::STATUS_WAITING_FOR_INTERNAL_ACTION => __('Waiting for Internal Action'),
            self::STATUS_RESOLVED => __('Resolved'),
            self::STATUS_CLOSED => __('Closed'),
            self::STATUS_REOPENED => __('Reopened'),
            default => ucfirst($status),
        };
    }
}
