<x-public-layout :title="$title" :description="__('How EduConnect Academy uses cookies.')">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-16 prose-sm">
        <h1 class="text-3xl font-bold text-gray-900">{{ __('Cookie Policy') }}</h1>
        <p class="text-sm text-gray-500 mt-1">{{ __('Version') }} {{ config('platform.policy_version') }}</p>

        <div class="mt-6 space-y-5 text-gray-700 leading-relaxed">
            <p>{{ __('EduConnect Academy uses a small number of cookies to run the platform.') }}</p>

            <h2 class="text-lg font-semibold text-gray-900">{{ __('Essential Cookies') }}</h2>
            <p>{{ __('These keep you signed in, remember your chosen interface language, and protect forms from cross-site request forgery. The platform cannot function without them, so they cannot be turned off.') }}</p>

            <h2 class="text-lg font-semibold text-gray-900">{{ __('Optional Cookies') }}</h2>
            <p>{{ __('As features such as analytics are added, any optional cookies will be listed here along with a way to opt out, in line with our') }} <a href="{{ route('privacy') }}" wire:navigate class="underline">{{ __('Privacy Policy') }}</a>.</p>

            <h2 class="text-lg font-semibold text-gray-900">{{ __('Managing Cookies') }}</h2>
            <p>{{ __('Most browsers let you block or delete cookies in their settings. Blocking essential cookies will prevent you from staying signed in.') }}</p>
        </div>
    </div>
</x-public-layout>
