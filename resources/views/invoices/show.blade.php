<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Invoice') }} {{ $invoice->invoice_number }}
        </h2>
    </x-slot>

    <div class="py-12 print:py-0">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 print:px-0 print:max-w-full">
            <div class="bg-white shadow-sm sm:rounded-lg p-8 print:shadow-none">
                <div class="flex justify-between items-start">
                    <div>
                        <div class="font-bold text-lg text-gray-900">
                            {{ \App\Models\Setting::get('branding.platform_name', config('app.name')) }}
                        </div>
                        <div class="text-sm text-gray-600">
                            {{ \App\Models\Setting::get('branding.contact_email') }}
                        </div>
                    </div>
                    <div class="text-end">
                        <div class="font-semibold text-gray-900">{{ __('Invoice') }}</div>
                        <div class="text-sm text-gray-600">{{ $invoice->invoice_number }}</div>
                        <div class="text-sm text-gray-600">{{ $invoice->issued_at->format('Y-m-d') }}</div>
                    </div>
                </div>

                <div class="mt-8 flex justify-between">
                    <div>
                        <div class="text-xs uppercase text-gray-600">{{ __('Billed to') }}</div>
                        <div class="text-sm text-gray-900">{{ $invoice->user->name }}</div>
                        <div class="text-sm text-gray-600">{{ $invoice->user->email }}</div>
                    </div>
                    <div class="text-end">
                        <div class="text-xs uppercase text-gray-600">{{ __('Status') }}</div>
                        <div class="text-sm text-gray-900">{{ match ($invoice->status) {
                            'paid' => __('Paid'),
                            'refunded' => __('Refunded'),
                            'void' => __('Void'),
                            default => ucfirst($invoice->status),
                        } }}</div>
                    </div>
                </div>

                <table class="mt-8 w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 text-start text-gray-600">
                            <th class="py-2 text-start font-medium">{{ __('Description') }}</th>
                            <th class="py-2 text-end font-medium">{{ __('Amount') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($invoice->items as $item)
                            <tr class="border-b border-gray-100">
                                <td class="py-2 text-gray-700">{{ $item->description }}</td>
                                <td class="py-2 text-end text-gray-900">{{ number_format($item->amount, 2) }} {{ $invoice->currency }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="mt-4 flex justify-end">
                    <div class="w-56 space-y-1 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">{{ __('Subtotal') }}</span>
                            <span class="text-gray-900">{{ number_format($invoice->subtotal, 2) }} {{ $invoice->currency }}</span>
                        </div>
                        @if ($invoice->discount_amount > 0)
                            <div class="flex justify-between text-green-700">
                                <span>{{ __('Discount') }}</span>
                                <span>&minus;{{ number_format($invoice->discount_amount, 2) }} {{ $invoice->currency }}</span>
                            </div>
                        @endif
                        @if ($invoice->tax_amount > 0)
                            <div class="flex justify-between">
                                <span class="text-gray-600">{{ __('Tax') }}</span>
                                <span class="text-gray-900">{{ number_format($invoice->tax_amount, 2) }} {{ $invoice->currency }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between font-semibold text-gray-900 border-t border-gray-100 pt-1">
                            <span>{{ __('Total') }}</span>
                            <span>{{ number_format($invoice->total, 2) }} {{ $invoice->currency }}</span>
                        </div>
                    </div>
                </div>

                <div class="mt-8 print:hidden">
                    <button onclick="window.print()" class="rounded-md px-4 py-2 text-sm font-medium text-gray-700 ring-1 ring-gray-300 hover:bg-gray-50">
                        {{ __('Print / Save as PDF') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
