<?php

namespace App\Http\Controllers;

use App\Models\LessonResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LessonResourceController extends Controller
{
    public function download(Request $request, LessonResource $lessonResource): StreamedResponse
    {
        $course = $lessonResource->lesson->section->course;

        abort_unless($course->isAccessibleBy($request->user()), 403);
        abort_unless(Storage::disk('local')->exists($lessonResource->file_path), 404);

        return Storage::disk('local')->download($lessonResource->file_path, $lessonResource->original_name, [
            'Cache-Control' => 'private, no-store, no-cache, must-revalidate',
            'Pragma' => 'no-cache',
        ]);
    }
}
