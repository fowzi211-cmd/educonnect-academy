<div>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $ticket->ticket_number }}: {{ $ticket->subject }}
            </h2>
            <a href="{{ route('support-tickets.index') }}" wire:navigate class="text-sm text-gray-600 hover:text-gray-900 underline">
                {{ __('Back to tickets') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <p class="text-sm text-gray-600">
                        {{ \App\Models\SupportTicket::categoryLabel($ticket->category) }}
                        &middot; {{ __(':priority priority', ['priority' => \App\Models\SupportTicket::priorityLabel($ticket->priority)]) }}
                        &middot; {{ $ticket->created_at->format('Y-m-d H:i') }}
                    </p>
                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-gray-100 text-gray-700">
                        {{ \App\Models\SupportTicket::statusLabel($ticket->status) }}
                    </span>
                </div>
                <p class="mt-4 text-gray-700 whitespace-pre-line">{{ $ticket->description }}</p>

                @if ($ticket->attachment_path)
                    <a href="{{ route('support-tickets.attachment', $ticket) }}" class="mt-3 inline-block text-sm text-indigo-600 hover:text-indigo-800 underline">
                        {{ __('Download attachment') }}: {{ $ticket->attachment_name }}
                    </a>
                @endif
            </div>

            <div class="space-y-3">
                @foreach ($replies as $reply)
                    <div @class(['bg-white shadow-sm sm:rounded-lg p-4', 'bg-indigo-50' => $reply->user_id !== $ticket->user_id])>
                        <div class="text-xs text-gray-600">
                            {{ $reply->user->name }}
                            @if ($reply->user_id !== $ticket->user_id)
                                <span class="text-indigo-600">({{ __('Support') }})</span>
                            @endif
                            &middot; {{ $reply->created_at->format('Y-m-d H:i') }}
                        </div>
                        <p class="mt-2 text-sm text-gray-700 whitespace-pre-line">{{ $reply->body }}</p>
                    </div>
                @endforeach
            </div>

            @if (in_array($ticket->status, ['resolved', 'closed']))
                <div class="bg-white shadow-sm sm:rounded-lg p-4 flex items-center justify-between">
                    <p class="text-sm text-gray-600 italic">{{ __('This ticket has been :status.', ['status' => \App\Models\SupportTicket::statusLabel($ticket->status)]) }}</p>
                    <x-secondary-button wire:click="reopen">{{ __('Reopen Ticket') }}</x-secondary-button>
                </div>
            @else
                <div class="bg-white shadow-sm sm:rounded-lg p-4">
                    <form wire:submit="reply" class="space-y-3">
                        <textarea wire:model="replyBody" rows="3" placeholder="{{ __('Write a reply...') }}"
                            class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full"></textarea>
                        <x-input-error :messages="$errors->get('replyBody')" class="mt-1" />
                        <x-primary-button>{{ __('Reply') }}</x-primary-button>
                    </form>
                </div>
            @endif
        </div>
    </div>
</div>
