<div>
    <div class="mb-4 rounded-md bg-amber-50 border border-amber-200 px-4 py-3 text-sm text-amber-800">
        {{ __('Sandbox payment page — no real card is charged. This stands in for a hosted gateway page until Stripe (or another provider) is configured.') }}
    </div>

    <h2 class="text-lg font-semibold text-gray-900">{{ __('Confirm Test Payment') }}</h2>

    <div class="mt-4 text-sm text-gray-700 space-y-1">
        <div class="flex justify-between">
            <span>{{ __('Course') }}</span>
            <span class="font-medium">{{ $subscription->course->title }}</span>
        </div>
        <div class="flex justify-between">
            <span>{{ __('Amount') }}</span>
            <span class="font-medium">
                {{ number_format($subscription->transactions()->latest()->value('amount'), 2) }} {{ $subscription->currency }}
            </span>
        </div>
    </div>

    <div class="mt-6 border-t border-gray-100 pt-6 space-y-3">
        <div>
            <x-input-label :value="__('Card Number')" />
            <x-text-input value="4242 4242 4242 4242" class="block mt-1 w-full" type="text" disabled />
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div>
                <x-input-label :value="__('Expiry')" />
                <x-text-input value="12/34" class="block mt-1 w-full" type="text" disabled />
            </div>
            <div>
                <x-input-label :value="__('CVC')" />
                <x-text-input value="123" class="block mt-1 w-full" type="text" disabled />
            </div>
        </div>
    </div>

    <div class="mt-6 flex flex-col gap-2">
        <x-primary-button wire:click="simulateSuccess" class="w-full justify-center">
            {{ __('Simulate Successful Payment') }}
        </x-primary-button>
        <x-danger-button wire:click="simulateFailure" class="w-full justify-center">
            {{ __('Simulate Declined Payment') }}
        </x-danger-button>
    </div>
</div>
