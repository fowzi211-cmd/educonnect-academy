<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Checkout') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <div class="flex items-center gap-4">
                    @if ($course->image_path)
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($course->image_path) }}" alt="{{ $course->title }}" class="h-16 w-16 rounded-md object-cover">
                    @endif
                    <div>
                        <h3 class="font-semibold text-gray-900">{{ $course->title }}</h3>
                        <p class="text-sm text-gray-500">{{ __('Monthly subscription') }}</p>
                    </div>
                </div>

                <div class="mt-6 border-t border-gray-100 pt-6 space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">{{ __('Price') }}</span>
                        <span class="text-gray-900">{{ number_format($course->monthly_price, 2) }} {{ $course->currency }}</span>
                    </div>

                    @if ($appliedCoupon)
                        <div class="flex justify-between text-green-700">
                            <span>{{ __('Coupon') }} ({{ $appliedCoupon->code }})</span>
                            <span>&minus;{{ number_format($discount, 2) }} {{ $course->currency }}</span>
                        </div>
                    @endif

                    <div class="flex justify-between font-semibold text-gray-900 border-t border-gray-100 pt-3">
                        <span>{{ __('Total due today') }}</span>
                        <span>{{ number_format($course->monthly_price - ($discount ?? 0), 2) }} {{ $course->currency }}</span>
                    </div>
                </div>

                <div class="mt-6">
                    <x-input-label for="couponCode" :value="__('Coupon Code (optional)')" />
                    <div class="mt-1 flex gap-2">
                        <x-text-input wire:model="couponCode" id="couponCode" class="block w-full" type="text" />
                        <x-secondary-button type="button" wire:click="applyCoupon">{{ __('Apply') }}</x-secondary-button>
                    </div>
                    <x-input-error :messages="$errors->get('couponCode')" class="mt-2" />
                </div>

                <x-input-error :messages="$errors->get('subscribe')" class="mt-4" />

                <div class="mt-6">
                    <x-primary-button wire:click="subscribe" class="w-full justify-center">
                        {{ __('Proceed to Payment') }}
                    </x-primary-button>
                    <p class="text-xs text-gray-500 mt-2 text-center">
                        {{ __('You can cancel future renewals at any time from your subscriptions page.') }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
