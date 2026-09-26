<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Content Management') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @include('livewire.admin._content-nav')

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-1">
                    {{ $editingId ? __('Edit News Item') : __('New News Item') }}
                </h3>
                <p class="text-lg text-gray-800 mb-4">{{ __('Shown as a rotating carousel on the public home page.') }}</p>

                <form wire:submit="save" class="space-y-4">
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="title_en" :value="__('Title (English)')" />
                            <x-text-input wire:model="title_en" id="title_en" class="mt-1 block w-full" />
                            <x-input-error :messages="$errors->get('title_en')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="title_ar" :value="__('Title (Arabic)')" />
                            <x-text-input wire:model="title_ar" id="title_ar" class="mt-1 block w-full" dir="rtl" />
                            <x-input-error :messages="$errors->get('title_ar')" class="mt-1" />
                        </div>
                    </div>
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="excerpt_en" :value="__('Excerpt (English)')" />
                            <textarea wire:model="excerpt_en" id="excerpt_en" rows="3" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full"></textarea>
                            <x-input-error :messages="$errors->get('excerpt_en')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="excerpt_ar" :value="__('Excerpt (Arabic)')" />
                            <textarea wire:model="excerpt_ar" id="excerpt_ar" rows="3" dir="rtl" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full"></textarea>
                            <x-input-error :messages="$errors->get('excerpt_ar')" class="mt-1" />
                        </div>
                    </div>
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="link_url" :value="__('Read More Link (optional)')" />
                            <x-text-input wire:model="link_url" id="link_url" type="url" class="mt-1 block w-full" dir="ltr" placeholder="https://" />
                            <x-input-error :messages="$errors->get('link_url')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="image" :value="__('Image (optional)')" />
                            <input type="file" wire:model="image" id="image" accept="image/*" class="mt-1 block w-full text-lg text-gray-800 file:me-3 file:rounded-md file:border-0 file:bg-gray-100 file:px-3 file:py-2 file:text-lg file:font-medium hover:file:bg-gray-200">
                            <div wire:loading wire:target="image" class="mt-1 text-sm text-gray-800">{{ __('Uploading...') }}</div>
                            <x-input-error :messages="$errors->get('image')" class="mt-1" />
                            @if ($currentImagePath && ! $image)
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($currentImagePath) }}" alt="" class="mt-2 h-16 w-28 object-cover rounded-md">
                            @endif
                        </div>
                    </div>
                    <div class="flex gap-2">
                        @if ($editingId)
                            <x-secondary-button type="button" wire:click="cancelEdit">{{ __('Cancel') }}</x-secondary-button>
                        @endif
                        <x-primary-button wire:loading.attr="disabled" wire:target="save,image">{{ $editingId ? __('Update News Item') : __('Add News Item') }}</x-primary-button>
                    </div>
                </form>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 text-lg">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Order') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Title') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Published') }}</th>
                            <th class="px-4 py-2"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($newsItems as $item)
                            <tr wire:key="news-{{ $item->id }}">
                                <td class="px-4 py-3 text-gray-800">
                                    <div class="flex flex-col gap-0.5">
                                        <button wire:click="moveUp({{ $item->id }})" class="text-gray-600 hover:text-gray-800" title="{{ __('Move up') }}">&uarr;</button>
                                        <button wire:click="moveDown({{ $item->id }})" class="text-gray-600 hover:text-gray-800" title="{{ __('Move down') }}">&darr;</button>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        @if ($item->image_path)
                                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($item->image_path) }}" alt="" class="h-10 w-16 object-cover rounded-md shrink-0">
                                        @endif
                                        <span class="text-gray-900">{{ $item->title_en }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <button wire:click="togglePublished({{ $item->id }})" @class([
                                        'inline-flex items-center rounded-full px-2.5 py-0.5 text-sm font-medium',
                                        'bg-green-100 text-green-800' => $item->is_published,
                                        'bg-gray-100 text-gray-800' => ! $item->is_published,
                                    ])>
                                        {{ $item->is_published ? __('Published') : __('Hidden') }}
                                    </button>
                                </td>
                                <td class="px-4 py-3 text-end space-x-2 rtl:space-x-reverse">
                                    <button wire:click="edit({{ $item->id }})" class="text-indigo-600 hover:text-indigo-800 underline">{{ __('Edit') }}</button>
                                    <button wire:click="delete({{ $item->id }})" wire:confirm="{{ __('Delete this news item permanently?') }}" class="text-red-600 hover:text-red-800 underline">{{ __('Delete') }}</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-gray-800">{{ __('No news items yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
