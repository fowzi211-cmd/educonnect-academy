<?php

namespace App\Livewire\Checkout;

use App\Models\Coupon;
use App\Models\Course;
use App\Services\Payments\PaymentService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Show extends Component
{
    public Course $course;

    public string $couponCode = '';
    public ?Coupon $appliedCoupon = null;
    public ?float $discount = null;

    public function mount(Course $course): void
    {
        abort_unless($course->status === Course::STATUS_PUBLISHED, 404);
        abort_if($course->monthly_price === null, 404);

        $this->course = $course;
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
        try {
            $result = $payments->initiateSubscription(Auth::user(), $this->course, $this->appliedCoupon);
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
