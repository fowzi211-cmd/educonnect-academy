<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Finance') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @include('livewire.admin.finance._nav')

            <div class="bg-white shadow-sm sm:rounded-lg p-4">
                <x-input-label for="status" :value="__('Status')" />
                <select wire:model.live="status" id="status" class="mt-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    <option value="">{{ __('All') }}</option>
                    <option value="requested">{{ __('Requested') }}</option>
                    <option value="approved">{{ __('Approved') }}</option>
                    <option value="paid">{{ __('Paid') }}</option>
                    <option value="rejected">{{ __('Rejected') }}</option>
                </select>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 text-lg">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Lecturer') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Amount') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Requested') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Status') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Reference') }}</th>
                            <th class="px-4 py-2"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($requests as $request)
                            <tr wire:key="payout-{{ $request->id }}">
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-900">{{ $request->user->name }}</div>
                                    <div class="text-gray-800">{{ $request->user->email }}</div>
                                </td>
                                <td class="px-4 py-3 text-gray-800">{{ number_format($request->amount, 2) }} {{ $request->currency }}</td>
                                <td class="px-4 py-3 text-gray-800">{{ $request->requested_at->format('Y-m-d') }}</td>
                                <td class="px-4 py-3">
                                    <span @class([
                                        'inline-flex items-center rounded-full px-2.5 py-0.5 text-sm font-medium',
                                        'bg-yellow-100 text-yellow-800' => $request->status === 'requested',
                                        'bg-blue-100 text-blue-800' => $request->status === 'approved',
                                        'bg-green-100 text-green-800' => $request->status === 'paid',
                                        'bg-red-100 text-red-800' => $request->status === 'rejected',
                                    ])>
                                        {{ match ($request->status) {
                                            'requested' => __('Requested'),
                                            'approved' => __('Approved'),
                                            'paid' => __('Paid'),
                                            'rejected' => __('Rejected'),
                                            default => ucfirst($request->status),
                                        } }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-800">{{ $request->payout_reference ?: '—' }}</td>
                                <td class="px-4 py-3 text-end space-x-2 rtl:space-x-reverse">
                                    @if ($request->status === 'requested')
                                        <button wire:click="approve({{ $request->id }})" class="text-indigo-600 hover:text-indigo-800 underline">{{ __('Approve') }}</button>
                                        <button wire:click="startReject({{ $request->id }})" class="text-red-600 hover:text-red-800 underline">{{ __('Reject') }}</button>
                                    @elseif ($request->status === 'approved')
                                        <button wire:click="startPay({{ $request->id }})" class="text-indigo-600 hover:text-indigo-800 underline">{{ __('Mark Paid') }}</button>
                                        <button wire:click="startReject({{ $request->id }})" class="text-red-600 hover:text-red-800 underline">{{ __('Reject') }}</button>
                                    @endif
                                </td>
                            </tr>

                            @if ($rejectingId === $request->id)
                                <tr>
                                    <td colspan="6" class="px-4 py-3 bg-red-50">
                                        <x-input-label for="rejectReason" :value="__('Reason')" />
                                        <textarea wire:model="rejectReason" id="rejectReason" rows="2"
                                            class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full"></textarea>
                                        <x-input-error :messages="$errors->get('rejectReason')" class="mt-2" />
                                        <div class="mt-2 flex gap-2">
                                            <x-danger-button wire:click="confirmReject">{{ __('Confirm Rejection') }}</x-danger-button>
                                            <x-secondary-button wire:click="cancelReject">{{ __('Never mind') }}</x-secondary-button>
                                        </div>
                                    </td>
                                </tr>
                            @endif

                            @if ($payingId === $request->id)
                                <tr>
                                    <td colspan="6" class="px-4 py-3 bg-indigo-50">
                                        <x-input-label for="payoutReference" :value="__('Payment reference')" />
                                        <x-text-input wire:model="payoutReference" id="payoutReference" class="mt-1 block w-full" placeholder="{{ __('Bank transfer ID, cheque number, etc.') }}" />
                                        <x-input-error :messages="$errors->get('payoutReference')" class="mt-2" />
                                        <div class="mt-2 flex gap-2">
                                            <x-primary-button wire:click="confirmPay">{{ __('Confirm Payment') }}</x-primary-button>
                                            <x-secondary-button wire:click="cancelPay">{{ __('Never mind') }}</x-secondary-button>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-6 text-center text-gray-800">{{ __('No payout requests yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div>
                {{ $requests->links() }}
            </div>
        </div>
    </div>
</div>
