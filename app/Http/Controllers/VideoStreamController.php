<?php

namespace App\Http\Controllers;

use App\Models\RecordedVideo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class VideoStreamController extends Controller
{
    /**
     * Stream a lesson's recorded video to authorised viewers only.
     * The file lives on the private ("local") disk — never a public URL —
     * and BinaryFileResponse handles HTTP Range requests for seeking.
     */
    public function show(Request $request, RecordedVideo $recordedVideo): BinaryFileResponse
    {
        $course = $recordedVideo->lesson->section->course;

        abort_unless($course->isAccessibleBy($request->user()), 403);
        abort_unless(Storage::disk('local')->exists($recordedVideo->video_path), 404);

        // Never let the browser (or any shared cache) retain this response: access can be
        // revoked between requests, and a cached copy would keep playing regardless.
        $response = response()->file(Storage::disk('local')->path($recordedVideo->video_path), [
            'Content-Disposition' => 'inline',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store, no-cache, must-revalidate',
            'Pragma' => 'no-cache',
        ]);

        // Symfony marks file responses "public" by default, which contradicts no-store.
        return $response->setPrivate();
    }
}
