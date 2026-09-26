<x-public-layout :title="$title" :description="__('Answers to common questions about Dr. Nada Center.')">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <h1 class="text-3xl font-bold text-gray-900">{{ __('Frequently Asked Questions') }}</h1>

        <div class="mt-8 space-y-6">
            @forelse ($faqs as $item)
                <div class="border-b border-gray-100 pb-6">
                    <h3 class="font-semibold text-gray-900">{{ $item->question() }}</h3>
                    <p class="mt-2 text-sm text-gray-600">{{ $item->answer() }}</p>
                </div>
            @empty
                <p class="text-gray-600">{{ __('No questions have been published yet.') }}</p>
            @endforelse
        </div>
    </div>
</x-public-layout>
