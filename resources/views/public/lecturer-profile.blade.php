<x-public-layout :title="$title" :description="$profile->headline">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <nav class="text-sm text-gray-600 mb-4">
            <a href="{{ route('lecturers.index') }}" wire:navigate class="hover:text-gray-900">{{ __('Our Lecturers') }}</a>
            <span class="mx-1">/</span>
            <span>{{ $profile->user->name }}</span>
        </nav>

        <div class="flex items-center gap-4">
            @if ($profile->photo_path)
                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($profile->photo_path) }}" alt="{{ $profile->user->name }}" class="h-20 w-20 rounded-full object-cover">
            @else
                <div class="h-20 w-20 rounded-full bg-gray-100 flex items-center justify-center text-gray-600 text-2xl font-medium">
                    {{ mb_substr($profile->user->name, 0, 1) }}
                </div>
            @endif
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $profile->user->name }}</h1>
                <p class="text-gray-600">{{ $profile->headline }}</p>
            </div>
        </div>

        <div class="mt-8 space-y-6">
            @if ($profile->biography)
                <div>
                    <h2 class="font-semibold text-gray-900">{{ __('Biography') }}</h2>
                    <p class="mt-2 text-gray-700 whitespace-pre-line">{{ $profile->biography }}</p>
                </div>
            @endif

            @if ($profile->qualifications)
                <div>
                    <h2 class="font-semibold text-gray-900">{{ __('Qualifications and Experience') }}</h2>
                    <p class="mt-2 text-gray-700 whitespace-pre-line">{{ $profile->qualifications }}</p>
                </div>
            @endif

            @if ($profile->areas_of_expertise)
                <div>
                    <h2 class="font-semibold text-gray-900">{{ __('Areas of Expertise') }}</h2>
                    <p class="mt-2 text-gray-700">{{ $profile->areas_of_expertise }}</p>
                </div>
            @endif

            <div>
                <h2 class="font-semibold text-gray-900">{{ __('Courses') }}</h2>
                @if ($courses->isEmpty())
                    <p class="mt-2 text-sm text-gray-600">{{ __('No published courses yet.') }}</p>
                @else
                    <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach ($courses as $course)
                            <a href="{{ route('courses.show', $course->slug) }}" wire:navigate class="block border border-gray-100 rounded-lg p-4 hover:shadow-sm transition">
                                <div class="font-medium text-gray-900">{{ $course->title }}</div>
                                <div class="text-sm text-gray-600">{{ $course->short_description }}</div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-public-layout>
