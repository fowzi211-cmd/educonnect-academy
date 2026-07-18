<x-public-layout :description="__('EduConnect Academy connects students with lecturers through live online classes and recorded video courses, in Arabic and English.')">
    <!-- Hero -->
    <section class="bg-gray-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 text-center">
            <h1 class="text-4xl sm:text-5xl font-bold text-white tracking-tight">
                {{ __('Learn live. Learn on your schedule.') }}
            </h1>
            <p class="mt-4 max-w-2xl mx-auto text-lg text-gray-300">
                {{ __('EduConnect Academy connects students with qualified lecturers through scheduled live lessons and recorded video courses — subscribe monthly and learn at your own pace.') }}
            </p>
            <div class="mt-8 flex justify-center gap-4">
                <a href="{{ route('register') }}" wire:navigate class="rounded-md px-5 py-3 text-sm font-semibold text-gray-900 bg-white hover:bg-gray-100">
                    {{ __('Get Started') }}
                </a>
                <a href="{{ route('how-it-works') }}" wire:navigate class="rounded-md px-5 py-3 text-sm font-semibold text-white ring-1 ring-white/40 hover:bg-white/10">
                    {{ __('How It Works') }}
                </a>
            </div>
        </div>
    </section>

    <!-- Value proposition -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-10">
            <div>
                <h3 class="font-semibold text-gray-900">{{ __('Live Online Lessons') }}</h3>
                <p class="mt-2 text-sm text-gray-600">{{ __('Join scheduled classes with your lecturer in real time, with times shown in your own time zone.') }}</p>
            </div>
            <div>
                <h3 class="font-semibold text-gray-900">{{ __('Recorded Video Lessons') }}</h3>
                <p class="mt-2 text-sm text-gray-600">{{ __('Rewatch lessons anytime, resume where you left off, and track your progress through every course.') }}</p>
            </div>
            <div>
                <h3 class="font-semibold text-gray-900">{{ __('Bilingual by Design') }}</h3>
                <p class="mt-2 text-sm text-gray-600">{{ __('Use the platform fully in Arabic or English, with right-to-left support built in from the ground up.') }}</p>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="bg-gray-50 border-t border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 text-center">
            <h2 class="text-2xl font-bold text-gray-900">{{ __('Ready to start learning?') }}</h2>
            <p class="mt-2 text-gray-600">{{ __('Create your free account in a minute.') }}</p>
            <div class="mt-6">
                <a href="{{ route('register') }}" wire:navigate class="rounded-md px-5 py-3 text-sm font-semibold text-white bg-gray-900 hover:bg-gray-700">
                    {{ __('Register Now') }}
                </a>
            </div>
        </div>
    </section>
</x-public-layout>
