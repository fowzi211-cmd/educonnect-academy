<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Certificates') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white shadow-sm sm:rounded-lg p-4">
                <x-text-input wire:model.live.debounce.400ms="search" placeholder="{{ __('Search by certificate number or student email') }}" class="w-full" />
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 text-lg">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Certificate No.') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Student') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Course') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Issued') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Status') }}</th>
                            <th class="px-4 py-2"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($certificates as $certificate)
                            <tr wire:key="cert-{{ $certificate->id }}">
                                <td class="px-4 py-3 text-gray-800">
                                    {{ $certificate->certificate_number }}
                                    @if ($certificate->reissue_of_certificate_id)
                                        <span class="text-sm text-gray-600">({{ __('reissued') }})</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-900">{{ $certificate->user->name }}</div>
                                    <div class="text-gray-800">{{ $certificate->user->email }}</div>
                                </td>
                                <td class="px-4 py-3 text-gray-800">{{ $certificate->course_title_snapshot }}</td>
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
                                <td class="px-4 py-3 text-end space-x-2 rtl:space-x-reverse">
                                    <a href="{{ route('certificates.show', $certificate) }}" target="_blank" class="text-indigo-600 hover:text-indigo-800 underline">{{ __('View') }}</a>
                                    @if ($certificate->status === 'active')
                                        <button wire:click="startRevoke({{ $certificate->id }})" class="text-red-600 hover:text-red-800 underline">{{ __('Revoke') }}</button>
                                    @else
                                        <button wire:click="reissue({{ $certificate->id }})" wire:confirm="{{ __('Issue a new active certificate for this student?') }}" class="text-indigo-600 hover:text-indigo-800 underline">{{ __('Reissue') }}</button>
                                    @endif
                                </td>
                            </tr>

                            @if ($revokingId === $certificate->id)
                                <tr>
                                    <td colspan="6" class="px-4 py-3 bg-red-50">
                                        <x-input-label for="revokeReason" :value="__('Reason')" />
                                        <textarea wire:model="revokeReason" id="revokeReason" rows="2"
                                            class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full"></textarea>
                                        <x-input-error :messages="$errors->get('revokeReason')" class="mt-2" />
                                        <div class="mt-2 flex gap-2">
                                            <x-danger-button wire:click="confirmRevoke">{{ __('Confirm Revocation') }}</x-danger-button>
                                            <x-secondary-button wire:click="cancelRevoke">{{ __('Never mind') }}</x-secondary-button>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-6 text-center text-gray-800">{{ __('No certificates issued yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div>
                {{ $certificates->links() }}
            </div>
        </div>
    </div>
</div>
