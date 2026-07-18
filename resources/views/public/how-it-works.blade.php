<x-public-layout :title="$title" :description="__('See how students learn on EduConnect Academy, from registration to certification.')">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <h1 class="text-3xl font-bold text-gray-900">{{ __('How It Works') }}</h1>

        <ol class="mt-8 space-y-8">
            <li class="flex gap-4">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-gray-900 text-white text-sm font-semibold">1</span>
                <div>
                    <h3 class="font-semibold text-gray-900">{{ __('Create your account') }}</h3>
                    <p class="mt-1 text-sm text-gray-600">{{ __('Register with your name, email, and mobile number, and verify your email address.') }}</p>
                </div>
            </li>
            <li class="flex gap-4">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-gray-900 text-white text-sm font-semibold">2</span>
                <div>
                    <h3 class="font-semibold text-gray-900">{{ __('Find a course') }}</h3>
                    <p class="mt-1 text-sm text-gray-600">{{ __('Browse the course catalogue and choose the subject, lecturer, and delivery format that fits you.') }}</p>
                </div>
            </li>
            <li class="flex gap-4">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-gray-900 text-white text-sm font-semibold">3</span>
                <div>
                    <h3 class="font-semibold text-gray-900">{{ __('Subscribe and start learning') }}</h3>
                    <p class="mt-1 text-sm text-gray-600">{{ __('Pay a simple monthly subscription, join live classes, and watch recorded lessons at your own pace.') }}</p>
                </div>
            </li>
            <li class="flex gap-4">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-gray-900 text-white text-sm font-semibold">4</span>
                <div>
                    <h3 class="font-semibold text-gray-900">{{ __('Track your progress and get certified') }}</h3>
                    <p class="mt-1 text-sm text-gray-600">{{ __('Complete assignments and assessments, follow your progress, and earn a verifiable certificate on completion.') }}</p>
                </div>
            </li>
        </ol>
    </div>
</x-public-layout>
