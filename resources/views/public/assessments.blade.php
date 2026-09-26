<x-public-layout :title="$title" :description="__('See how Dr. Nada Center measures your progress — quizzes, exams, assignments, and the question types behind them.')">
    <!-- Hero -->
    <section class="relative overflow-hidden bg-brand-gradient">
        <div class="pointer-events-none absolute inset-0" style="background-image: radial-gradient(circle at 15% 20%, rgba(255,255,255,0.16), transparent 40%), radial-gradient(circle at 85% 75%, rgba(255,255,255,0.14), transparent 45%);"></div>

        <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-20 text-center">
            <h1 class="text-4xl sm:text-5xl font-extrabold text-white tracking-tight leading-tight">
                {{ __('Assessments that actually measure learning') }}
            </h1>
            <p class="mt-5 max-w-2xl mx-auto text-lg text-white/85">
                {{ __('From quick quizzes to full exams and hands-on assignments, lecturers on Dr. Nada Center can build assessments that fit the subject — with real deadlines, real grading, and a certificate at the end.') }}
            </p>
        </div>
    </section>

    <!-- Assessment methods -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
        <div class="text-center max-w-2xl mx-auto">
            <h2 class="text-3xl font-bold text-gray-900">{{ __('Ways your progress gets assessed') }}</h2>
            <p class="mt-3 text-gray-600">{{ __('A lecturer picks the right method for what they\'re teaching.') }}</p>
        </div>

        <div class="mt-14 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="group relative rounded-2xl border border-gray-100 p-6 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-brand-gradient text-white shadow-sm">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="mt-5 font-semibold text-gray-900 text-lg">{{ __('Quizzes') }}</h3>
                <p class="mt-2 text-sm text-gray-600">{{ __('Short, low-stakes checks after a lesson — instant feedback, multiple attempts, no pressure.') }}</p>
            </div>

            <div class="group relative rounded-2xl border border-gray-100 p-6 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-brand-gradient text-white shadow-sm">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15.75h.008v.008H12v-.008z" />
                    </svg>
                </div>
                <h3 class="mt-5 font-semibold text-gray-900 text-lg">{{ __('Exams') }}</h3>
                <p class="mt-2 text-sm text-gray-600">{{ __('Timed, proctored-by-policy assessments with a pass mark and a fixed open/close window.') }}</p>
            </div>

            <div class="group relative rounded-2xl border border-gray-100 p-6 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-brand-gradient text-white shadow-sm">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 4.5v15m6-15v15M4.5 9h15M4.5 15h15" />
                    </svg>
                </div>
                <h3 class="mt-5 font-semibold text-gray-900 text-lg">{{ __('Practice Tests & Surveys') }}</h3>
                <p class="mt-2 text-sm text-gray-600">{{ __('Ungraded practice runs to build confidence, and surveys to gather feedback — no score attached.') }}</p>
            </div>

            <div class="group relative rounded-2xl border border-gray-100 p-6 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-brand-gradient text-white shadow-sm">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                    </svg>
                </div>
                <h3 class="mt-5 font-semibold text-gray-900 text-lg">{{ __('Assignments') }}</h3>
                <p class="mt-2 text-sm text-gray-600">{{ __('Upload-based work with its own deadline and late policy, graded and returned with written feedback.') }}</p>
            </div>
        </div>
    </section>

    <!-- Question types -->
    <section class="bg-gray-50 border-y border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
            <div class="text-center max-w-2xl mx-auto">
                <h2 class="text-3xl font-bold text-gray-900">{{ __('Question types quizzes and exams can use') }}</h2>
                <p class="mt-3 text-gray-600">{{ __('Lecturers mix and match these to fit the subject, and most are graded automatically the moment you submit.') }}</p>
            </div>

            <div class="mt-14 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                @foreach ([
                    ['title' => __('Multiple Choice'), 'body' => __('Pick one correct answer from a list of options.')],
                    ['title' => __('Multi-Select'), 'body' => __('Pick every option that applies — partial credit where the lecturer allows it.')],
                    ['title' => __('True / False'), 'body' => __('A quick binary check of a single statement.')],
                    ['title' => __('Matching'), 'body' => __('Pair items from one column with the correct item in another.')],
                    ['title' => __('Short Answer'), 'body' => __('Type a brief text answer, checked against accepted responses.')],
                    ['title' => __('Numerical'), 'body' => __('Enter a number, graded within a tolerance the lecturer sets.')],
                    ['title' => __('Essay'), 'body' => __('Longer written responses, read and scored by the lecturer.')],
                    ['title' => __('File Upload'), 'body' => __('Submit a document, image, or recording as your answer.')],
                ] as $type)
                    <div class="bg-white rounded-xl border border-gray-100 p-5 shadow-sm">
                        <h3 class="font-semibold text-gray-900">{{ $type['title'] }}</h3>
                        <p class="mt-1.5 text-sm text-gray-600">{{ $type['body'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- How grading & results work -->
    <section class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-8">
            <div>
                <h3 class="font-semibold text-gray-900 text-lg">{{ __('Multiple attempts, if allowed') }}</h3>
                <p class="mt-2 text-sm text-gray-600">{{ __('Lecturers can cap attempts or leave a quiz open for unlimited retakes — you always see how many you have left.') }}</p>
            </div>
            <div>
                <h3 class="font-semibold text-gray-900 text-lg">{{ __('Clear pass marks') }}</h3>
                <p class="mt-2 text-sm text-gray-600">{{ __('Every graded quiz and exam can carry a pass mark, so you know exactly where you stand.') }}</p>
            </div>
            <div>
                <h3 class="font-semibold text-gray-900 text-lg">{{ __('Certificates on completion') }}</h3>
                <p class="mt-2 text-sm text-gray-600">{{ __('Finish a course\'s assessments and you can earn a certificate — verifiable online by its certificate number.') }}</p>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="relative overflow-hidden bg-gray-900">
        <div class="pointer-events-none absolute inset-0 bg-brand-gradient opacity-20"></div>
        <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-20 text-center">
            <h2 class="text-3xl sm:text-4xl font-bold text-white">{{ __('See it from the inside') }}</h2>
            <p class="mt-3 text-gray-300">{{ __('Register free and try a quiz in one of our courses.') }}</p>
            <div class="mt-8">
                <a href="{{ route('register') }}" wire:navigate class="rounded-lg px-6 py-3 text-sm font-semibold text-gray-900 bg-white hover:bg-gray-100 shadow-lg transition">
                    {{ __('Register Now') }}
                </a>
            </div>
        </div>
    </section>
</x-public-layout>
