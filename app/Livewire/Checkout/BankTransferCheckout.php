<?php

namespace App\Livewire\Checkout;

use App\Models\AuditLog;
use App\Models\BankAccount;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use App\Notifications\BankTransferReceiptReceived;
use App\Notifications\BankTransferReceiptSubmitted;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * The bank-transfer gateway's local stand-in for a hosted checkout page.
 * The student uploads proof of a manual transfer; the transaction stays
 * pending until an admin reviews the receipt and approves or rejects it
 * from the Finance > Transactions screen, which then runs through the
 * exact same PaymentService::confirmPayment() path any other gateway uses.
 */
#[Layout('layouts.guest')]
class BankTransferCheckout extends Component
{
    use WithFileUploads;

    public Subscription $subscription;

    public $receipt;

    public ?int $bankAccountId = null;

    public string $declaredAmount = '';

    public string $bankReferenceNumber = '';

    public function mount(Subscription $subscription): void
    {
        abort_unless($subscription->user_id === Auth::id(), 403);
        abort_unless($subscription->status === Subscription::STATUS_PENDING, 404);

        $this->subscription = $subscription;
    }

    public function submitReceipt(): void
    {
        $validated = $this->validate([
            'bankAccountId' => ['required', 'integer', 'exists:bank_accounts,id'],
            'receipt' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'declaredAmount' => ['required', 'numeric', 'gt:0'],
            'bankReferenceNumber' => ['required', 'string', 'max:100', Rule::unique('transactions', 'bank_reference_number')],
        ]);

        $transaction = $this->subscription->transactions()->latest()->firstOrFail();

        abort_unless($transaction->status === Transaction::STATUS_PENDING, 404);

        $hash = hash_file('sha256', $this->receipt->getRealPath());

        if (Transaction::where('receipt_hash', $hash)->exists()) {
            $this->addError('receipt', __('This exact receipt file has already been submitted for another payment.'));

            return;
        }

        $path = $this->receipt->store('bank-transfer-receipts', 'local');

        $transaction->update([
            'bank_account_id' => $validated['bankAccountId'],
            'receipt_path' => $path,
            'receipt_name' => $this->receipt->getClientOriginalName(),
            'receipt_hash' => $hash,
            'declared_amount' => $validated['declaredAmount'],
            'bank_reference_number' => $validated['bankReferenceNumber'],
            'receipt_reference_number' => Transaction::nextReceiptReferenceNumber(),
        ]);

        AuditLog::record('transaction.receipt_uploaded', subject: $transaction, new: [
            'bank_account_id' => $validated['bankAccountId'],
            'declared_amount' => $validated['declaredAmount'],
            'bank_reference_number' => $validated['bankReferenceNumber'],
        ]);

        $transaction->refresh();

        $this->subscription->user->notify(new BankTransferReceiptReceived($transaction));

        Notification::send(
            User::permission('manage finances')->get(),
            new BankTransferReceiptSubmitted($transaction),
        );

        $this->redirect(route('checkout.complete', $this->subscription), navigate: false);
    }

    public function render()
    {
        return view('livewire.checkout.bank-transfer-checkout', [
            'bankAccounts' => BankAccount::active()->get(),
        ]);
    }
}
