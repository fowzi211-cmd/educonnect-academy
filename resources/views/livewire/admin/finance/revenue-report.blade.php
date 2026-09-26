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
                <div class="flex flex-wrap gap-3 items-end mb-6">
                    <div>
                        <x-input-label for="startDate" :value="__('From')" />
                        <x-text-input wire:model.live="startDate" id="startDate" type="date" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label for="endDate" :value="__('To')" />
                        <x-text-input wire:model.live="endDate" id="endDate" type="date" class="mt-1" />
                    </div>
                </div>

                @forelse ($revenueByCurrency as $row)
                    <div class="grid sm:grid-cols-4 gap-4 mb-6 p-4 bg-gray-50 rounded-lg">
                        <div>
                            <div class="text-sm uppercase text-gray-800">{{ __('Gross Revenue') }}</div>
                            <div class="text-2xl font-semibold text-gray-900">{{ number_format($row->gross, 2) }} {{ $row->currency }}</div>
                        </div>
                        <div>
                            <div class="text-sm uppercase text-gray-800">{{ __('Refunded') }}</div>
                            <div class="text-2xl font-semibold text-red-600">{{ number_format($row->refunded, 2) }} {{ $row->currency }}</div>
                        </div>
                        <div>
                            <div class="text-sm uppercase text-gray-800">{{ __('Net Revenue') }}</div>
                            <div class="text-2xl font-semibold text-green-700">{{ number_format($row->net, 2) }} {{ $row->currency }}</div>
                        </div>
                        <div>
                            <div class="text-sm uppercase text-gray-800">{{ __('Payments') }}</div>
                            <div class="text-2xl font-semibold text-gray-900">{{ $row->count }}</div>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-800 mb-6">{{ __('No revenue recorded in this period.') }}</p>
                @endforelse

                <h3 class="font-medium text-gray-900 mb-3">{{ __('Top Courses by Revenue') }}</h3>
                <table class="min-w-full divide-y divide-gray-200 text-lg">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Course') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Payments') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Revenue') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($byCourse as $row)
                            <tr>
                                <td class="px-4 py-3 text-gray-800">{{ $row->title }}</td>
                                <td class="px-4 py-3 text-gray-800">{{ $row->count }}</td>
                                <td class="px-4 py-3 text-gray-800">{{ number_format($row->total, 2) }} {{ $row->currency }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-6 text-center text-gray-800">{{ __('No data for this period.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
