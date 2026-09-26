<x-public-layout :title="$page->title()" :description="__('Learn about Dr. Nada Center, a bilingual platform connecting students and lecturers.')">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <h1 class="text-3xl font-bold text-gray-900">{{ $page->title() }}</h1>

        <div class="mt-6 space-y-4 text-gray-700 leading-relaxed">
            {!! $page->body() !!}
        </div>
    </div>
</x-public-layout>
