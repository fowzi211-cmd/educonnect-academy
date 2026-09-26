<?php

namespace App\Livewire\Admin\Finance;

use App\Models\AuditLog;
use App\Models\Transaction;
use App\Services\Payments\PaymentService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Transactions extends Component
{
    use WithPagination;

    public string $status = '';

    public string $studentEmail = '';

    public ?int $refundingId = null;

    public string $refundAmount = '';

    public string $refundReason = '';

    public bool $refundEndsAccess = false;

    public ?int $rejectingId = null;

    public string $rejectReason = '';

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingStudentEmail(): void
    {
        $this->resetPage();
    }

    public function startRefund(int $id): void
    {
        $transaction = Transaction::findOrFail($id);

        $this->refundingId = $id;
        $this->refundAmount = number_format($transaction->remainingRefundable(), 2, '.', '');
        $this->refundReason = '';
        $this->refundEndsAccess = false;
    }

    public function cancelRefund(): void
    {
        $this->refundingId = null;
    }

    public function confirmRefund(PaymentService $payments): void
    {
        $transaction = Transaction::findOrFail($this->refundingId);

        $this->validate([
            'refundAmount' => ['required', 'numeric', 'gt:0', 'lte:'.$transaction->remainingRefundable()],
            'refundReason' => ['required', 'string', 'max:1000'],
        ]);

        try {
            $payments->processRefund(
                $transaction,
                (float) $this->refundAmount,
                $this->refundReason,
                Auth::user(),
                $this->refundEndsAccess,
            );
        } catch (\RuntimeException $e) {
            $this->addError('refundAmount', $e->getMessage());

            return;
        }

        $this->refundingId = null;
    }

    public function approveBankTransfer(int $id, PaymentService $payments): void
    {
        $transaction = Transaction::findOrFail($id);

        abort_unless($transaction->gateway === 'bank_transfer' && $transaction->receipt_path, 404);

        $transaction->update(['reviewed_by' => Auth::id(), 'reviewed_at' => now()]);

        $payments->confirmPayment($transaction, succeeded: true, gatewayTransactionId: 'bank_transfer_'.$transaction->id);

        AuditLog::record('transaction.bank_transfer_approved', subject: $transaction);
    }

    public function startReject(int $id): void
    {
        $this->rejectingId = $id;
        $this->rejectReason = '';
    }

    public function cancelReject(): void
    {
        $this->rejectingId = null;
    }

    public function confirmRejectBankTransfer(PaymentService $payments): void
    {
        $transaction = Transaction::findOrFail($this->rejectingId);

        abort_unless($transaction->gateway === 'bank_transfer' && $transaction->receipt_path, 404);

        $this->validate([
            'rejectReason' => ['required', 'string', 'max:1000'],
        ]);

        $transaction->update(['reviewed_by' => Auth::id(), 'reviewed_at' => now()]);

        $payments->confirmPayment($transaction, succeeded: false, failureReason: $this->rejectReason);

        AuditLog::record('transaction.bank_transfer_rejected', subject: $transaction, reason: $this->rejectReason);

        $this->rejectingId = null;
    }

    public function render()
    {
        $transactions = Transaction::query()
            ->with(['user', 'course', 'refunds', 'bankAccount'])
            ->when($this->status, fn ($query) => $query->where('status', $this->status))
            ->when($this->studentEmail, fn ($query) => $query->whereHas('user', fn ($q) => $q->where('email', 'like', '%'.$this->studentEmail.'%')))
            ->latest()
            ->paginate(15);

        return view('livewire.admin.finance.transactions', [
            'transactions' => $transactions,
        ]);
    }
}
