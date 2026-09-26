<?php

namespace App\Notifications;

use App\Models\DiscussionReply;
use Illuminate\Notifications\Notification;

class DiscussionReplyPosted extends Notification
{
    public function __construct(protected DiscussionReply $reply) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $thread = $this->reply->thread;

        return [
            'type' => 'discussion.reply_posted',
            'message' => __(':name replied to ":title"', [
                'name' => $this->reply->user->name,
                'title' => $thread->title,
            ]),
            'url' => route('courses.discussions.show', $thread),
        ];
    }
}
