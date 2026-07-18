<x-public-layout :title="$title" :description="__('Refund and cancellation terms for EduConnect Academy course subscriptions.')">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-16 prose-sm">
        <h1 class="text-3xl font-bold text-gray-900">{{ __('Refund and Cancellation Policy') }}</h1>
        <p class="text-sm text-gray-500 mt-1">{{ __('Version') }} {{ config('platform.policy_version') }}</p>

        <div class="mt-6 space-y-5 text-gray-700 leading-relaxed">
            <h2 class="text-lg font-semibold text-gray-900">{{ __('1. Cancelling a Subscription') }}</h2>
            <p>{{ __('You may cancel a course subscription at any time from your dashboard. Cancelling stops future renewal but does not end access early — you keep access until the end of the period you already paid for, unless a refund is issued.') }}</p>

            <h2 class="text-lg font-semibold text-gray-900">{{ __('2. Refund Eligibility') }}</h2>
            <p>{{ __('Refund eligibility (such as a trial period or a short cooling-off window after a first payment) is set per course and shown on the course page before you subscribe. Refunds outside that window are reviewed case by case.') }}</p>

            <h2 class="text-lg font-semibold text-gray-900">{{ __('3. How Refunds Are Processed') }}</h2>
            <p>{{ __('Approved refunds are returned to the original payment method. Processing times depend on the payment provider and are typically a few business days.') }}</p>

            <h2 class="text-lg font-semibold text-gray-900">{{ __('4. Administrative Cancellation') }}</h2>
            <p>{{ __('We may suspend or cancel access in cases of policy violation, fraud, or non-payment, in accordance with these terms.') }}</p>

            <h2 class="text-lg font-semibold text-gray-900">{{ __('5. Requesting a Refund') }}</h2>
            <p>{{ __('To request a refund, contact us through the') }} <a href="{{ route('contact') }}" wire:navigate class="underline">{{ __('Contact page') }}</a> {{ __('with your account email and the course in question.') }}</p>
        </div>
    </div>
</x-public-layout>
