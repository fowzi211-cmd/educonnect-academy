<?php

namespace App\Http\Controllers;

use App\Models\AssignmentSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AssignmentSubmissionController extends Controller
{
    public function download(Request $request, AssignmentSubmission $assignmentSubmission): StreamedResponse
    {
        $user = $request->user();
        $course = $assignmentSubmission->assignment->course;

        abort_unless($assignmentSubmission->user_id === $user->id || $course->isTaughtBy($user), 403);
        abort_unless(Storage::disk('local')->exists($assignmentSubmission->file_path), 404);

        return Storage::disk('local')->download($assignmentSubmission->file_path, $assignmentSubmission->file_name, [
            'Cache-Control' => 'private, no-store, no-cache, must-revalidate',
            'Pragma' => 'no-cache',
        ]);
    }
}
