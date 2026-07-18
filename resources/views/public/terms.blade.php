<x-public-layout :title="$title" :description="__('Terms and conditions for using EduConnect Academy.')">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-16 prose-sm">
        <h1 class="text-3xl font-bold text-gray-900">{{ __('Terms and Conditions') }}</h1>
        <p class="text-sm text-gray-500 mt-1">{{ __('Version') }} {{ config('platform.policy_version') }}</p>

        <div class="mt-6 space-y-5 text-gray-700 leading-relaxed">
            <p>{{ __('These Terms and Conditions govern your use of EduConnect Academy. By creating an account, you agree to these terms.') }}</p>

            <h2 class="text-lg font-semibold text-gray-900">{{ __('1. Accounts') }}</h2>
            <p>{{ __('You must provide accurate registration information and keep your password secure. You are responsible for all activity under your account.') }}</p>

            <h2 class="text-lg font-semibold text-gray-900">{{ __('2. Course Subscriptions') }}</h2>
            <p>{{ __('Paid courses are billed as recurring monthly subscriptions unless stated otherwise on the course page. Access to course content is granted only after a subscription payment is confirmed.') }}</p>

            <h2 class="text-lg font-semibold text-gray-900">{{ __('3. Acceptable Use') }}</h2>
            <p>{{ __('You may not share your account, redistribute course recordings, or use the platform for any unlawful purpose. Lecturers may not contact students outside of their enrolled courses without authorisation.') }}</p>

            <h2 class="text-lg font-semibold text-gray-900">{{ __('4. Cancellations and Refunds') }}</h2>
            <p>{{ __('Cancellation and refund terms are described in our') }} <a href="{{ route('refund-policy') }}" wire:navigate class="underline">{{ __('Refund and Cancellation Policy') }}</a>.</p>

            <h2 class="text-lg font-semibold text-gray-900">{{ __('5. Changes to These Terms') }}</h2>
            <p>{{ __('We may update these terms as the platform evolves. Material changes will be reflected in the version number above, and continued use of the platform after a change constitutes acceptance.') }}</p>

            <h2 class="text-lg font-semibold text-gray-900">{{ __('6. Contact') }}</h2>
            <p>{{ __('Questions about these terms can be sent through our') }} <a href="{{ route('contact') }}" wire:navigate class="underline">{{ __('Contact page') }}</a>.</p>
        </div>
    </div>
</x-public-layout>
