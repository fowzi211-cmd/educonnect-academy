<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Audit Logs') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white shadow-sm sm:rounded-lg p-4">
                <div class="flex flex-wrap gap-3 items-end">
                    <div>
                        <x-input-label for="search" :value="__('Search')" />
                        <x-text-input wire:model.live.debounce.400ms="search" id="search" class="mt-1" placeholder="{{ __('Reason, action, user name or email') }}" />
                    </div>
                    <div>
                        <x-input-label for="userFilter" :value="__('User')" />
                        <select wire:model.live="userFilter" id="userFilter" class="mt-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="">{{ __('All') }}</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="actionFilter" :value="__('Action')" />
                        <select wire:model.live="actionFilter" id="actionFilter" class="mt-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="">{{ __('All') }}</option>
                            @foreach ($actions as $action)
                                <option value="{{ $action }}">{{ $action }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="auditableTypeFilter" :value="__('Subject type')" />
                        <select wire:model.live="auditableTypeFilter" id="auditableTypeFilter" class="mt-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="">{{ __('All') }}</option>
                            @foreach ($auditableTypes as $type)
                                <option value="{{ $type }}">{{ class_basename($type) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="dateFrom" :value="__('From')" />
                        <x-text-input wire:model.live="dateFrom" id="dateFrom" type="date" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label for="dateTo" :value="__('To')" />
                        <x-text-input wire:model.live="dateTo" id="dateTo" type="date" class="mt-1" />
                    </div>
                    <x-secondary-button wire:click="resetFilters">{{ __('Clear filters') }}</x-secondary-button>
                </div>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 text-lg">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('When') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('User') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Action') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Subject') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Reason') }}</th>
                            <th class="px-4 py-2"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($logs as $log)
                            <tr wire:key="log-{{ $log->id }}">
                                <td class="px-4 py-3 text-gray-800 whitespace-nowrap">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                                <td class="px-4 py-3">
                                    @if ($log->user)
                                        <div class="font-medium text-gray-900">{{ $log->user->name }}</div>
                                        <div class="text-gray-800">{{ $log->user->email }}</div>
                                    @else
                                        <span class="text-gray-600">{{ __('System') }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-800 font-mono text-sm">{{ $log->action }}</td>
                                <td class="px-4 py-3 text-gray-800">
                                    @if ($log->auditable_type)
                                        {{ class_basename($log->auditable_type) }} #{{ $log->auditable_id }}
                                    @else
                                        &mdash;
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-800 max-w-xs truncate">{{ $log->reason ?: '—' }}</td>
                                <td class="px-4 py-3 text-end">
                                    <button wire:click="toggleExpand({{ $log->id }})" class="text-indigo-600 hover:text-indigo-800 underline">
                                        {{ $expandedId === $log->id ? __('Hide') : __('Details') }}
                                    </button>
                                </td>
                            </tr>

                            @if ($expandedId === $log->id)
                                <tr>
                                    <td colspan="6" class="px-4 py-4 bg-gray-50">
                                        <div class="grid sm:grid-cols-2 gap-4 text-sm">
                                            <div>
                                                <div class="font-medium text-gray-800 mb-1">{{ __('Old values') }}</div>
                                                <pre class="bg-white border border-gray-200 rounded p-2 overflow-x-auto">{{ $log->old_values ? json_encode($log->old_values, JSON_PRETTY_PRINT) : __('None') }}</pre>
                                            </div>
                                            <div>
                                                <div class="font-medium text-gray-800 mb-1">{{ __('New values') }}</div>
                                                <pre class="bg-white border border-gray-200 rounded p-2 overflow-x-auto">{{ $log->new_values ? json_encode($log->new_values, JSON_PRETTY_PRINT) : __('None') }}</pre>
                                            </div>
                                        </div>
                                        <div class="mt-3 grid sm:grid-cols-3 gap-4 text-sm text-gray-800">
                                            <div><span class="font-medium text-gray-800">{{ __('Reason') }}:</span> {{ $log->reason ?: '—' }}</div>
                                            <div><span class="font-medium text-gray-800">{{ __('IP address') }}:</span> {{ $log->ip_address ?: '—' }}</div>
                                            <div><span class="font-medium text-gray-800">{{ __('User agent') }}:</span> {{ $log->user_agent ?: '—' }}</div>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-6 text-center text-gray-800">{{ __('No audit log entries match these filters.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div>
                {{ $logs->links() }}
            </div>
        </div>
    </div>
</div>
