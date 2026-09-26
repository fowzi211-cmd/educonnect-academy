<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Models\QuizAttemptAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class QuizSubmissionController extends Controller
{
    public function download(Request $request, QuizAttemptAnswer $quizAttemptAnswer): StreamedResponse
    {
        $course = $quizAttemptAnswer->attempt->quiz->course;

        abort_unless($course->isTaughtBy($request->user()), 403);
        abort_unless($quizAttemptAnswer->file_path && Storage::disk('local')->exists($quizAttemptAnswer->file_path), 404);

        return Storage::disk('local')->download($quizAttemptAnswer->file_path, $quizAttemptAnswer->file_name, [
            'Cache-Control' => 'private, no-store, no-cache, must-revalidate',
            'Pragma' => 'no-cache',
        ]);
    }
}
