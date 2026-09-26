<?php

namespace App\Livewire\Lecturer;

use App\Models\CommissionEarning;
use App\Models\PayoutRequest;
use App\Models\Setting;
use App\Services\Finance\PayoutService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use RuntimeException;

#[Layout('layouts.app')]
class Earnings extends Component
{
    use WithPagination;

    public ?string $requestError = null;

    public bool $requestSuccess = false;

    public function requestPayout(PayoutService $payouts): void
    {
        $this->requestError = null;
        $this->requestSuccess = false;

        try {
            $payouts->requestPayout(Auth::user());
            $this->requestSuccess = true;
        } catch (RuntimeException $e) {
            $this->requestError = $e->getMessage();
        }
    }

    public function render(PayoutService $payouts)
    {
        $user = Auth::user();

        return view('livewire.lecturer.earnings', [
            'earnings' => CommissionEarning::where('user_id', $user->id)->with('course')->latest()->paginate(15),
            'payoutRequests' => PayoutRequest::where('user_id', $user->id)->latest('requested_at')->get(),
            'availableBalance' => $payouts->availableBalance($user),
            'payoutThreshold' => (float) Setting::get('commission.payout_threshold', 0),
            'totalPaid' => CommissionEarning::where('user_id', $user->id)->where('status', CommissionEarning::STATUS_PAID)->sum('amount'),
        ]);
    }
}
