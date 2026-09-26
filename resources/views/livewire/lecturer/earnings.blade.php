<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My Earnings') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="grid sm:grid-cols-3 gap-4">
                <div class="bg-white shadow-sm sm:rounded-lg p-4">
                    <div class="text-lg text-gray-800">{{ __('Available Balance') }}</div>
                    <div class="text-2xl font-semibold text-gray-900">{{ number_format($availableBalance, 2) }}</div>
                </div>
                <div class="bg-white shadow-sm sm:rounded-lg p-4">
                    <div class="text-lg text-gray-800">{{ __('Payout Threshold') }}</div>
                    <div class="text-2xl font-semibold text-gray-900">{{ number_format($payoutThreshold, 2) }}</div>
                </div>
                <div class="bg-white shadow-sm sm:rounded-lg p-4">
                    <div class="text-lg text-gray-800">{{ __('Total Paid Out') }}</div>
                    <div class="text-2xl font-semibold text-gray-900">{{ number_format($totalPaid, 2) }}</div>
                </div>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                @if ($requestSuccess)
                    <div class="mb-4 rounded-md bg-green-50 px-4 py-3 text-lg text-green-700">
                        {{ __('Payout requested. Finance will review it shortly.') }}
                    </div>
                @endif
                @if ($requestError)
                    <div class="mb-4 rounded-md bg-red-50 px-4 py-3 text-lg text-red-700">
                        {{ $requestError }}
                    </div>
                @endif

                @php $canRequestPayout = $availableBalance > 0 && $availableBalance >= $payoutThreshold; @endphp
                <div class="flex items-center justify-between">
                    <p class="text-lg text-gray-800">
                        {{ __('Request a payout once your available balance reaches the payout threshold.') }}
                    </p>
                    <x-primary-button wire:click="requestPayout" wire:loading.attr="disabled" :disabled="! $canRequestPayout">
                        {{ __('Request Payout') }}
                    </x-primary-button>
                </div>
            </div>

            @if ($payoutRequests->isNotEmpty())
                <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                    <div class="px-4 pt-4">
                        <h3 class="text-lg font-semibold text-gray-900">{{ __('Payout Requests') }}</h3>
                    </div>
                    <table class="min-w-full divide-y divide-gray-200 text-lg mt-2">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Requested') }}</th>
                                <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Amount') }}</th>
                                <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Status') }}</th>
                                <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Reference') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($payoutRequests as $request)
                                <tr wire:key="payout-{{ $request->id }}">
                                    <td class="px-4 py-3 text-gray-800">{{ $request->requested_at->format('Y-m-d') }}</td>
                                    <td class="px-4 py-3 text-gray-800">{{ number_format($request->amount, 2) }} {{ $request->currency }}</td>
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
                                        @if ($request->status === 'rejected' && $request->rejection_reason)
                                            <p class="text-sm text-gray-800 mt-1">{{ $request->rejection_reason }}</p>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-gray-800">{{ $request->payout_reference ?: '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <div class="px-4 pt-4">
                    <h3 class="text-lg font-semibold text-gray-900">{{ __('Earnings Statement') }}</h3>
                </div>
                <table class="min-w-full divide-y divide-gray-200 text-lg mt-2">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Date') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Course') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Gross') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Rate') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Refunded') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Net Earning') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($earnings as $earning)
                            <tr wire:key="earning-{{ $earning->id }}">
                                <td class="px-4 py-3 text-gray-800">{{ $earning->created_at->format('Y-m-d') }}</td>
                                <td class="px-4 py-3 text-gray-800">{{ $earning->course->title }}</td>
                                <td class="px-4 py-3 text-gray-800">{{ number_format($earning->gross_amount, 2) }} {{ $earning->currency }}</td>
                                <td class="px-4 py-3 text-gray-800">
                                    {{ $earning->rate_type === 'percentage' ? number_format($earning->rate_value, 2).'%' : number_format($earning->rate_value, 2).' '.$earning->currency }}
                                </td>
                                <td class="px-4 py-3 text-gray-800">
                                    @if ($earning->refunded_amount > 0)
                                        {{ number_format($earning->refunded_amount, 2) }} {{ $earning->currency }}
                                    @else
                                        &mdash;
                                    @endif
                                </td>
                                <td class="px-4 py-3 font-medium text-gray-900">{{ number_format($earning->netAmount(), 2) }} {{ $earning->currency }}</td>
                                <td class="px-4 py-3">
                                    <span @class([
                                        'inline-flex items-center rounded-full px-2.5 py-0.5 text-sm font-medium',
                                        'bg-green-100 text-green-800' => $earning->status === 'paid',
                                        'bg-yellow-100 text-yellow-800' => $earning->status === 'unpaid',
                                    ])>
                                        {{ $earning->status === 'paid' ? __('Paid') : __('Unpaid') }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-6 text-center text-gray-800">{{ __('No earnings recorded yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div>
                {{ $earnings->links() }}
            </div>
        </div>
    </div>
</div>
