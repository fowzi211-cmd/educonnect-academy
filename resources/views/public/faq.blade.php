<x-public-layout :title="$title" :description="__('Answers to common questions about EduConnect Academy.')">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <h1 class="text-3xl font-bold text-gray-900">{{ __('Frequently Asked Questions') }}</h1>

        <div class="mt-8 space-y-6">
            @foreach ([
                ['q' => __('Do I need to pay to create an account?'), 'a' => __('No. Creating an account is free. You only pay when you subscribe to a course.')],
                ['q' => __('What languages is the platform available in?'), 'a' => __('EduConnect Academy is available in both Arabic and English. You can switch languages at any time from the header.')],
                ['q' => __('Can I access recorded lessons after I finish a course?'), 'a' => __('Access to recorded content after completion depends on the settings each course is published with; this will be shown clearly on the course page.')],
                ['q' => __('What currency are prices shown in?'), 'a' => __('Prices are shown in Saudi Riyal (SAR) by default. The platform is built to support additional currencies in the future.')],
                ['q' => __('How do I contact support?'), 'a' => __('Use the Contact page to send us a message, or email us directly at the address shown in the footer.')],
            ] as $item)
                <div class="border-b border-gray-100 pb-6">
                    <h3 class="font-semibold text-gray-900">{{ $item['q'] }}</h3>
                    <p class="mt-2 text-sm text-gray-600">{{ $item['a'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</x-public-layout>
