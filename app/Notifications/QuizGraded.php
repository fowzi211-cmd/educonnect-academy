<?php

namespace App\Notifications;

use App\Models\QuizAttempt;
use Illuminate\Notifications\Notification;

class QuizGraded extends Notification
{
    public function __construct(protected QuizAttempt $attempt) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $quiz = $this->attempt->quiz;

        return [
            'type' => 'quiz.graded',
            'message' => __('Your results for ":title" are ready: :percent%', [
                'title' => $quiz->title,
                'percent' => $this->attempt->score_percent,
            ]),
            'url' => route('my-courses.quizzes.take', $quiz),
        ];
    }
}
