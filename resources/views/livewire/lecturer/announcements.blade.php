<div>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Announcements') }}: {{ $course->title }}
            </h2>
            <a href="{{ route('lecturer.courses.index') }}" wire:navigate class="text-lg text-gray-800 hover:text-gray-900 underline">
                {{ __('Back to my courses') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if ($showForm)
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <h3 class="font-medium text-gray-900 mb-4">{{ $editingId ? __('Edit Announcement') : __('New Announcement') }}</h3>
                    <form wire:submit="save" class="space-y-4">
                        <div>
                            <x-input-label for="an_title" :value="__('Title')" />
                            <x-text-input wire:model="title" id="an_title" class="block mt-1 w-full" />
                            <x-input-error :messages="$errors->get('title')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="an_body" :value="__('Message')" />
                            <textarea wire:model="body" id="an_body" rows="4"
                                class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full"></textarea>
                            <x-input-error :messages="$errors->get('body')" class="mt-2" />
                        </div>

                        <label class="flex items-center gap-2 text-lg text-gray-800">
                            <input type="checkbox" wire:model="is_pinned" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            {{ __('Pin to top') }}
                        </label>

                        <div class="flex gap-2">
                            <x-primary-button>{{ __('Save') }}</x-primary-button>
                            <x-secondary-button type="button" wire:click="cancelForm">{{ __('Cancel') }}</x-secondary-button>
                        </div>
                    </form>
                </div>
            @else
                <div class="flex justify-end">
                    <x-primary-button wire:click="newForm">{{ __('New Announcement') }}</x-primary-button>
                </div>
            @endif

            <div class="space-y-3">
                @forelse ($announcements as $announcement)
                    <div class="bg-white shadow-sm sm:rounded-lg p-5" wire:key="an-{{ $announcement->id }}">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <div class="flex items-center gap-2">
                                    @if ($announcement->is_pinned)
                                        <span class="text-amber-500" title="{{ __('Pinned') }}">&#9733;</span>
                                    @endif
                                    <h4 class="font-medium text-gray-900">{{ $announcement->title }}</h4>
                                    <span @class([
                                        'inline-flex items-center rounded-full px-2 py-0.5 text-sm font-medium',
                                        'bg-green-100 text-green-800' => $announcement->published_at,
                                        'bg-gray-100 text-gray-800' => ! $announcement->published_at,
                                    ])>
                                        {{ $announcement->published_at ? __('Published') : __('Draft') }}
                                    </span>
                                </div>
                                <p class="mt-1 text-lg text-gray-800 whitespace-pre-line">{{ $announcement->body }}</p>
                                <p class="mt-2 text-sm text-gray-600">{{ $announcement->created_at->format('Y-m-d H:i') }}</p>
                            </div>
                            <div class="flex gap-2 text-lg shrink-0">
                                <button wire:click="edit({{ $announcement->id }})" class="text-indigo-600 hover:text-indigo-800 underline">{{ __('Edit') }}</button>
                                <button wire:click="togglePublish({{ $announcement->id }})" class="text-gray-800 hover:text-gray-800 underline">
                                    {{ $announcement->published_at ? __('Unpublish') : __('Publish') }}
                                </button>
                                <button wire:click="delete({{ $announcement->id }})" wire:confirm="{{ __('Delete this announcement?') }}" class="text-red-600 hover:text-red-800 underline">{{ __('Delete') }}</button>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-center text-gray-800 py-6">{{ __('No announcements yet.') }}</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
