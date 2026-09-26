<?php

namespace App\Notifications;

use App\Models\Announcement;
use Illuminate\Notifications\Notification;

class AnnouncementPublished extends Notification
{
    public function __construct(protected Announcement $announcement) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'announcement.published',
            'message' => __('New announcement in :course: :title', [
                'course' => $this->announcement->course->title,
                'title' => $this->announcement->title,
            ]),
            'url' => route('my-courses.classroom', $this->announcement->course),
        ];
    }
}
