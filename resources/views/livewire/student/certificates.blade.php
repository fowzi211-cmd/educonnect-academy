<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My Certificates') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 text-lg">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Course') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Certificate No.') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Issued') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Status') }}</th>
                            <th class="px-4 py-2"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($certificates as $certificate)
                            <tr>
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $certificate->course_title_snapshot }}</td>
                                <td class="px-4 py-3 text-gray-800">{{ $certificate->certificate_number }}</td>
                                <td class="px-4 py-3 text-gray-800">{{ $certificate->issued_at->format('Y-m-d') }}</td>
                                <td class="px-4 py-3">
                                    <span @class([
                                        'inline-flex items-center rounded-full px-2.5 py-0.5 text-sm font-medium',
                                        'bg-green-100 text-green-800' => $certificate->status === 'active',
                                        'bg-red-100 text-red-800' => $certificate->status === 'revoked',
                                    ])>
                                        {{ $certificate->status === 'active' ? __('Active') : __('Revoked') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-end">
                                    <a href="{{ route('certificates.show', $certificate) }}" target="_blank" class="text-indigo-600 hover:text-indigo-800 underline">{{ __('View') }}</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-gray-800">{{ __('You have not earned any certificates yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
