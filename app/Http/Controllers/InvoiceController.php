<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function show(Request $request, Invoice $invoice): View
    {
        $user = $request->user();

        abort_unless($invoice->user_id === $user->id || $user->can('manage finances'), 403);

        return view('invoices.show', [
            'invoice' => $invoice->load('items', 'user', 'transaction.course'),
        ]);
    }
}
