<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My Courses') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse ($courses as $course)
                    <a href="{{ route('my-courses.classroom', $course) }}" wire:navigate class="block bg-white border border-gray-100 rounded-lg overflow-hidden shadow-sm hover:shadow-md transition">
                        @if ($course->image_path)
                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($course->image_path) }}" alt="{{ $course->title }}" class="h-32 w-full object-cover">
                        @else
                            <div class="h-32 w-full bg-gray-100 flex items-center justify-center text-gray-400 text-sm">{{ __('No image') }}</div>
                        @endif
                        <div class="p-4">
                            <div class="text-xs text-gray-500">{{ $course->category?->name }}</div>
                            <h3 class="mt-1 font-semibold text-gray-900">{{ $course->title }}</h3>
                            <p class="mt-1 text-sm text-gray-600">{{ $course->lecturers->first()?->name }}</p>

                            @php $progress = $course->videoProgressPercentFor(auth()->user()); @endphp
                            @if ($progress !== null)
                                <div class="mt-3">
                                    <x-progress-bar :percent="$progress" />
                                </div>
                            @endif
                        </div>
                    </a>
                @empty
                    <p class="col-span-full text-center text-gray-500 py-12">
                        {{ __("You're not enrolled in any courses yet.") }}
                        <a href="{{ route('courses.index') }}" wire:navigate class="underline">{{ __('Browse the catalogue') }}</a>.
                    </p>
                @endforelse
            </div>
        </div>
    </div>
</div>
