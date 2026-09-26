<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Content Management') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @include('livewire.admin._content-nav')

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 text-lg">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Page') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Last Updated') }}</th>
                            <th class="px-4 py-2"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($pages as $page)
                            <tr wire:key="page-{{ $page->id }}">
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-900">{{ $page->title_en }}</div>
                                    <div class="text-gray-800 font-mono text-sm">/{{ $page->slug }}</div>
                                </td>
                                <td class="px-4 py-3 text-gray-800">
                                    {{ $page->updated_at->format('Y-m-d H:i') }}
                                    @if ($page->updatedBy)
                                        &middot; {{ $page->updatedBy->name }}
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-end">
                                    <button wire:click="edit({{ $page->id }})" class="text-indigo-600 hover:text-indigo-800 underline">{{ __('Edit') }}</button>
                                </td>
                            </tr>

                            @if ($editingId === $page->id)
                                <tr>
                                    <td colspan="3" class="px-4 py-4 bg-gray-50">
                                        @if ($saved)
                                            <div class="mb-4 rounded-md bg-green-50 px-4 py-3 text-lg text-green-700">
                                                {{ __('Page saved.') }}
                                            </div>
                                        @endif

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
                                                    <x-input-label for="body_en" :value="__('Body (English HTML)')" />
                                                    <textarea wire:model="body_en" id="body_en" rows="14" class="font-mono text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full"></textarea>
                                                    <x-input-error :messages="$errors->get('body_en')" class="mt-1" />
                                                </div>
                                                <div>
                                                    <x-input-label for="body_ar" :value="__('Body (Arabic HTML)')" />
                                                    <textarea wire:model="body_ar" id="body_ar" rows="14" dir="rtl" class="font-mono text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full"></textarea>
                                                    <x-input-error :messages="$errors->get('body_ar')" class="mt-1" />
                                                </div>
                                            </div>
                                            <p class="text-sm text-gray-800">{{ __('Body accepts basic HTML (paragraphs, headings, links).') }}</p>
                                            <div class="flex gap-2">
                                                <x-primary-button>{{ __('Save') }}</x-primary-button>
                                                <x-secondary-button type="button" wire:click="cancelEdit">{{ __('Cancel') }}</x-secondary-button>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
