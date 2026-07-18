<?php

namespace App\Livewire\Student;

use App\Models\Subscription;
use App\Services\Payments\PaymentService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Subscriptions extends Component
{
    public ?int $cancellingId = null;
    public string $cancelReason = '';

    public function startCancel(int $id): void
    {
        $this->cancellingId = $id;
        $this->cancelReason = '';
    }

    public function confirmCancel(PaymentService $payments): void
    {
        $this->validate(['cancelReason' => ['required', 'string', 'max:1000']]);

        $subscription = Auth::user()->subscriptions()->findOrFail($this->cancellingId);

        abort_unless(in_array($subscription->status, Subscription::ACCESS_GRANTING_STATUSES, true), 403);

        $payments->cancelSubscription($subscription, $this->cancelReason);

        $this->cancellingId = null;
    }

    public function render()
    {
        return view('livewire.student.subscriptions', [
            'subscriptions' => Auth::user()->subscriptions()->with('course', 'transactions.invoice')->latest()->get(),
        ]);
    }
}
