<div>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Discussions') }}: {{ $course->title }}
            </h2>
            <a href="{{ route('my-courses.classroom', $course) }}" wire:navigate class="text-sm text-gray-600 hover:text-gray-900 underline">
                {{ __('Back to classroom') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if ($showForm)
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <h3 class="font-medium text-gray-900 mb-4">{{ __('New Discussion') }}</h3>
                    <form wire:submit="create" class="space-y-4">
                        <div>
                            <x-input-label for="d_title" :value="__('Title')" />
                            <x-text-input wire:model="title" id="d_title" class="block mt-1 w-full" />
                            <x-input-error :messages="$errors->get('title')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="d_lesson" :value="__('Related Lesson (optional)')" />
                            <select wire:model="lessonId" id="d_lesson" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full">
                                <option value="">{{ __('General discussion') }}</option>
                                @foreach ($course->sections as $section)
                                    @foreach ($section->lessons as $lesson)
                                        <option value="{{ $lesson->id }}">{{ $lesson->title }}</option>
                                    @endforeach
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <x-input-label for="d_body" :value="__('Message')" />
                            <textarea wire:model="body" id="d_body" rows="4"
                                class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full"></textarea>
                            <x-input-error :messages="$errors->get('body')" class="mt-2" />
                        </div>

                        <div class="flex gap-2">
                            <x-primary-button>{{ __('Post') }}</x-primary-button>
                            <x-secondary-button type="button" wire:click="cancelForm">{{ __('Cancel') }}</x-secondary-button>
                        </div>
                    </form>
                </div>
            @else
                <div class="flex justify-end">
                    <x-primary-button wire:click="newForm">{{ __('New Discussion') }}</x-primary-button>
                </div>
            @endif

            <div class="space-y-3">
                @forelse ($threads as $thread)
                    <a href="{{ route('courses.discussions.show', $thread) }}" wire:navigate class="block bg-white shadow-sm sm:rounded-lg p-5 hover:bg-gray-50">
                        <div class="flex items-center gap-2">
                            @if ($thread->is_pinned)
                                <span class="text-amber-500">&#9733;</span>
                            @endif
                            @if ($thread->is_locked)
                                <span class="text-gray-500" title="{{ __('Locked') }}">&#128274;</span>
                            @endif
                            @if ($thread->is_hidden)
                                <span class="text-xs text-red-500">({{ __('Hidden') }})</span>
                            @endif
                            <h4 class="font-medium text-gray-900">{{ $thread->title }}</h4>
                        </div>
                        <p class="mt-1 text-sm text-gray-600 line-clamp-2">{{ $thread->body }}</p>
                        <p class="mt-2 text-xs text-gray-500">
                            {{ $thread->user->name }} &middot; {{ $thread->created_at->format('Y-m-d H:i') }}
                            @if ($thread->lesson)
                                &middot; {{ __('Lesson') }}: {{ $thread->lesson->title }}
                            @endif
                            &middot; {{ __(':n replies', ['n' => $thread->replies->count()]) }}
                        </p>
                    </a>
                @empty
                    <p class="text-center text-gray-600 py-6">{{ __('No discussions yet. Start one!') }}</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
