<?php

namespace App\Notifications;

use App\Models\LecturerProfile;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LecturerApplicationReviewed extends Notification
{
    public function __construct(protected LecturerProfile $lecturerProfile, protected bool $approved) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => $this->approved ? 'lecturer_application.approved' : 'lecturer_application.rejected',
            'message' => $this->message(),
            'url' => route('lecturer-application'),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject($this->approved ? __('Lecturer Application Approved') : __('Lecturer Application Update'))
            ->line($this->message());

        return $this->approved
            ? $mail->action(__('Start Creating Courses'), route('lecturer.courses.index'))
            : $mail->action(__('View Application'), route('lecturer-application'));
    }

    protected function message(): string
    {
        return $this->approved
            ? __('Your lecturer application was approved. You can now create courses.')
            : __('Your lecturer application was rejected: :reason', [
                'reason' => $this->lecturerProfile->rejection_reason,
            ]);
    }
}
