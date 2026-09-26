<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Finance') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @include('livewire.admin.finance._nav')

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <div class="flex flex-wrap gap-3 items-end mb-4">
                    <div>
                        <x-input-label for="studentEmail" :value="__('Student email')" />
                        <x-text-input wire:model.live.debounce.400ms="studentEmail" id="studentEmail" class="mt-1" placeholder="{{ __('Search by email') }}" />
                    </div>
                    <div>
                        <x-input-label for="status" :value="__('Status')" />
                        <select wire:model.live="status" id="status" class="mt-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="">{{ __('All') }}</option>
                            <option value="pending">{{ __('Pending') }}</option>
                            <option value="succeeded">{{ __('Succeeded') }}</option>
                            <option value="failed">{{ __('Failed') }}</option>
                        </select>
                    </div>
                </div>

                <table class="min-w-full divide-y divide-gray-200 text-lg">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Student') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Course') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Amount') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Refunded') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Status') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Date') }}</th>
                            <th class="px-4 py-2"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($transactions as $transaction)
                            <tr wire:key="txn-{{ $transaction->id }}">
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-900">{{ $transaction->user->name }}</div>
                                    <div class="text-gray-800">{{ $transaction->user->email }}</div>
                                </td>
                                <td class="px-4 py-3 text-gray-800">{{ $transaction->course->title }}</td>
                                <td class="px-4 py-3 text-gray-800">{{ number_format($transaction->amount, 2) }} {{ $transaction->currency }}</td>
                                <td class="px-4 py-3 text-gray-800">
                                    @if ($transaction->totalRefunded() > 0)
                                        {{ number_format($transaction->totalRefunded(), 2) }} {{ $transaction->currency }}
                                    @else
                                        &mdash;
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <span @class([
                                        'inline-flex items-center rounded-full px-2.5 py-0.5 text-sm font-medium',
                                        'bg-green-100 text-green-800' => $transaction->status === 'succeeded',
                                        'bg-yellow-100 text-yellow-800' => $transaction->status === 'pending',
                                        'bg-red-100 text-red-800' => $transaction->status === 'failed',
                                    ])>
                                        {{ match ($transaction->status) {
                                            'succeeded' => __('Succeeded'),
                                            'pending' => __('Pending'),
                                            'failed' => __('Failed'),
                                            default => ucfirst($transaction->status),
                                        } }}
                                    </span>
                                    @if ($transaction->gateway === 'bank_transfer')
                                        <span class="ms-1 inline-flex items-center rounded-full px-2.5 py-0.5 text-sm font-medium bg-indigo-100 text-indigo-800">
                                            {{ __('Bank Transfer') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-800">{{ $transaction->created_at->format('Y-m-d') }}</td>
                                <td class="px-4 py-3 text-end">
                                    @if ($transaction->status === 'succeeded' && $transaction->remainingRefundable() > 0)
                                        <button wire:click="startRefund({{ $transaction->id }})" class="text-red-600 hover:text-red-800 underline">{{ __('Refund') }}</button>
                                    @endif
                                    @if ($transaction->status === 'pending' && $transaction->gateway === 'bank_transfer' && $transaction->receipt_path)
                                        <button wire:click="approveBankTransfer({{ $transaction->id }})" wire:confirm="{{ __('Approve this payment and activate the subscription?') }}" class="text-green-700 hover:text-green-900 underline">{{ __('Approve') }}</button>
                                        <button wire:click="startReject({{ $transaction->id }})" class="text-red-600 hover:text-red-800 underline ms-3">{{ __('Reject') }}</button>
                                    @endif
                                    @if ($transaction->invoice)
                                        <a href="{{ route('invoices.show', $transaction->invoice) }}" target="_blank" class="text-indigo-600 hover:text-indigo-800 underline ms-3">{{ __('Invoice') }}</a>
                                    @endif
                                </td>
                            </tr>

                            @if ($transaction->gateway === 'bank_transfer' && $transaction->receipt_path && $rejectingId !== $transaction->id)
                                <tr>
                                    <td colspan="7" class="px-4 py-3 bg-indigo-50/60 text-sm text-gray-800">
                                        <div class="flex flex-wrap items-center gap-x-4 gap-y-1.5">
                                            <span>
                                                <span class="font-medium">{{ __('Reference') }}:</span>
                                                <span dir="ltr">{{ $transaction->receipt_reference_number ?? '—' }}</span>
                                            </span>
                                            <span>
                                                <span class="font-medium">{{ __('Bank account') }}:</span>
                                                {{ $transaction->bankAccount?->bank_name }}
                                            </span>
                                            <span>
                                                <span class="font-medium">{{ __('Bank Ref #') }}:</span>
                                                <span dir="ltr">{{ $transaction->bank_reference_number ?? '—' }}</span>
                                            </span>
                                            <span>
                                                <span class="font-medium">{{ __('Declared') }}:</span>
                                                {{ $transaction->declared_amount !== null ? number_format($transaction->declared_amount, 2).' '.$transaction->currency : '—' }}
                                                {{ __('vs Owed') }}: {{ number_format($transaction->amount, 2) }} {{ $transaction->currency }}
                                                @if ($transaction->hasAmountMismatch())
                                                    <span class="ms-1 inline-flex items-center rounded-full px-2 py-0.5 text-sm font-semibold bg-amber-100 text-amber-800">
                                                        {{ __('⚠ Amount mismatch') }}
                                                    </span>
                                                @endif
                                            </span>
                                            <a href="{{ route('transactions.receipt.download', $transaction) }}" target="_blank" class="text-indigo-600 hover:text-indigo-800 underline">
                                                {{ __('Download receipt') }}
                                            </a>
                                            @if ($transaction->reviewed_at)
                                                <span>{{ __('Reviewed by :name on :date', ['name' => $transaction->reviewedBy?->name, 'date' => $transaction->reviewed_at->format('Y-m-d')]) }}</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endif

                            @if ($rejectingId === $transaction->id)
                                <tr>
                                    <td colspan="7" class="px-4 py-4 bg-red-50">
                                        <x-input-label for="rejectReason" :value="__('Reason for rejection')" />
                                        <textarea wire:model="rejectReason" id="rejectReason" rows="2" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full"></textarea>
                                        <x-input-error :messages="$errors->get('rejectReason')" class="mt-1" />
                                        <div class="mt-3 flex gap-2">
                                            <x-danger-button wire:click="confirmRejectBankTransfer">{{ __('Reject Payment') }}</x-danger-button>
                                            <x-secondary-button wire:click="cancelReject">{{ __('Never mind') }}</x-secondary-button>
                                        </div>
                                    </td>
                                </tr>
                            @endif

                            @if ($refundingId === $transaction->id)
                                <tr>
                                    <td colspan="7" class="px-4 py-4 bg-red-50">
                                        <div class="grid sm:grid-cols-3 gap-3">
                                            <div>
                                                <x-input-label for="refundAmount" :value="__('Refund amount')" />
                                                <x-text-input wire:model="refundAmount" id="refundAmount" type="number" step="0.01" class="mt-1 block w-full" />
                                                <p class="text-sm text-gray-800 mt-1">{{ __('Remaining refundable: :amount', ['amount' => number_format($transaction->remainingRefundable(), 2).' '.$transaction->currency]) }}</p>
                                                <x-input-error :messages="$errors->get('refundAmount')" class="mt-1" />
                                            </div>
                                            <div class="sm:col-span-2">
                                                <x-input-label for="refundReason" :value="__('Reason')" />
                                                <textarea wire:model="refundReason" id="refundReason" rows="2" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full"></textarea>
                                                <x-input-error :messages="$errors->get('refundReason')" class="mt-1" />
                                            </div>
                                        </div>
                                        <label class="mt-3 flex items-center gap-2 text-lg text-gray-800">
                                            <input type="checkbox" wire:model="refundEndsAccess" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                            {{ __('End the student\'s course access immediately') }}
                                        </label>
                                        <div class="mt-3 flex gap-2">
                                            <x-danger-button wire:click="confirmRefund">{{ __('Process Refund') }}</x-danger-button>
                                            <x-secondary-button wire:click="cancelRefund">{{ __('Never mind') }}</x-secondary-button>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-6 text-center text-gray-800">{{ __('No transactions yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div>
                {{ $transactions->links() }}
            </div>
        </div>
    </div>
</div>
