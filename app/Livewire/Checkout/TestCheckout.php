<?php

namespace App\Livewire\Checkout;

use App\Models\Subscription;
use App\Services\Payments\PaymentService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * The sandbox gateway's local stand-in for a hosted checkout page. Nothing
 * here talks to a real payment network — clicking a button runs the exact
 * same PaymentService::confirmPayment() a verified Stripe webhook would.
 */
#[Layout('layouts.guest')]
class TestCheckout extends Component
{
    public Subscription $subscription;

    public function mount(Subscription $subscription): void
    {
        abort_unless($subscription->user_id === Auth::id(), 403);
        abort_unless($subscription->status === Subscription::STATUS_PENDING, 404);

        $this->subscription = $subscription;
    }

    public function simulateSuccess(PaymentService $payments): void
    {
        $transaction = $this->subscription->transactions()->latest()->firstOrFail();

        $payments->confirmPayment($transaction, succeeded: true, gatewayTransactionId: 'test_'.$this->subscription->id.'_'.now()->timestamp);

        $this->redirect(route('checkout.complete', $this->subscription), navigate: false);
    }

    public function simulateFailure(PaymentService $payments): void
    {
        $transaction = $this->subscription->transactions()->latest()->firstOrFail();

        $payments->confirmPayment($transaction, succeeded: false, failureReason: __('The test card was declined.'));

        $this->redirect(route('checkout.complete', $this->subscription), navigate: false);
    }

    public function render()
    {
        return view('livewire.checkout.test-checkout');
    }
}
