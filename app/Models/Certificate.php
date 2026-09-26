<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Certificate extends Model
{
    public const STATUS_ACTIVE = 'active';

    public const STATUS_REVOKED = 'revoked';

    protected $fillable = [
        'certificate_number', 'user_id', 'course_id', 'student_name_snapshot',
        'course_title_snapshot', 'lecturer_name_snapshot', 'course_duration_snapshot',
        'template_text', 'completion_date', 'issued_at', 'status', 'revoked_at',
        'revoked_reason', 'revoked_by', 'reissue_of_certificate_id',
    ];

    protected function casts(): array
    {
        return [
            'completion_date' => 'datetime',
            'issued_at' => 'datetime',
            'revoked_at' => 'datetime',
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

    public function revokedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revoked_by');
    }

    public function reissueOf(): BelongsTo
    {
        return $this->belongsTo(Certificate::class, 'reissue_of_certificate_id');
    }

    public static function nextCertificateNumber(): string
    {
        $year = now()->format('Y');
        $count = static::whereYear('created_at', $year)->count() + 1;

        return sprintf('CERT-%s-%06d', $year, $count);
    }
}
