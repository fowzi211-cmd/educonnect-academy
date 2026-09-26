<div>
    <div class="bg-gray-50 border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <h1 class="text-3xl font-bold text-gray-900">{{ __('Course Catalogue') }}</h1>
            <p class="mt-2 text-gray-600">{{ __('Browse live and recorded courses across every subject.') }}</p>

            <div class="mt-6 grid grid-cols-1 sm:grid-cols-4 gap-3">
                <input wire:model.live.debounce.400ms="keyword" type="text" placeholder="{{ __('Search courses') }}"
                    class="border-gray-300 focus:border-brand-primary focus:ring-brand-primary rounded-lg shadow-sm sm:col-span-2" />

                <select wire:model.live="category" class="border-gray-300 focus:border-brand-primary focus:ring-brand-primary rounded-lg shadow-sm">
                    <option value="">{{ __('All Categories') }}</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>

                <select wire:model.live="level" class="border-gray-300 focus:border-brand-primary focus:ring-brand-primary rounded-lg shadow-sm">
                    <option value="">{{ __('All Levels') }}</option>
                    <option value="beginner">{{ __('Beginner') }}</option>
                    <option value="intermediate">{{ __('Intermediate') }}</option>
                    <option value="advanced">{{ __('Advanced') }}</option>
                </select>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse ($courses as $course)
                <a href="{{ route('courses.show', $course->slug) }}" wire:navigate class="group block bg-white border border-gray-100 rounded-2xl overflow-hidden shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition">
                    @if ($course->image_path)
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($course->image_path) }}" alt="{{ $course->title }}" class="h-40 w-full object-cover">
                    @else
                        <div class="h-40 w-full bg-brand-gradient flex items-center justify-center opacity-90">
                            <svg class="h-10 w-10 text-white/80" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                            </svg>
                        </div>
                    @endif
                    <div class="p-5">
                        <div class="text-xs font-semibold text-brand-primary">{{ $course->category?->name }}</div>
                        <h3 class="mt-1.5 font-semibold text-gray-900 group-hover:text-brand-primary transition">{{ $course->title }}</h3>
                        <p class="mt-1.5 text-sm text-gray-600 line-clamp-2">{{ $course->short_description }}</p>
                        <div class="mt-4 flex items-center justify-between text-sm">
                            <span class="text-gray-600">{{ $course->lecturers->first()?->name }}</span>
                            <span class="font-semibold text-gray-900">
                                {{ $course->monthly_price ? number_format($course->monthly_price, 2).' '.$course->currency.'/'.__('mo') : __('Free') }}
                            </span>
                        </div>
                    </div>
                </a>
            @empty
                <div class="col-span-full text-center py-16">
                    <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                    <p class="mt-3 text-gray-600">{{ __('No courses match your search yet.') }}</p>
                </div>
            @endforelse
        </div>

        <div class="mt-8">
            {{ $courses->links() }}
        </div>
    </div>
</div>
