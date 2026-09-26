<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Lecture Rooms') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <div class="flex items-center justify-between gap-4 mb-4">
                    <h3 class="font-medium text-gray-900">{{ __('Add Lecture Room') }}</h3>
                    <button type="button" wire:click="toggleBulkAdd" class="text-lg text-indigo-600 hover:text-indigo-800 underline whitespace-nowrap">
                        {{ $showBulkAdd ? __('Add one at a time instead') : __('Bulk Add') }}
                    </button>
                </div>

                @if (! $showBulkAdd)
                    <form wire:submit="create" class="grid sm:grid-cols-2 lg:grid-cols-4 gap-3 items-start">
                        <div>
                            <x-input-label for="name" :value="__('Room Name')" />
                            <x-text-input wire:model="name" id="name" class="mt-1 block w-full" />
                            <x-input-error :messages="$errors->get('name')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="capacity" :value="__('Capacity (optional)')" />
                            <x-text-input wire:model="capacity" id="capacity" type="number" min="1" class="mt-1 block w-full" />
                            <x-input-error :messages="$errors->get('capacity')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="location" :value="__('Location (optional)')" />
                            <x-text-input wire:model="location" id="location" class="mt-1 block w-full" placeholder="{{ __('e.g. Building A, Floor 2') }}" />
                            <x-input-error :messages="$errors->get('location')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="notes" :value="__('Notes (optional)')" />
                            <x-text-input wire:model="notes" id="notes" class="mt-1 block w-full" />
                            <x-input-error :messages="$errors->get('notes')" class="mt-1" />
                        </div>
                        <div class="lg:col-span-4">
                            <x-primary-button>{{ __('Add Room') }}</x-primary-button>
                        </div>
                    </form>
                @else
                    <p class="text-lg text-gray-800 mb-3">
                        {{ __('One room per line: name, then optionally capacity and location separated by "|". Example:') }}
                        <br>
                        <code class="text-sm text-gray-600">{{ __('Building A - Room 101 | 30 | Building A, Floor 1') }}</code>
                    </p>
                    <textarea wire:model="bulkAddText" rows="6" placeholder="Building A - Room 101 | 30 | Building A, Floor 1&#10;Building A - Room 102 | 25"
                        class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full font-mono text-lg"></textarea>
                    <x-input-error :messages="$errors->get('bulkAddText')" class="mt-1" />

                    <div class="mt-3">
                        <x-primary-button wire:click="runBulkAdd" wire:loading.attr="disabled" wire:target="runBulkAdd">
                            {{ __('Import') }}
                        </x-primary-button>
                    </div>

                    @if ($bulkAddCreated > 0)
                        <div class="mt-4 rounded-md bg-green-50 px-4 py-3 text-lg text-green-700">
                            {{ __('Rooms created: :count', ['count' => $bulkAddCreated]) }}
                        </div>
                    @endif

                    @if (count($bulkAddFailures))
                        <div class="mt-4">
                            <p class="text-lg font-medium text-red-700 mb-2">
                                {{ __('Rows skipped: :count', ['count' => count($bulkAddFailures)]) }}
                            </p>
                            <ul class="space-y-1 text-lg">
                                @foreach ($bulkAddFailures as $failure)
                                    <li class="rounded-md bg-red-50 px-3 py-2 text-red-700">
                                        <span class="font-mono text-sm">{{ $failure['line'] }}</span>
                                        — {{ $failure['error'] }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                @endif
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 text-lg">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Name') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Capacity') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Location') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Status') }}</th>
                            <th class="px-4 py-2"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($rooms as $room)
                            <tr wire:key="room-{{ $room->id }}">
                                @if ($editingId === $room->id)
                                    <td class="px-4 py-3" colspan="5">
                                        <form wire:submit="update" class="grid sm:grid-cols-2 lg:grid-cols-4 gap-3 items-start">
                                            <div>
                                                <x-input-label for="editingName" :value="__('Room Name')" />
                                                <x-text-input wire:model="editingName" id="editingName" class="mt-1 block w-full" />
                                                <x-input-error :messages="$errors->get('editingName')" class="mt-1" />
                                            </div>
                                            <div>
                                                <x-input-label for="editingCapacity" :value="__('Capacity (optional)')" />
                                                <x-text-input wire:model="editingCapacity" id="editingCapacity" type="number" min="1" class="mt-1 block w-full" />
                                                <x-input-error :messages="$errors->get('editingCapacity')" class="mt-1" />
                                            </div>
                                            <div>
                                                <x-input-label for="editingLocation" :value="__('Location (optional)')" />
                                                <x-text-input wire:model="editingLocation" id="editingLocation" class="mt-1 block w-full" />
                                            </div>
                                            <div>
                                                <x-input-label for="editingNotes" :value="__('Notes (optional)')" />
                                                <x-text-input wire:model="editingNotes" id="editingNotes" class="mt-1 block w-full" />
                                            </div>
                                            <div class="flex gap-2 self-end">
                                                <x-primary-button>{{ __('Save') }}</x-primary-button>
                                                <x-secondary-button type="button" wire:click="cancelEdit">{{ __('Cancel') }}</x-secondary-button>
                                            </div>
                                        </form>
                                    </td>
                                @else
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-gray-900">{{ $room->name }}</div>
                                        @if ($room->notes)
                                            <div class="text-gray-800">{{ $room->notes }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-gray-800">{{ $room->capacity ?? '—' }}</td>
                                    <td class="px-4 py-3 text-gray-800">{{ $room->location ?? '—' }}</td>
                                    <td class="px-4 py-3">
                                        <button wire:click="toggleActive({{ $room->id }})" @class([
                                            'inline-flex items-center rounded-full px-2.5 py-0.5 text-sm font-medium',
                                            'bg-green-100 text-green-800' => $room->is_active,
                                            'bg-gray-100 text-gray-800' => ! $room->is_active,
                                        ])>
                                            {{ $room->is_active ? __('Active') : __('Inactive') }}
                                        </button>
                                    </td>
                                    <td class="px-4 py-3 text-end space-x-2 rtl:space-x-reverse">
                                        <button wire:click="edit({{ $room->id }})" class="text-indigo-600 hover:text-indigo-800 underline">{{ __('Edit') }}</button>
                                        <button wire:click="delete({{ $room->id }})" wire:confirm="{{ __('Delete this room?') }}" class="text-red-600 hover:text-red-800 underline">{{ __('Delete') }}</button>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-gray-800">{{ __('No lecture rooms yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
