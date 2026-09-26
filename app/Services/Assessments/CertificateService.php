<?php

namespace App\Services\Assessments;

use App\Models\AuditLog;
use App\Models\Certificate;
use App\Models\CourseCompletion;
use App\Models\User;
use App\Notifications\CertificateIssued;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Facades\DB;

class CertificateService
{
    /**
     * Issue a certificate for a course completion, unless the course has
     * certificates disabled or one is already active for this completion.
     * Safe to call more than once (idempotent).
     */
    public function issueFor(CourseCompletion $completion): ?Certificate
    {
        $completion->loadMissing('user', 'course.lecturers', 'course.creator');
        $course = $completion->course;

        if (! $course->certificate_available) {
            return null;
        }

        $existing = Certificate::where('user_id', $completion->user_id)
            ->where('course_id', $completion->course_id)
            ->where('status', Certificate::STATUS_ACTIVE)
            ->first();

        if ($existing) {
            return $existing;
        }

        return DB::transaction(function () use ($completion, $course) {
            $lecturer = $course->lecturers->first() ?? $course->creator;

            $certificate = Certificate::create([
                'certificate_number' => Certificate::nextCertificateNumber(),
                'user_id' => $completion->user_id,
                'course_id' => $completion->course_id,
                'student_name_snapshot' => $completion->user->name,
                'course_title_snapshot' => $course->title,
                'lecturer_name_snapshot' => $lecturer?->name,
                'course_duration_snapshot' => $this->durationLabel($course),
                'completion_date' => $completion->completed_at,
                'issued_at' => now(),
                'status' => Certificate::STATUS_ACTIVE,
            ]);

            AuditLog::record('certificate.issued', subject: $certificate, new: [
                'certificate_number' => $certificate->certificate_number,
            ]);

            $completion->user->notify(new CertificateIssued($certificate));

            return $certificate;
        });
    }

    public function revoke(Certificate $certificate, string $reason, User $revokedBy): void
    {
        $certificate->update([
            'status' => Certificate::STATUS_REVOKED,
            'revoked_at' => now(),
            'revoked_reason' => $reason,
            'revoked_by' => $revokedBy->id,
        ]);

        AuditLog::record('certificate.revoked', subject: $certificate, reason: $reason);
    }

    public function reissue(Certificate $certificate): Certificate
    {
        $reissued = Certificate::create([
            'certificate_number' => Certificate::nextCertificateNumber(),
            'user_id' => $certificate->user_id,
            'course_id' => $certificate->course_id,
            'student_name_snapshot' => $certificate->student_name_snapshot,
            'course_title_snapshot' => $certificate->course_title_snapshot,
            'lecturer_name_snapshot' => $certificate->lecturer_name_snapshot,
            'course_duration_snapshot' => $certificate->course_duration_snapshot,
            'template_text' => $certificate->template_text,
            'completion_date' => $certificate->completion_date,
            'issued_at' => now(),
            'status' => Certificate::STATUS_ACTIVE,
            'reissue_of_certificate_id' => $certificate->id,
        ]);

        AuditLog::record('certificate.reissued', subject: $reissued, old: ['previous_certificate_number' => $certificate->certificate_number]);

        return $reissued;
    }

    protected function durationLabel($course): ?string
    {
        if (! $course->starts_at || ! $course->ends_at) {
            return null;
        }

        $weeks = max(1, (int) round($course->starts_at->diffInWeeks($course->ends_at)));

        return __(':n weeks', ['n' => $weeks]);
    }

    public function verificationUrl(Certificate $certificate): string
    {
        return route('certificates.verify', $certificate->certificate_number);
    }

    public function qrCodeSvg(Certificate $certificate): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle(180),
            new SvgImageBackEnd
        );

        return (new Writer($renderer))->writeString($this->verificationUrl($certificate));
    }
}
