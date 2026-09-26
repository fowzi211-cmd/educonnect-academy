<x-public-layout :title="__('Verify Certificate')" :description="__('Verify the authenticity of a Dr. Nada Center certificate.')">
    <div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <h1 class="text-2xl font-bold text-gray-900 text-center">{{ __('Certificate Verification') }}</h1>

        <form method="GET" action="{{ route('certificates.verify') }}" class="mt-8">
            <label for="certificate_number" class="text-sm font-medium text-gray-700">{{ __('Certificate Number') }}</label>
            <div class="mt-1 flex gap-2">
                <input type="text" id="certificate_number" name="number" value="{{ $certificateNumber }}"
                    placeholder="CERT-2026-000123"
                    class="flex-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                <button type="submit" class="rounded-md px-4 py-2 text-sm font-medium text-white bg-gray-900 hover:bg-gray-700">
                    {{ __('Verify') }}
                </button>
            </div>
        </form>

        <div class="mt-8">
            @if ($certificate && $certificate->status === 'active')
                <div class="bg-green-50 border border-green-200 rounded-lg p-6 text-center">
                    <div class="text-green-700 font-semibold">{{ __('Valid Certificate') }}</div>
                    <dl class="mt-4 text-sm text-start space-y-2">
                        <div class="flex justify-between"><dt class="text-gray-600">{{ __('Student') }}</dt><dd class="text-gray-900">{{ $certificate->student_name_snapshot }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-600">{{ __('Course') }}</dt><dd class="text-gray-900">{{ $certificate->course_title_snapshot }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-600">{{ __('Completion Date') }}</dt><dd class="text-gray-900">{{ $certificate->completion_date->format('Y-m-d') }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-600">{{ __('Certificate No.') }}</dt><dd class="text-gray-900">{{ $certificate->certificate_number }}</dd></div>
                    </dl>
                </div>
            @elseif ($certificate && $certificate->status === 'revoked')
                <div class="bg-red-50 border border-red-200 rounded-lg p-6 text-center">
                    <div class="text-red-700 font-semibold">{{ __('This certificate has been revoked') }}</div>
                    <p class="mt-2 text-sm text-gray-600">{{ __('Certificate No.') }} {{ $certificate->certificate_number }}</p>
                </div>
            @elseif ($certificateNumber)
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 text-center text-gray-600">
                    {{ __('No certificate found with this number.') }}
                </div>
            @endif
        </div>
    </div>
</x-public-layout>
