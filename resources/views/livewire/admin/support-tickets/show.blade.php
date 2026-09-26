<div>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $ticket->ticket_number }}: {{ $ticket->subject }}
            </h2>
            <a href="{{ route('admin.support-tickets.index') }}" wire:navigate class="text-lg text-gray-800 hover:text-gray-900 underline">
                {{ __('Back to queue') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <div class="flex items-start justify-between flex-wrap gap-2">
                    <div class="text-lg text-gray-800">
                        {{ __('Submitted by') }} <span class="font-medium text-gray-800">{{ $ticket->user->name }}</span> ({{ $ticket->user->email }})
                        <br>
                        {{ \App\Models\SupportTicket::categoryLabel($ticket->category) }}
                        &middot; {{ __(':priority priority', ['priority' => \App\Models\SupportTicket::priorityLabel($ticket->priority)]) }}
                        &middot; {{ $ticket->created_at->format('Y-m-d H:i') }}
                    </div>
                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-sm font-medium bg-gray-100 text-gray-800">
                        {{ \App\Models\SupportTicket::statusLabel($ticket->status) }}
                    </span>
                </div>
                <p class="mt-4 text-gray-800 whitespace-pre-line">{{ $ticket->description }}</p>

                @if ($ticket->attachment_path)
                    <a href="{{ route('support-tickets.attachment', $ticket) }}" class="mt-3 inline-block text-lg text-indigo-600 hover:text-indigo-800 underline">
                        {{ __('Download attachment') }}: {{ $ticket->attachment_name }}
                    </a>
                @endif
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-4">
                <div class="flex flex-wrap items-end gap-4">
                    <div>
                        <x-input-label :value="__('Assigned To')" />
                        <div class="flex gap-2 mt-1">
                            <select wire:change="assignTo($event.target.value)" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">{{ __('Unassigned') }}</option>
                                @foreach ($staff as $member)
                                    <option value="{{ $member->id }}" @selected($ticket->assigned_to === $member->id)>{{ $member->name }}</option>
                                @endforeach
                            </select>
                            @if (! $ticket->assigned_to)
                                <x-secondary-button wire:click="assignToMe">{{ __('Assign to Me') }}</x-secondary-button>
                            @endif
                        </div>
                    </div>
                    <div>
                        <x-input-label :value="__('Status')" />
                        <div class="flex flex-wrap gap-1 mt-1">
                            @foreach (\App\Models\SupportTicket::STATUSES as $s)
                                <button wire:click="updateStatus('{{ $s }}')"
                                    @class([
                                        'text-sm px-2 py-1 rounded-md border',
                                        'bg-indigo-600 text-white border-indigo-600' => $ticket->status === $s,
                                        'bg-white text-gray-800 border-gray-300 hover:bg-gray-50' => $ticket->status !== $s,
                                    ])>
                                    {{ \App\Models\SupportTicket::statusLabel($s) }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-3">
                @foreach ($replies as $reply)
                    <div @class([
                        'shadow-sm sm:rounded-lg p-4',
                        'bg-amber-50 border border-amber-200' => $reply->is_internal_note,
                        'bg-indigo-50' => ! $reply->is_internal_note && $reply->user_id !== $ticket->user_id,
                        'bg-white' => ! $reply->is_internal_note && $reply->user_id === $ticket->user_id,
                    ])>
                        <div class="text-sm text-gray-800">
                            {{ $reply->user->name }}
                            @if ($reply->is_internal_note)
                                <span class="text-amber-700">({{ __('Internal Note') }})</span>
                            @elseif ($reply->user_id !== $ticket->user_id)
                                <span class="text-indigo-600">({{ __('Support') }})</span>
                            @endif
                            &middot; {{ $reply->created_at->format('Y-m-d H:i') }}
                        </div>
                        <p class="mt-2 text-lg text-gray-800 whitespace-pre-line">{{ $reply->body }}</p>
                    </div>
                @endforeach
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-4">
                <form wire:submit="reply" class="space-y-3">
                    <textarea wire:model="replyBody" rows="3" placeholder="{{ __('Write a reply...') }}"
                        class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full"></textarea>
                    <x-input-error :messages="$errors->get('replyBody')" class="mt-1" />
                    <label class="flex items-center gap-2 text-lg text-gray-800">
                        <input type="checkbox" wire:model="isInternalNote" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        {{ __('Internal note (not visible to the user)') }}
                    </label>
                    <x-primary-button>{{ __('Post Reply') }}</x-primary-button>
                </form>
            </div>
        </div>
    </div>
</div>
