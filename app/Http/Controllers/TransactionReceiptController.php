<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TransactionReceiptController extends Controller
{
    public function download(Request $request, Transaction $transaction): StreamedResponse
    {
        $user = $request->user();

        abort_unless($transaction->user_id === $user->id || $user->can('manage finances'), 403);
        abort_unless($transaction->receipt_path && Storage::disk('local')->exists($transaction->receipt_path), 404);

        return Storage::disk('local')->download($transaction->receipt_path, $transaction->receipt_name, [
            'Cache-Control' => 'private, no-store, no-cache, must-revalidate',
            'Pragma' => 'no-cache',
        ]);
    }
}
