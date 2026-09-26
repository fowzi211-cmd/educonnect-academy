<div>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $thread->title }}
            </h2>
            <a href="{{ route('courses.discussions.index', $thread->course) }}" wire:navigate class="text-sm text-gray-600 hover:text-gray-900 underline">
                {{ __('Back to discussions') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if ($thread->is_hidden)
                <div class="bg-red-50 border border-red-200 text-red-800 text-sm rounded-lg p-3">
                    {{ __('This thread is hidden from students.') }}
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-600">
                        {{ $thread->user->name }} &middot; {{ $thread->created_at->format('Y-m-d H:i') }}
                        @if ($thread->lesson)
                            &middot; {{ __('Lesson') }}: {{ $thread->lesson->title }}
                        @endif
                    </div>
                    @if ($isModerator)
                        <div class="flex gap-2 text-xs">
                            <button wire:click="togglePin" class="text-gray-600 hover:text-gray-800 underline">{{ $thread->is_pinned ? __('Unpin') : __('Pin') }}</button>
                            <button wire:click="toggleLock" class="text-gray-600 hover:text-gray-800 underline">{{ $thread->is_locked ? __('Unlock') : __('Lock') }}</button>
                            <button wire:click="toggleHideThread" class="text-gray-600 hover:text-gray-800 underline">{{ $thread->is_hidden ? __('Unhide') : __('Hide') }}</button>
                        </div>
                    @endif
                </div>
                <p class="mt-4 text-gray-700 whitespace-pre-line">{{ $thread->body }}</p>
            </div>

            <div class="space-y-3">
                @foreach ($replies as $reply)
                    <div @class(['bg-white shadow-sm sm:rounded-lg p-4', 'opacity-50' => $reply->is_hidden])>
                        <div class="flex items-center justify-between">
                            <div class="text-xs text-gray-600">
                                {{ $reply->user->name }} &middot; {{ $reply->created_at->format('Y-m-d H:i') }}
                                @if ($reply->is_hidden)
                                    &middot; <span class="text-red-500">{{ __('Hidden') }}</span>
                                @endif
                            </div>
                            @if ($isModerator)
                                <button wire:click="toggleHideReply({{ $reply->id }})" class="text-xs text-gray-600 hover:text-gray-800 underline">
                                    {{ $reply->is_hidden ? __('Unhide') : __('Hide') }}
                                </button>
                            @endif
                        </div>
                        <p class="mt-2 text-sm text-gray-700 whitespace-pre-line">{{ $reply->body }}</p>
                    </div>
                @endforeach
            </div>

            @if ($thread->is_locked)
                <p class="text-sm text-gray-600 italic">{{ __('This discussion is locked and no longer accepting replies.') }}</p>
            @else
                <div class="bg-white shadow-sm sm:rounded-lg p-4">
                    <form wire:submit="reply" class="space-y-3">
                        <textarea wire:model="replyBody" rows="3" placeholder="{{ __('Write a reply...') }}"
                            class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full"></textarea>
                        <x-input-error :messages="$errors->get('replyBody')" class="mt-1" />
                        <x-primary-button>{{ __('Reply') }}</x-primary-button>
                    </form>
                </div>
            @endif
        </div>
    </div>
</div>
