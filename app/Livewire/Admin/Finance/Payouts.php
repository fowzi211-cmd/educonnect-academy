<?php

namespace App\Livewire\Admin\Finance;

use App\Models\PayoutRequest;
use App\Services\Finance\PayoutService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use RuntimeException;

#[Layout('layouts.app')]
class Payouts extends Component
{
    use WithPagination;

    #[Url]
    public string $status = '';

    public ?int $rejectingId = null;

    public string $rejectReason = '';

    public ?int $payingId = null;

    public string $payoutReference = '';

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function approve(int $id, PayoutService $payouts): void
    {
        $request = PayoutRequest::findOrFail($id);

        try {
            $payouts->approve($request, Auth::user());
        } catch (RuntimeException $e) {
            $this->addError('approve', $e->getMessage());
        }
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

    public function confirmReject(PayoutService $payouts): void
    {
        $this->validate(['rejectReason' => ['required', 'string', 'max:1000']]);

        $request = PayoutRequest::findOrFail($this->rejectingId);
        $payouts->reject($request, Auth::user(), $this->rejectReason);

        $this->rejectingId = null;
    }

    public function startPay(int $id): void
    {
        $this->payingId = $id;
        $this->payoutReference = '';
    }

    public function cancelPay(): void
    {
        $this->payingId = null;
    }

    public function confirmPay(PayoutService $payouts): void
    {
        $this->validate(['payoutReference' => ['required', 'string', 'max:255']]);

        $request = PayoutRequest::findOrFail($this->payingId);
        $payouts->markPaid($request, Auth::user(), $this->payoutReference);

        $this->payingId = null;
    }

    public function render()
    {
        return view('livewire.admin.finance.payouts', [
            'requests' => PayoutRequest::with('user')
                ->when($this->status, fn ($q) => $q->where('status', $this->status))
                ->latest('requested_at')
                ->paginate(15),
        ]);
    }
}
