<div wire:poll.30s>
    <x-dropdown align="right" width="80">
        <x-slot name="trigger">
            <button class="relative inline-flex items-center p-2 rounded-md text-gray-600 hover:text-gray-700 focus:outline-none">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                </svg>
                @if ($unreadCount > 0)
                    <span class="absolute top-0 end-0 inline-flex items-center justify-center h-4 w-4 rounded-full bg-red-500 text-white text-[10px] font-medium">
                        {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                    </span>
                @endif
            </button>
        </x-slot>

        <x-slot name="content">
            <div class="max-h-96 overflow-y-auto">
                <div class="flex items-center justify-between px-4 py-2 border-b border-gray-100">
                    <span class="text-sm font-medium text-gray-700">{{ __('Notifications') }}</span>
                    @if ($unreadCount > 0)
                        <button wire:click="markAllAsRead" class="text-xs text-indigo-600 hover:text-indigo-800">{{ __('Mark all as read') }}</button>
                    @endif
                </div>

                @forelse ($notifications as $notification)
                    <a href="{{ $notification->data['url'] ?? '#' }}" wire:navigate
                        wire:click="markAsRead('{{ $notification->id }}')"
                        @class([
                            'block px-4 py-3 text-sm border-b border-gray-50 hover:bg-gray-50',
                            'bg-indigo-50' => is_null($notification->read_at),
                        ])>
                        <p class="text-gray-700">{{ $notification->data['message'] ?? '' }}</p>
                        <p class="mt-1 text-xs text-gray-500">{{ $notification->created_at->diffForHumans() }}</p>
                    </a>
                @empty
                    <p class="px-4 py-6 text-sm text-center text-gray-600">{{ __('No notifications yet.') }}</p>
                @endforelse
            </div>
        </x-slot>
    </x-dropdown>
</div>
