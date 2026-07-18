<x-public-layout :title="$title" :description="__('How EduConnect Academy collects, uses, and protects your data.')">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-16 prose-sm">
        <h1 class="text-3xl font-bold text-gray-900">{{ __('Privacy Policy') }}</h1>
        <p class="text-sm text-gray-500 mt-1">{{ __('Version') }} {{ config('platform.policy_version') }}</p>

        <div class="mt-6 space-y-5 text-gray-700 leading-relaxed">
            <p>{{ __('This policy explains what personal data EduConnect Academy collects, why, and how you can control it.') }}</p>

            <h2 class="text-lg font-semibold text-gray-900">{{ __('1. What We Collect') }}</h2>
            <p>{{ __('We collect the information you provide when registering (name, email, mobile number, country, preferred language) and, optionally, demographic details you choose to add to your profile. We also record course activity such as attendance and progress needed to run the platform.') }}</p>

            <h2 class="text-lg font-semibold text-gray-900">{{ __('2. How We Use It') }}</h2>
            <p>{{ __('Your data is used to operate your account, deliver course content you are enrolled in, process payments, send necessary transactional notifications, and improve the platform. We do not sell your personal data.') }}</p>

            <h2 class="text-lg font-semibold text-gray-900">{{ __('3. Your Choices') }}</h2>
            <p>{{ __('You can update your profile information at any time, change your notification preferences, and request a copy or deletion of your data subject to legal retention requirements, by contacting us.') }}</p>

            <h2 class="text-lg font-semibold text-gray-900">{{ __('4. Data Retention and Security') }}</h2>
            <p>{{ __('We keep personal data only as long as needed for the purposes described here or as required by law, and apply reasonable technical and organisational measures to protect it.') }}</p>

            <h2 class="text-lg font-semibold text-gray-900">{{ __('5. Cookies') }}</h2>
            <p>{{ __('Details on the cookies we use are set out in our') }} <a href="{{ route('cookie-policy') }}" wire:navigate class="underline">{{ __('Cookie Policy') }}</a>.</p>

            <h2 class="text-lg font-semibold text-gray-900">{{ __('6. Contact') }}</h2>
            <p>{{ __('For any privacy question or request, please use our') }} <a href="{{ route('contact') }}" wire:navigate class="underline">{{ __('Contact page') }}</a>.</p>
        </div>
    </div>
</x-public-layout>
