<x-public-layout :description="__('Dr. Nada Center connects students with lecturers through live online classes and recorded video courses, in Arabic and English.')">
    <!-- Hero -->
    <section class="relative overflow-hidden bg-brand-gradient">
        <div class="pointer-events-none absolute inset-0" style="background-image: radial-gradient(circle at 15% 20%, rgba(255,255,255,0.16), transparent 40%), radial-gradient(circle at 85% 75%, rgba(255,255,255,0.14), transparent 45%);"></div>
        <div class="pointer-events-none absolute -top-24 -end-24 h-96 w-96 rounded-full bg-white/10 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-24 -start-24 h-96 w-96 rounded-full bg-black/10 blur-3xl"></div>

        <div class="relative max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-24 sm:py-28 text-center">
            @if ($heroLogo = \App\Models\Setting::get('branding.logo_path'))
                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($heroLogo) }}" alt="{{ \App\Models\Setting::get('branding.platform_name', config('app.name')) }}" class="mx-auto h-[72px] w-auto object-contain drop-shadow-xl">
            @endif

            <span class="inline-flex items-center gap-1.5 rounded-full bg-white/15 px-3 py-1 text-xs font-semibold text-white ring-1 ring-white/25 mt-6">
                {{ __('Bilingual · Arabic & English') }}
            </span>

            <h1 class="mt-6 text-4xl sm:text-6xl font-extrabold text-white tracking-tight leading-tight">
                {{ __('Learn live. Learn on your schedule.') }}
            </h1>
            <p class="mt-5 max-w-2xl mx-auto text-lg text-white/85">
                {{ __('Dr. Nada Center connects students with qualified lecturers through scheduled live lessons and recorded video courses — subscribe monthly and learn at your own pace.') }}
            </p>
            <div class="mt-10 flex flex-wrap justify-center gap-3">
                <a href="{{ route('register') }}" wire:navigate class="rounded-lg px-6 py-3 text-sm font-semibold text-gray-900 bg-white hover:bg-gray-100 shadow-lg shadow-black/10 transition">
                    {{ __('Get Started Free') }}
                </a>
                <a href="{{ route('courses.index') }}" wire:navigate class="rounded-lg px-6 py-3 text-sm font-semibold text-white ring-1 ring-white/40 hover:bg-white/10 transition">
                    {{ __('Browse Courses') }}
                </a>
            </div>
        </div>

        <!-- Stats strip -->
        <div class="relative border-t border-white/15 bg-black/10">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6 grid grid-cols-2 sm:grid-cols-4 gap-6 text-center">
                <div>
                    <div class="text-2xl sm:text-3xl font-extrabold text-white">{{ number_format($stats['courses']) }}+</div>
                    <div class="mt-1 text-xs sm:text-sm text-white/75">{{ __('Published Courses') }}</div>
                </div>
                <div>
                    <div class="text-2xl sm:text-3xl font-extrabold text-white">{{ number_format($stats['lecturers']) }}+</div>
                    <div class="mt-1 text-xs sm:text-sm text-white/75">{{ __('Expert Lecturers') }}</div>
                </div>
                <div>
                    <div class="text-2xl sm:text-3xl font-extrabold text-white">{{ number_format($stats['students']) }}+</div>
                    <div class="mt-1 text-xs sm:text-sm text-white/75">{{ __('Active Students') }}</div>
                </div>
                <div>
                    <div class="text-2xl sm:text-3xl font-extrabold text-white">{{ number_format($stats['categories']) }}+</div>
                    <div class="mt-1 text-xs sm:text-sm text-white/75">{{ __('Subject Categories') }}</div>
                </div>
            </div>
        </div>
    </section>

    @include('partials.news-carousel')

    <!-- Value proposition -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
        <div class="text-center max-w-2xl mx-auto">
            <h2 class="text-3xl font-bold text-gray-900">{{ __('Everything you need to learn well') }}</h2>
            <p class="mt-3 text-gray-600">{{ __('One platform for live classes, recorded lessons, assessments, and certificates.') }}</p>
        </div>

        <div class="mt-14 grid grid-cols-1 sm:grid-cols-3 gap-8">
            <div class="group relative rounded-2xl border border-gray-100 p-6 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-brand-gradient text-white shadow-sm">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                    </svg>
                </div>
                <h3 class="mt-5 font-semibold text-gray-900 text-lg">{{ __('Live Online Lessons') }}</h3>
                <p class="mt-2 text-sm text-gray-600">{{ __('Join scheduled classes with your lecturer in real time, with times shown in your own time zone.') }}</p>
            </div>

            <div class="group relative rounded-2xl border border-gray-100 p-6 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-brand-gradient text-white shadow-sm">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 8.5l6 3.5-6 3.5v-7z" />
                    </svg>
                </div>
                <h3 class="mt-5 font-semibold text-gray-900 text-lg">{{ __('Recorded Video Lessons') }}</h3>
                <p class="mt-2 text-sm text-gray-600">{{ __('Rewatch lessons anytime, resume where you left off, and track your progress through every course.') }}</p>
            </div>

            <div class="group relative rounded-2xl border border-gray-100 p-6 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-brand-gradient text-white shadow-sm">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 5h12M3 12h12M3 19h6m6-13l3 3-3 3m3-3H15" />
                    </svg>
                </div>
                <h3 class="mt-5 font-semibold text-gray-900 text-lg">{{ __('Bilingual by Design') }}</h3>
                <p class="mt-2 text-sm text-gray-600">{{ __('Use the platform fully in Arabic or English, with right-to-left support built in from the ground up.') }}</p>
            </div>
        </div>
    </section>

    <!-- Featured courses -->
    @if ($featuredCourses->isNotEmpty())
        <section class="bg-gray-50 border-y border-gray-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
                <div class="flex items-end justify-between flex-wrap gap-4">
                    <div>
                        <h2 class="text-3xl font-bold text-gray-900">{{ __('Featured Courses') }}</h2>
                        <p class="mt-2 text-gray-600">{{ __('A sample of what you can learn right now.') }}</p>
                    </div>
                    <a href="{{ route('courses.index') }}" wire:navigate class="text-sm font-semibold text-brand-primary hover:underline">
                        {{ __('View all courses') }} &rarr;
                    </a>
                </div>

                <div class="mt-10 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($featuredCourses as $course)
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
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <!-- CTA -->
    <section class="relative overflow-hidden bg-gray-900">
        <div class="pointer-events-none absolute inset-0 bg-brand-gradient opacity-20"></div>
        <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-20 text-center">
            <h2 class="text-3xl sm:text-4xl font-bold text-white">{{ __('Ready to start learning?') }}</h2>
            <p class="mt-3 text-gray-300">{{ __('Create your free account in a minute.') }}</p>
            <div class="mt-8">
                <a href="{{ route('register') }}" wire:navigate class="rounded-lg px-6 py-3 text-sm font-semibold text-gray-900 bg-white hover:bg-gray-100 shadow-lg transition">
                    {{ __('Register Now') }}
                </a>
            </div>
        </div>
    </section>
</x-public-layout>
