<?php

namespace App\Notifications;

use App\Models\Certificate;
use Illuminate\Notifications\Notification;

class CertificateIssued extends Notification
{
    public function __construct(protected Certificate $certificate) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'certificate.issued',
            'message' => __('Your certificate for ":course" is ready.', [
                'course' => $this->certificate->course_title_snapshot,
            ]),
            'url' => route('certificates.show', $this->certificate),
        ];
    }
}
