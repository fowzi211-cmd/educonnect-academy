<div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <h1 class="text-2xl font-bold text-gray-900">{{ __('Course Catalogue') }}</h1>

        <div class="mt-6 grid grid-cols-1 sm:grid-cols-4 gap-3">
            <input wire:model.live.debounce.400ms="keyword" type="text" placeholder="{{ __('Search courses') }}"
                class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm sm:col-span-2" />

            <select wire:model.live="category" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                <option value="">{{ __('All Categories') }}</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>

            <select wire:model.live="level" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                <option value="">{{ __('All Levels') }}</option>
                <option value="beginner">{{ __('Beginner') }}</option>
                <option value="intermediate">{{ __('Intermediate') }}</option>
                <option value="advanced">{{ __('Advanced') }}</option>
            </select>
        </div>

        <div class="mt-8 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse ($courses as $course)
                <a href="{{ route('courses.show', $course->slug) }}" wire:navigate class="block bg-white border border-gray-100 rounded-lg overflow-hidden shadow-sm hover:shadow-md transition">
                    @if ($course->image_path)
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($course->image_path) }}" alt="{{ $course->title }}" class="h-40 w-full object-cover">
                    @else
                        <div class="h-40 w-full bg-gray-100 flex items-center justify-center text-gray-400 text-sm">{{ __('No image') }}</div>
                    @endif
                    <div class="p-4">
                        <div class="text-xs text-gray-500">{{ $course->category?->name }}</div>
                        <h3 class="mt-1 font-semibold text-gray-900">{{ $course->title }}</h3>
                        <p class="mt-1 text-sm text-gray-600 line-clamp-2">{{ $course->short_description }}</p>
                        <div class="mt-3 flex items-center justify-between text-sm">
                            <span class="text-gray-500">{{ $course->lecturers->first()?->name }}</span>
                            <span class="font-medium text-gray-900">
                                {{ $course->monthly_price ? number_format($course->monthly_price, 2).' '.$course->currency.'/'.__('mo') : __('Free') }}
                            </span>
                        </div>
                    </div>
                </a>
            @empty
                <p class="col-span-full text-center text-gray-500 py-12">{{ __('No courses match your search yet.') }}</p>
            @endforelse
        </div>

        <div class="mt-8">
            {{ $courses->links() }}
        </div>
    </div>
</div>
