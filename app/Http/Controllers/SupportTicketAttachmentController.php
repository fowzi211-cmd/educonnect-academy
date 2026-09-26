<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SupportTicketAttachmentController extends Controller
{
    public function download(Request $request, SupportTicket $supportTicket): StreamedResponse
    {
        $user = $request->user();

        abort_unless($supportTicket->user_id === $user->id || $user->can('manage support tickets'), 403);
        abort_unless($supportTicket->attachment_path && Storage::disk('local')->exists($supportTicket->attachment_path), 404);

        return Storage::disk('local')->download($supportTicket->attachment_path, $supportTicket->attachment_name, [
            'Cache-Control' => 'private, no-store, no-cache, must-revalidate',
            'Pragma' => 'no-cache',
        ]);
    }
}
