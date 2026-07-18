<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LecturerDocument;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LecturerDocumentController extends Controller
{
    public function download(LecturerDocument $lecturerDocument): StreamedResponse
    {
        abort_unless(Storage::disk('local')->exists($lecturerDocument->file_path), 404);

        return Storage::disk('local')->download($lecturerDocument->file_path, $lecturerDocument->original_name);
    }
}
