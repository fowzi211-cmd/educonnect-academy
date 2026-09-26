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
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    {{ $editingId ? __('Edit FAQ') : __('New FAQ') }}
                </h3>

                <form wire:submit="save" class="space-y-4">
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="question_en" :value="__('Question (English)')" />
                            <x-text-input wire:model="question_en" id="question_en" class="mt-1 block w-full" />
                            <x-input-error :messages="$errors->get('question_en')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="question_ar" :value="__('Question (Arabic)')" />
                            <x-text-input wire:model="question_ar" id="question_ar" class="mt-1 block w-full" dir="rtl" />
                            <x-input-error :messages="$errors->get('question_ar')" class="mt-1" />
                        </div>
                    </div>
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="answer_en" :value="__('Answer (English)')" />
                            <textarea wire:model="answer_en" id="answer_en" rows="3" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full"></textarea>
                            <x-input-error :messages="$errors->get('answer_en')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="answer_ar" :value="__('Answer (Arabic)')" />
                            <textarea wire:model="answer_ar" id="answer_ar" rows="3" dir="rtl" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full"></textarea>
                            <x-input-error :messages="$errors->get('answer_ar')" class="mt-1" />
                        </div>
                    </div>
                    <div class="flex gap-2">
                        @if ($editingId)
                            <x-secondary-button type="button" wire:click="cancelEdit">{{ __('Cancel') }}</x-secondary-button>
                        @endif
                        <x-primary-button>{{ $editingId ? __('Update FAQ') : __('Add FAQ') }}</x-primary-button>
                    </div>
                </form>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 text-lg">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Order') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Question') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Published') }}</th>
                            <th class="px-4 py-2"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($faqs as $faq)
                            <tr wire:key="faq-{{ $faq->id }}">
                                <td class="px-4 py-3 text-gray-800">
                                    <div class="flex flex-col gap-0.5">
                                        <button wire:click="moveUp({{ $faq->id }})" class="text-gray-600 hover:text-gray-800" title="{{ __('Move up') }}">&uarr;</button>
                                        <button wire:click="moveDown({{ $faq->id }})" class="text-gray-600 hover:text-gray-800" title="{{ __('Move down') }}">&darr;</button>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-gray-900">{{ $faq->question_en }}</td>
                                <td class="px-4 py-3">
                                    <button wire:click="togglePublished({{ $faq->id }})" @class([
                                        'inline-flex items-center rounded-full px-2.5 py-0.5 text-sm font-medium',
                                        'bg-green-100 text-green-800' => $faq->is_published,
                                        'bg-gray-100 text-gray-800' => ! $faq->is_published,
                                    ])>
                                        {{ $faq->is_published ? __('Published') : __('Hidden') }}
                                    </button>
                                </td>
                                <td class="px-4 py-3 text-end space-x-2 rtl:space-x-reverse">
                                    <button wire:click="edit({{ $faq->id }})" class="text-indigo-600 hover:text-indigo-800 underline">{{ __('Edit') }}</button>
                                    <button wire:click="confirmDelete({{ $faq->id }})" class="text-red-600 hover:text-red-800 underline">{{ __('Delete') }}</button>
                                </td>
                            </tr>

                            @if ($deletingId === $faq->id)
                                <tr>
                                    <td colspan="4" class="px-4 py-3 bg-red-50">
                                        <p class="text-lg text-gray-800 mb-2">{{ __('Delete this FAQ entry permanently?') }}</p>
                                        <div class="flex gap-2">
                                            <x-danger-button wire:click="delete">{{ __('Confirm Delete') }}</x-danger-button>
                                            <x-secondary-button wire:click="$set('deletingId', null)">{{ __('Never mind') }}</x-secondary-button>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-gray-800">{{ __('No FAQ entries yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
