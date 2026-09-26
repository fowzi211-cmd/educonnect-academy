<x-public-layout :title="$title" :description="__('Get in touch with the Dr. Nada Center team.')">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <h1 class="text-3xl font-bold text-gray-900">{{ __('Contact Us') }}</h1>
        <p class="mt-2 text-gray-600">{{ __("Have a question? Send us a message and we'll get back to you.") }}</p>

        <div class="mt-8">
            <livewire:contact-form />
        </div>
    </div>
</x-public-layout>
