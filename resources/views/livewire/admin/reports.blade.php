<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Reports') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="flex gap-4 border-b border-gray-200 text-lg font-medium overflow-x-auto">
                @foreach (\App\Livewire\Admin\Reports::REPORTS as $key)
                    <button wire:click="selectReport('{{ $key }}')"
                        @class(['pb-3 border-b-2 whitespace-nowrap' => true, 'border-indigo-500 text-indigo-600' => $report === $key, 'border-transparent text-gray-800 hover:text-gray-800' => $report !== $key])>
                        {{ match ($key) {
                            'registrations' => __('Registrations'),
                            'enrolments' => __('Enrolments'),
                            'completions' => __('Completion Rates'),
                            'attendance' => __('Attendance'),
                            'assessments' => __('Assessment Performance'),
                            'lecturer_activity' => __('Lecturer Activity'),
                        } }}
                    </button>
                @endforeach
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-4">
                <div class="flex flex-wrap gap-3 items-end">
                    <div>
                        <x-input-label for="dateFrom" :value="__('From')" />
                        <x-text-input wire:model.live="dateFrom" id="dateFrom" type="date" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label for="dateTo" :value="__('To')" />
                        <x-text-input wire:model.live="dateTo" id="dateTo" type="date" class="mt-1" />
                    </div>
                    <x-secondary-button wire:click="exportCsv">{{ __('Export CSV') }}</x-secondary-button>
                    <span class="text-lg text-gray-800">{{ __(':n rows', ['n' => $rows->count()]) }}</span>
                </div>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
                @if ($rows->isEmpty())
                    <p class="px-4 py-6 text-center text-gray-800">{{ __('No data for this report in the selected date range.') }}</p>
                @else
                    <table class="min-w-full divide-y divide-gray-200 text-lg">
                        <thead class="bg-gray-50">
                            <tr>
                                @foreach (array_keys($rows->first()) as $column)
                                    <th class="px-4 py-2 text-start font-medium text-gray-800 whitespace-nowrap">{{ $column }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($rows as $row)
                                <tr>
                                    @foreach ($row as $value)
                                        <td class="px-4 py-3 text-gray-800 whitespace-nowrap">{{ $value }}</td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>
</div>
