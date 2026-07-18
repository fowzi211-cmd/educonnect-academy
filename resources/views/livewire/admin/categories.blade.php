<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Course Categories') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="font-medium text-gray-900 mb-4">{{ __('Add Category') }}</h3>
                <form wire:submit="create" class="flex gap-3 items-start">
                    <div class="flex-1">
                        <x-text-input wire:model="name" placeholder="{{ __('Category name') }}" class="block w-full" type="text" />
                        <x-input-error :messages="$errors->get('name')" class="mt-1" />
                    </div>
                    <div class="flex-1">
                        <x-text-input wire:model="description" placeholder="{{ __('Description (optional)') }}" class="block w-full" type="text" />
                        <x-input-error :messages="$errors->get('description')" class="mt-1" />
                    </div>
                    <x-primary-button>{{ __('Add') }}</x-primary-button>
                </form>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <x-input-error :messages="$errors->get('delete')" class="m-4" />

                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-start font-medium text-gray-500">{{ __('Name') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-500">{{ __('Courses') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-500">{{ __('Status') }}</th>
                            <th class="px-4 py-2"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($categories as $category)
                            <tr wire:key="category-{{ $category->id }}">
                                @if ($editingId === $category->id)
                                    <td class="px-4 py-3" colspan="4">
                                        <form wire:submit="update" class="flex gap-3 items-start">
                                            <div class="flex-1">
                                                <x-text-input wire:model="editingName" class="block w-full" type="text" />
                                                <x-input-error :messages="$errors->get('editingName')" class="mt-1" />
                                            </div>
                                            <div class="flex-1">
                                                <x-text-input wire:model="editingDescription" class="block w-full" type="text" />
                                            </div>
                                            <x-primary-button>{{ __('Save') }}</x-primary-button>
                                            <x-secondary-button type="button" wire:click="cancelEdit">{{ __('Cancel') }}</x-secondary-button>
                                        </form>
                                    </td>
                                @else
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-gray-900">{{ $category->name }}</div>
                                        @if ($category->description)
                                            <div class="text-gray-500">{{ $category->description }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-gray-600">{{ $category->courses_count }}</td>
                                    <td class="px-4 py-3">
                                        <button wire:click="toggleActive({{ $category->id }})" @class([
                                            'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium',
                                            'bg-green-100 text-green-800' => $category->is_active,
                                            'bg-gray-100 text-gray-600' => ! $category->is_active,
                                        ])>
                                            {{ $category->is_active ? __('Active') : __('Inactive') }}
                                        </button>
                                    </td>
                                    <td class="px-4 py-3 text-end space-x-2 rtl:space-x-reverse">
                                        <button wire:click="edit({{ $category->id }})" class="text-indigo-600 hover:text-indigo-800 underline">{{ __('Edit') }}</button>
                                        <button wire:click="delete({{ $category->id }})" wire:confirm="{{ __('Delete this category?') }}" class="text-red-600 hover:text-red-800 underline">{{ __('Delete') }}</button>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
