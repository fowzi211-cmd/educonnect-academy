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
                            <option value="paid">{{ __('Paid') }}</option>
                            <option value="refunded">{{ __('Refunded') }}</option>
                            <option value="void">{{ __('Void') }}</option>
                        </select>
                    </div>
                </div>

                <table class="min-w-full divide-y divide-gray-200 text-lg">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Invoice') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Student') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Course') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Total') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Status') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Issued') }}</th>
                            <th class="px-4 py-2"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($invoices as $invoice)
                            <tr wire:key="inv-{{ $invoice->id }}">
                                <td class="px-4 py-3 text-gray-800">{{ $invoice->invoice_number }}</td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-900">{{ $invoice->user->name }}</div>
                                    <div class="text-gray-800">{{ $invoice->user->email }}</div>
                                </td>
                                <td class="px-4 py-3 text-gray-800">{{ $invoice->transaction?->course?->title ?? '—' }}</td>
                                <td class="px-4 py-3 text-gray-800">{{ number_format($invoice->total, 2) }} {{ $invoice->currency }}</td>
                                <td class="px-4 py-3">
                                    <span @class([
                                        'inline-flex items-center rounded-full px-2.5 py-0.5 text-sm font-medium',
                                        'bg-green-100 text-green-800' => $invoice->status === 'paid',
                                        'bg-red-100 text-red-800' => $invoice->status === 'refunded',
                                        'bg-gray-100 text-gray-800' => $invoice->status === 'void',
                                    ])>
                                        {{ match ($invoice->status) {
                                            'paid' => __('Paid'),
                                            'refunded' => __('Refunded'),
                                            'void' => __('Void'),
                                            default => ucfirst($invoice->status),
                                        } }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-800">{{ $invoice->issued_at->format('Y-m-d') }}</td>
                                <td class="px-4 py-3 text-end">
                                    <a href="{{ route('invoices.show', $invoice) }}" target="_blank" class="text-indigo-600 hover:text-indigo-800 underline">{{ __('View') }}</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-6 text-center text-gray-800">{{ __('No invoices yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div>
                {{ $invoices->links() }}
            </div>
        </div>
    </div>
</div>
