<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Course Categories') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <div class="flex items-center justify-between gap-4 mb-4">
                    <h3 class="font-medium text-gray-900">{{ __('Add Category') }}</h3>
                    <button type="button" wire:click="toggleBulkAdd" class="text-lg text-indigo-600 hover:text-indigo-800 underline whitespace-nowrap">
                        {{ $showBulkAdd ? __('Add one at a time instead') : __('Bulk Add') }}
                    </button>
                </div>

                @if (! $showBulkAdd)
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
                @else
                    <p class="text-lg text-gray-800 mb-3">
                        {{ __('One category per line. An optional description can follow a "|". Example:') }}
                        <br>
                        <code class="text-sm text-gray-600">{{ __('Physics | Foundations of classical and modern physics.') }}</code>
                    </p>
                    <textarea wire:model="bulkAddText" rows="6" placeholder="Physics&#10;Chemistry&#10;Biology"
                        class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full font-mono text-lg"></textarea>
                    <x-input-error :messages="$errors->get('bulkAddText')" class="mt-1" />

                    <div class="mt-3">
                        <x-primary-button wire:click="runBulkAdd" wire:loading.attr="disabled" wire:target="runBulkAdd">
                            {{ __('Import') }}
                        </x-primary-button>
                    </div>

                    @if ($bulkAddCreated > 0)
                        <div class="mt-4 rounded-md bg-green-50 px-4 py-3 text-lg text-green-700">
                            {{ __('Categories created: :count', ['count' => $bulkAddCreated]) }}
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
                <x-input-error :messages="$errors->get('delete')" class="m-4" />

                <table class="min-w-full divide-y divide-gray-200 text-lg">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Name') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Courses') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Status') }}</th>
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
                                            <div class="text-gray-800">{{ $category->description }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-gray-800">{{ $category->courses_count }}</td>
                                    <td class="px-4 py-3">
                                        <button wire:click="toggleActive({{ $category->id }})" @class([
                                            'inline-flex items-center rounded-full px-2.5 py-0.5 text-sm font-medium',
                                            'bg-green-100 text-green-800' => $category->is_active,
                                            'bg-gray-100 text-gray-800' => ! $category->is_active,
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
