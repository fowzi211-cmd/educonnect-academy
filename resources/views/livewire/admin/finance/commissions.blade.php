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
                <h3 class="text-lg font-semibold text-gray-900 mb-1">
                    {{ $editingId ? __('Edit commission override') : __('New commission override') }}
                </h3>
                <p class="text-lg text-gray-800 mb-4">{{ __('Overrides the default commission rate for a specific lecturer, or a specific lecturer and course together. Leave course empty to override all of a lecturer\'s courses.') }}</p>

                <form wire:submit="save" class="grid sm:grid-cols-4 gap-4 items-end">
                    <div>
                        <x-input-label for="lecturerId" :value="__('Lecturer')" />
                        <select wire:model="lecturerId" id="lecturerId" class="mt-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full">
                            <option value="">{{ __('Select a lecturer') }}</option>
                            @foreach ($lecturers as $lecturer)
                                <option value="{{ $lecturer->id }}">{{ $lecturer->name }} ({{ $lecturer->email }})</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('lecturerId')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label for="courseId" :value="__('Course (optional)')" />
                        <select wire:model="courseId" id="courseId" class="mt-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full">
                            <option value="">{{ __('All courses') }}</option>
                            @foreach ($courses as $course)
                                <option value="{{ $course->id }}">{{ $course->title }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('courseId')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label for="type" :value="__('Rate Type')" />
                        <select wire:model="type" id="type" class="mt-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full">
                            <option value="percentage">{{ __('Percentage') }}</option>
                            <option value="fixed">{{ __('Fixed amount') }}</option>
                        </select>
                        <x-input-error :messages="$errors->get('type')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label for="value" :value="__('Rate Value')" />
                        <x-text-input wire:model="value" id="value" type="number" step="0.01" min="0" class="mt-1 block w-full" />
                        <x-input-error :messages="$errors->get('value')" class="mt-1" />
                    </div>
                    <div class="sm:col-span-4 flex gap-2 justify-end">
                        @if ($editingId)
                            <x-secondary-button type="button" wire:click="cancelEdit">{{ __('Cancel') }}</x-secondary-button>
                        @endif
                        <x-primary-button>{{ $editingId ? __('Update Rate') : __('Add Rate') }}</x-primary-button>
                    </div>
                </form>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 text-lg">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Lecturer') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Course') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Rate Type') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Rate Value') }}</th>
                            <th class="px-4 py-2"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($rates as $rate)
                            <tr wire:key="rate-{{ $rate->id }}">
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-900">{{ $rate->user->name }}</div>
                                    <div class="text-gray-800">{{ $rate->user->email }}</div>
                                </td>
                                <td class="px-4 py-3 text-gray-800">{{ $rate->course->title ?? __('All courses') }}</td>
                                <td class="px-4 py-3 text-gray-800">{{ $rate->type === 'percentage' ? __('Percentage') : __('Fixed amount') }}</td>
                                <td class="px-4 py-3 text-gray-800">{{ $rate->type === 'percentage' ? number_format($rate->value, 2).'%' : number_format($rate->value, 2) }}</td>
                                <td class="px-4 py-3 text-end space-x-2 rtl:space-x-reverse">
                                    <button wire:click="edit({{ $rate->id }})" class="text-indigo-600 hover:text-indigo-800 underline">{{ __('Edit') }}</button>
                                    <button wire:click="confirmDelete({{ $rate->id }})" class="text-red-600 hover:text-red-800 underline">{{ __('Delete') }}</button>
                                </td>
                            </tr>

                            @if ($deletingId === $rate->id)
                                <tr>
                                    <td colspan="5" class="px-4 py-3 bg-red-50">
                                        <p class="text-lg text-gray-800 mb-2">{{ __('Remove this commission override? Future earnings will fall back to the next applicable rate.') }}</p>
                                        <div class="flex gap-2">
                                            <x-danger-button wire:click="delete">{{ __('Confirm Delete') }}</x-danger-button>
                                            <x-secondary-button wire:click="$set('deletingId', null)">{{ __('Never mind') }}</x-secondary-button>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-gray-800">{{ __('No commission overrides set. Lecturers earn the platform default rate.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div>
                {{ $rates->links() }}
            </div>
        </div>
    </div>
</div>
