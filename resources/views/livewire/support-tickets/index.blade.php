<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Support') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if ($showForm)
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <h3 class="font-medium text-gray-900 mb-4">{{ __('New Support Ticket') }}</h3>
                    <form wire:submit="create" class="space-y-4">
                        <div class="grid sm:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="category" :value="__('Category')" />
                                <select wire:model="category" id="category" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full">
                                    <option value="">{{ __('Select a category') }}</option>
                                    @foreach (\App\Models\SupportTicket::CATEGORIES as $cat)
                                        <option value="{{ $cat }}">{{ \App\Models\SupportTicket::categoryLabel($cat) }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('category')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="priority" :value="__('Priority')" />
                                <select wire:model="priority" id="priority" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full">
                                    @foreach (\App\Models\SupportTicket::PRIORITIES as $pri)
                                        <option value="{{ $pri }}">{{ \App\Models\SupportTicket::priorityLabel($pri) }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('priority')" class="mt-2" />
                            </div>
                        </div>

                        <div>
                            <x-input-label for="subject" :value="__('Subject')" />
                            <x-text-input wire:model="subject" id="subject" class="block mt-1 w-full" />
                            <x-input-error :messages="$errors->get('subject')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="description" :value="__('Description')" />
                            <textarea wire:model="description" id="description" rows="5"
                                class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full"></textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="attachment" :value="__('Attachment (optional)')" />
                            <input wire:model="attachment" id="attachment" type="file"
                                class="block mt-1 w-full text-sm text-gray-600 file:me-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:bg-indigo-50 file:text-indigo-700" />
                            <x-input-error :messages="$errors->get('attachment')" class="mt-2" />
                        </div>

                        <div class="flex gap-2">
                            <x-primary-button>{{ __('Submit Ticket') }}</x-primary-button>
                            <x-secondary-button type="button" wire:click="cancelForm">{{ __('Cancel') }}</x-secondary-button>
                        </div>
                    </form>
                </div>
            @else
                <div class="flex justify-end">
                    <x-primary-button wire:click="newForm">{{ __('New Support Ticket') }}</x-primary-button>
                </div>
            @endif

            <div class="space-y-3">
                @forelse ($tickets as $ticket)
                    <a href="{{ route('support-tickets.show', $ticket) }}" wire:navigate class="block bg-white shadow-sm sm:rounded-lg p-5 hover:bg-gray-50">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="text-xs text-gray-500 font-mono">{{ $ticket->ticket_number }}</span>
                                <h4 class="font-medium text-gray-900">{{ $ticket->subject }}</h4>
                            </div>
                            <span @class([
                                'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium',
                                'bg-yellow-100 text-yellow-800' => in_array($ticket->status, ['open', 'reopened']),
                                'bg-blue-100 text-blue-800' => in_array($ticket->status, ['assigned', 'waiting_for_internal_action']),
                                'bg-purple-100 text-purple-800' => $ticket->status === 'waiting_for_user',
                                'bg-green-100 text-green-800' => $ticket->status === 'resolved',
                                'bg-gray-100 text-gray-700' => $ticket->status === 'closed',
                            ])>
                                {{ \App\Models\SupportTicket::statusLabel($ticket->status) }}
                            </span>
                        </div>
                        <p class="mt-2 text-xs text-gray-500">
                            {{ \App\Models\SupportTicket::categoryLabel($ticket->category) }}
                            &middot; {{ __(':priority priority', ['priority' => \App\Models\SupportTicket::priorityLabel($ticket->priority)]) }}
                            &middot; {{ $ticket->created_at->format('Y-m-d H:i') }}
                        </p>
                    </a>
                @empty
                    <p class="text-center text-gray-600 py-6">{{ __('No support tickets yet.') }}</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
