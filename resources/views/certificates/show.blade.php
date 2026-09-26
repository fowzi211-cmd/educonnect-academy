<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Certificate') }} {{ $certificate->certificate_number }}
        </h2>
    </x-slot>

    <div class="py-12 print:py-0">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 print:px-0 print:max-w-full space-y-4">
            @if ($certificate->status === 'revoked')
                <div class="bg-red-50 border border-red-200 text-red-800 text-sm rounded-lg p-3 print:hidden">
                    {{ __('This certificate has been revoked.') }}
                    @if ($certificate->revoked_reason)
                        {{ __('Reason') }}: {{ $certificate->revoked_reason }}
                    @endif
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg p-10 print:shadow-none border-4 border-double border-indigo-200 text-center">
                <div class="text-sm uppercase tracking-widest text-gray-500">
                    {{ \App\Models\Setting::get('branding.platform_name', config('app.name')) }}
                </div>

                <h1 class="mt-6 text-2xl font-bold text-gray-900">{{ __('Certificate of Completion') }}</h1>

                <p class="mt-6 text-gray-600">{{ __('This certifies that') }}</p>
                <p class="mt-2 text-3xl font-semibold text-gray-900">{{ $certificate->student_name_snapshot }}</p>

                <p class="mt-6 text-gray-600">{{ __('has successfully completed') }}</p>
                <p class="mt-2 text-xl font-medium text-gray-900">{{ $certificate->course_title_snapshot }}</p>

                @if ($certificate->course_duration_snapshot)
                    <p class="mt-1 text-sm text-gray-600">{{ $certificate->course_duration_snapshot }}</p>
                @endif

                @if ($certificate->template_text)
                    <p class="mt-6 text-sm text-gray-600 max-w-xl mx-auto">{{ $certificate->template_text }}</p>
                @endif

                <div class="mt-10 grid grid-cols-3 items-end gap-4 text-sm">
                    <div class="text-start">
                        <div class="text-gray-900">{{ $certificate->completion_date->format('Y-m-d') }}</div>
                        <div class="text-xs text-gray-500 border-t border-gray-200 mt-1 pt-1">{{ __('Completion Date') }}</div>
                    </div>
                    <div>
                        <div class="inline-block bg-white p-1">{!! $qrCodeSvg !!}</div>
                        <div class="text-xs text-gray-500 mt-1">{{ __('Scan to verify') }}</div>
                    </div>
                    <div class="text-end">
                        @if ($certificate->lecturer_name_snapshot)
                            <div class="text-gray-900 italic">{{ $certificate->lecturer_name_snapshot }}</div>
                        @endif
                        <div class="text-xs text-gray-500 border-t border-gray-200 mt-1 pt-1">{{ __('Lecturer') }}</div>
                    </div>
                </div>

                <div class="mt-6 text-xs text-gray-500">
                    {{ __('Certificate No.') }} {{ $certificate->certificate_number }} &middot; {{ $verificationUrl }}
                </div>
            </div>

            <div class="print:hidden">
                <button onclick="window.print()" class="rounded-md px-4 py-2 text-sm font-medium text-gray-700 ring-1 ring-gray-300 hover:bg-gray-50">
                    {{ __('Print / Save as PDF') }}
                </button>
            </div>
        </div>
    </div>
</x-app-layout>
