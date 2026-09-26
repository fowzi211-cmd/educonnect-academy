<?php

namespace App\Notifications;

use App\Models\AssignmentSubmission;
use Illuminate\Notifications\Notification;

class AssignmentGraded extends Notification
{
    public function __construct(protected AssignmentSubmission $submission) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $assignment = $this->submission->assignment;

        return [
            'type' => 'assignment.graded',
            'message' => __('Your assignment ":title" was graded: :score / :max', [
                'title' => $assignment->title,
                'score' => $this->submission->score,
                'max' => $assignment->max_points,
            ]),
            'url' => route('my-courses.assignments.submit', $assignment),
        ];
    }
}
