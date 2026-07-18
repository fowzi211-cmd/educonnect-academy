<div @if ($subscription->status === 'pending') wire:poll.2s @endif>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Subscription') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6 text-center">
                @if ($subscription->status === 'active')
                    <div class="text-green-600 text-3xl">&#10003;</div>
                    <h3 class="mt-3 font-semibold text-lg text-gray-900">{{ __("You're subscribed!") }}</h3>
                    <p class="mt-2 text-sm text-gray-600">
                        {{ __('Your payment was confirmed and you now have access to :course.', ['course' => $subscription->course->title]) }}
                    </p>
                    <div class="mt-6 flex justify-center gap-3">
                        <a href="{{ route('my-courses.classroom', $subscription->course) }}" wire:navigate class="rounded-md px-4 py-2 text-sm font-medium text-white bg-gray-900 hover:bg-gray-700">
                            {{ __('Go to Classroom') }}
                        </a>
                        <a href="{{ route('my-subscriptions.index') }}" wire:navigate class="rounded-md px-4 py-2 text-sm font-medium text-gray-700 ring-1 ring-gray-300 hover:bg-gray-50">
                            {{ __('View Subscriptions') }}
                        </a>
                    </div>
                @elseif ($subscription->status === 'payment_failed')
                    <div class="text-red-600 text-3xl">&times;</div>
                    <h3 class="mt-3 font-semibold text-lg text-gray-900">{{ __('Payment failed') }}</h3>
                    <p class="mt-2 text-sm text-gray-600">
                        {{ $transaction?->failure_reason ?? __('Your payment could not be completed.') }}
                    </p>
                    <div class="mt-6">
                        <a href="{{ route('checkout.show', $subscription->course) }}" wire:navigate class="rounded-md px-4 py-2 text-sm font-medium text-white bg-gray-900 hover:bg-gray-700">
                            {{ __('Try Again') }}
                        </a>
                    </div>
                @else
                    <div class="text-gray-400 text-3xl">&hellip;</div>
                    <h3 class="mt-3 font-semibold text-lg text-gray-900">{{ __('Confirming your payment') }}</h3>
                    <p class="mt-2 text-sm text-gray-600">
                        {{ __("This usually takes a few seconds. This page will update automatically — no need to refresh.") }}
                    </p>
                @endif
            </div>
        </div>
    </div>
</div>
