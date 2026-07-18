<?php

namespace App\Livewire\Checkout;

use App\Models\Subscription;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Complete extends Component
{
    public Subscription $subscription;

    public function mount(Subscription $subscription): void
    {
        abort_unless($subscription->user_id === Auth::id(), 403);

        $this->subscription = $subscription;
    }

    public function render()
    {
        return view('livewire.checkout.complete', [
            'transaction' => $this->subscription->transactions()->latest()->first(),
        ]);
    }
}
