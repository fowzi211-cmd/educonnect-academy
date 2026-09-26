<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AssignmentAttachmentController extends Controller
{
    public function download(Request $request, Assignment $assignment): StreamedResponse
    {
        abort_unless($assignment->course->isAccessibleBy($request->user()), 403);
        abort_unless($assignment->attachment_path && Storage::disk('local')->exists($assignment->attachment_path), 404);

        return Storage::disk('local')->download($assignment->attachment_path, $assignment->attachment_name, [
            'Cache-Control' => 'private, no-store, no-cache, must-revalidate',
            'Pragma' => 'no-cache',
        ]);
    }
}
