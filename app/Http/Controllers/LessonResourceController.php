<?php

namespace App\Http\Controllers;

use App\Models\LessonResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class LessonResourceController extends Controller
{
    /**
     * Serve a lesson resource for on-screen viewing only. There is no download
     * endpoint: the response is inline, never cached, and limited to types the
     * classroom can render (PDF, images, audio, video).
     */
    public function view(Request $request, LessonResource $lessonResource): BinaryFileResponse
    {
        $course = $lessonResource->lesson->section->course;

        abort_unless($course->isAccessibleBy($request->user()), 403);
        abort_unless($lessonResource->viewerKind() !== null, 415);
        abort_unless(Storage::disk('local')->exists($lessonResource->file_path), 404);

        $response = response()->file(Storage::disk('local')->path($lessonResource->file_path), [
            'Content-Type' => $lessonResource->viewerMime(),
            'Content-Disposition' => 'inline',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store, no-cache, must-revalidate',
            'Pragma' => 'no-cache',
        ]);

        // Symfony marks file responses "public" by default, which contradicts no-store.
        return $response->setPrivate();
    }
}
