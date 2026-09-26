<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Support Tickets') }} ({{ $openCount }} {{ __('open') }})
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white shadow-sm sm:rounded-lg p-4">
                <div class="flex flex-wrap gap-3 items-end">
                    <div>
                        <x-input-label for="status" :value="__('Status')" />
                        <select wire:model.live="status" id="status" class="mt-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="">{{ __('All') }}</option>
                            @foreach (\App\Models\SupportTicket::STATUSES as $s)
                                <option value="{{ $s }}">{{ \App\Models\SupportTicket::statusLabel($s) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="category" :value="__('Category')" />
                        <select wire:model.live="category" id="category" class="mt-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="">{{ __('All') }}</option>
                            @foreach (\App\Models\SupportTicket::CATEGORIES as $c)
                                <option value="{{ $c }}">{{ \App\Models\SupportTicket::categoryLabel($c) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="priority" :value="__('Priority')" />
                        <select wire:model.live="priority" id="priority" class="mt-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="">{{ __('All') }}</option>
                            @foreach (\App\Models\SupportTicket::PRIORITIES as $p)
                                <option value="{{ $p }}">{{ \App\Models\SupportTicket::priorityLabel($p) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="assignee" :value="__('Assigned')" />
                        <select wire:model.live="assignee" id="assignee" class="mt-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="">{{ __('All') }}</option>
                            <option value="me">{{ __('Assigned to me') }}</option>
                            <option value="unassigned">{{ __('Unassigned') }}</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 text-lg">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Ticket') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Submitted By') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Category') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Priority') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Assigned To') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Status') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Updated') }}</th>
                            <th class="px-4 py-2"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($tickets as $ticket)
                            <tr wire:key="ticket-{{ $ticket->id }}" class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <div class="font-mono text-sm text-gray-600">{{ $ticket->ticket_number }}</div>
                                    <div class="font-medium text-gray-900">{{ $ticket->subject }}</div>
                                </td>
                                <td class="px-4 py-3 text-gray-800">{{ $ticket->user->name }}</td>
                                <td class="px-4 py-3 text-gray-800">{{ \App\Models\SupportTicket::categoryLabel($ticket->category) }}</td>
                                <td class="px-4 py-3">
                                    <span @class([
                                        'inline-flex items-center rounded-full px-2.5 py-0.5 text-sm font-medium',
                                        'bg-red-100 text-red-800' => $ticket->priority === 'urgent',
                                        'bg-orange-100 text-orange-800' => $ticket->priority === 'high',
                                        'bg-yellow-100 text-yellow-800' => $ticket->priority === 'medium',
                                        'bg-gray-100 text-gray-800' => $ticket->priority === 'low',
                                    ])>
                                        {{ \App\Models\SupportTicket::priorityLabel($ticket->priority) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-800">{{ $ticket->assignedTo->name ?? __('Unassigned') }}</td>
                                <td class="px-4 py-3">
                                    <span @class([
                                        'inline-flex items-center rounded-full px-2.5 py-0.5 text-sm font-medium',
                                        'bg-yellow-100 text-yellow-800' => in_array($ticket->status, ['open', 'reopened']),
                                        'bg-blue-100 text-blue-800' => in_array($ticket->status, ['assigned', 'waiting_for_internal_action']),
                                        'bg-purple-100 text-purple-800' => $ticket->status === 'waiting_for_user',
                                        'bg-green-100 text-green-800' => $ticket->status === 'resolved',
                                        'bg-gray-100 text-gray-800' => $ticket->status === 'closed',
                                    ])>
                                        {{ \App\Models\SupportTicket::statusLabel($ticket->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-800">{{ $ticket->updated_at->format('Y-m-d H:i') }}</td>
                                <td class="px-4 py-3 text-end">
                                    <a href="{{ route('admin.support-tickets.show', $ticket) }}" wire:navigate class="text-indigo-600 hover:text-indigo-800 underline">{{ __('View') }}</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-6 text-center text-gray-800">{{ __('No support tickets match these filters.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div>
                {{ $tickets->links() }}
            </div>
        </div>
    </div>
</div>
