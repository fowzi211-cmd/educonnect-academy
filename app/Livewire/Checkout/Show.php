<?php

namespace App\Livewire\Checkout;

use App\Models\BankAccount;
use App\Models\Coupon;
use App\Models\Course;
use App\Services\Payments\PaymentGatewayManager;
use App\Services\Payments\PaymentService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.app')]
class Show extends Component
{
    public Course $course;

    public string $couponCode = '';

    public ?Coupon $appliedCoupon = null;

    public ?float $discount = null;

    public string $paymentMethod = 'gateway';

    #[Locked]
    public bool $bankTransferAvailable = false;

    #[Locked]
    public bool $onlinePaymentAvailable = false;

    public function mount(Course $course, PaymentGatewayManager $gateways): void
    {
        abort_unless($course->status === Course::STATUS_PUBLISHED, 404);
        abort_if($course->monthly_price === null, 404);

        $this->course = $course;
        $this->bankTransferAvailable = BankAccount::active()->exists();

        // The sandbox "test" gateway lets anyone simulate a successful payment,
        // so it must never be offered as a real checkout option outside local
        // development — a production deploy whose active gateway is still
        // "test" gets bank transfer only.
        $this->onlinePaymentAvailable = $gateways->activeDriverName() !== 'test'
            || app()->environment('local', 'testing');

        if (! $this->onlinePaymentAvailable) {
            $this->paymentMethod = 'bank_transfer';
        }
    }

    public function applyCoupon(): void
    {
        $this->resetErrorBag('couponCode');
        $this->appliedCoupon = null;
        $this->discount = null;

        if (! $this->couponCode) {
            return;
        }

        $coupon = Coupon::where('code', $this->couponCode)->first();

        if (! $coupon) {
            $this->addError('couponCode', __('Coupon code not found.'));

            return;
        }

        if ($error = $coupon->validationErrorFor(Auth::user(), $this->course)) {
            $this->addError('couponCode', $error);

            return;
        }

        $this->appliedCoupon = $coupon;
        $this->discount = $coupon->discountFor((float) $this->course->monthly_price);
    }

    public function subscribe(PaymentService $payments): void
    {
        // Server-side enforcement of the same rules the UI applies — a crafted
        // request must not be able to reach a payment path the page never offered.
        if ($this->paymentMethod === 'bank_transfer') {
            abort_unless($this->bankTransferAvailable, 403);
        } else {
            abort_unless($this->onlinePaymentAvailable, 403);
        }

        $gatewayName = $this->paymentMethod === 'bank_transfer' ? 'bank_transfer' : null;

        try {
            $result = $payments->initiateSubscription(Auth::user(), $this->course, $this->appliedCoupon, $gatewayName);
        } catch (\RuntimeException $e) {
            $this->addError('subscribe', $e->getMessage());

            return;
        }

        $this->redirect($result['redirectUrl']);
    }

    public function render()
    {
        return view('livewire.checkout.show');
    }
}
