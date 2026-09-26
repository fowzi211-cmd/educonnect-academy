<x-public-layout :title="$page->title()" :description="__('Terms and conditions for using Dr. Nada Center.')">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-16 prose-sm">
        <h1 class="text-3xl font-bold text-gray-900">{{ $page->title() }}</h1>
        <p class="text-sm text-gray-600 mt-1">{{ __('Version') }} {{ \App\Models\Setting::get('platform.policy_version', config('platform.policy_version')) }}</p>

        <div class="mt-6 space-y-5 text-gray-700 leading-relaxed [&_h2]:text-lg [&_h2]:font-semibold [&_h2]:text-gray-900 [&_h2]:mt-6">
            {!! $page->body() !!}
        </div>
    </div>
</x-public-layout>
