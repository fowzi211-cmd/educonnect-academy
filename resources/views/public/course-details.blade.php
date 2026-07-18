<x-public-layout :title="$title" :description="$description">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <nav class="text-sm text-gray-500 mb-4">
            <a href="{{ route('courses.index') }}" wire:navigate class="hover:text-gray-900">{{ __('Course Catalogue') }}</a>
            <span class="mx-1">/</span>
            <span>{{ $course->title }}</span>
        </nav>

        @if ($course->image_path)
            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($course->image_path) }}" alt="{{ $course->title }}" class="w-full h-64 object-cover rounded-lg">
        @endif

        <div class="mt-6 flex items-start justify-between gap-6 flex-wrap">
            <div>
                <div class="text-sm text-gray-500">{{ $course->category?->name }}</div>
                <h1 class="mt-1 text-2xl font-bold text-gray-900">{{ $course->title }}</h1>
                <p class="mt-2 text-gray-600">{{ $course->short_description }}</p>
            </div>
            <div class="text-end">
                <div class="text-2xl font-bold text-gray-900">
                    {{ $course->monthly_price ? number_format($course->monthly_price, 2).' '.$course->currency.' / '.__('month') : __('Free') }}
                </div>
                @if ($course->trial_period_days)
                    <div class="text-sm text-gray-500">{{ __(':days-day free trial', ['days' => $course->trial_period_days]) }}</div>
                @endif
            </div>
        </div>

        <div class="mt-4 flex flex-wrap gap-2 text-xs">
            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 bg-gray-100 text-gray-700">{{ ucfirst($course->level) }}</span>
            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 bg-gray-100 text-gray-700">{{ ucfirst($course->delivery_format) }}</span>
            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 bg-gray-100 text-gray-700">
                {{ $course->teaching_language === 'both' ? __('Arabic and English') : ($course->teaching_language === 'ar' ? __('Arabic') : __('English')) }}
            </span>
            @if ($course->certificate_available)
                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 bg-teal-100 text-teal-800">{{ __('Certificate available') }}</span>
            @endif
        </div>

        <div class="mt-8 grid grid-cols-1 sm:grid-cols-3 gap-8">
            <div class="sm:col-span-2">
                <h2 class="font-semibold text-gray-900">{{ __('About this course') }}</h2>
                <p class="mt-2 text-gray-700 whitespace-pre-line">{{ $course->full_description }}</p>
            </div>

            <div>
                <h2 class="font-semibold text-gray-900">{{ __('Lecturer') }}</h2>
                @foreach ($course->lecturers as $lecturer)
                    <a href="{{ route('lecturers.show', $lecturer) }}" wire:navigate class="mt-2 flex items-center gap-3 hover:opacity-80">
                        @if ($lecturer->lecturerProfile?->photo_path)
                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($lecturer->lecturerProfile->photo_path) }}" alt="{{ $lecturer->name }}" class="h-10 w-10 rounded-full object-cover">
                        @else
                            <div class="h-10 w-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 font-medium">
                                {{ mb_substr($lecturer->name, 0, 1) }}
                            </div>
                        @endif
                        <div>
                            <div class="text-sm font-medium text-gray-900">{{ $lecturer->name }}</div>
                            <div class="text-xs text-gray-500">{{ $lecturer->lecturerProfile?->headline }}</div>
                        </div>
                    </a>
                @endforeach

                <div class="mt-6 border border-gray-200 rounded-lg p-4">
                    <h3 class="text-sm font-semibold text-gray-900">{{ __('Enrollment') }}</h3>

                    @auth
                        @if ($course->isAccessibleBy(auth()->user()))
                            <p class="mt-1 text-sm text-gray-600">{{ __('You already have access to this course.') }}</p>
                            <a href="{{ route('my-courses.classroom', $course) }}" wire:navigate class="mt-3 inline-block rounded-md px-4 py-2 text-sm font-medium text-white bg-gray-900 hover:bg-gray-700">
                                {{ __('Go to Classroom') }}
                            </a>
                        @else
                            <p class="mt-1 text-sm text-gray-600">{{ __('Subscribe to get full access to this course.') }}</p>
                            <a href="{{ route('checkout.show', $course) }}" wire:navigate class="mt-3 inline-block rounded-md px-4 py-2 text-sm font-medium text-white bg-gray-900 hover:bg-gray-700">
                                {{ __('Subscribe Now') }}
                            </a>
                        @endif
                    @else
                        <p class="mt-1 text-sm text-gray-600">
                            <a href="{{ route('login') }}" wire:navigate class="underline">{{ __('Log in') }}</a>
                            {{ __('or') }}
                            <a href="{{ route('register') }}" wire:navigate class="underline">{{ __('create an account') }}</a>
                            {{ __('to subscribe.') }}
                        </p>
                    @endauth
                </div>
            </div>
        </div>
    </div>
</x-public-layout>
