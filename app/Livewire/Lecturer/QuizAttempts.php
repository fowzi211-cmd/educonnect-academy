<?php

namespace App\Livewire\Lecturer;

use App\Models\Quiz;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class QuizAttempts extends Component
{
    public Quiz $quiz;

    public function mount(Quiz $quiz): void
    {
        abort_unless($quiz->course->isTaughtBy(Auth::user()), 403);
        $this->quiz = $quiz;
    }

    public function render()
    {
        return view('livewire.lecturer.quiz-attempts', [
            'attempts' => $this->quiz->attempts()
                ->with('user')
                ->whereIn('status', ['submitted', 'graded'])
                ->withCount(['answers as ungraded_count' => fn ($q) => $q->whereNull('points_awarded')])
                ->latest('started_at')
                ->get(),
        ]);
    }
}
