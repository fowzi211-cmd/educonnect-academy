<?php

namespace App\Notifications;

use App\Models\LecturerProfile;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LecturerApplicationSubmitted extends Notification
{
    public function __construct(protected LecturerProfile $lecturerProfile) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'lecturer_application.submitted',
            'message' => $this->message(),
            'url' => $this->url(),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('New Lecturer Application'))
            ->line($this->message())
            ->action(__('Review Application'), $this->url());
    }

    protected function message(): string
    {
        return __('New lecturer application from :name is awaiting review.', [
            'name' => $this->lecturerProfile->user->name,
        ]);
    }

    protected function url(): string
    {
        return route('admin.lecturer-applications.show', $this->lecturerProfile);
    }
}
