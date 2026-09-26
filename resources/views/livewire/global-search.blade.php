<div x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false" class="relative w-full max-w-md">
    <div class="relative">
        <span class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3 text-gray-400">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M17 10.5a6.5 6.5 0 1 1-13 0 6.5 6.5 0 0 1 13 0Z" />
            </svg>
        </span>
        <input
            type="search"
            wire:model.live.debounce.300ms="query"
            @focus="open = true"
            @input="open = true"
            placeholder="{{ __('Search pages, courses...') }}"
            autocomplete="off"
            class="block w-full rounded-lg border-gray-300 ps-9 pe-3 py-2 text-sm focus:border-brand-primary focus:ring-brand-primary shadow-sm"
        >
    </div>

    <div
        x-show="open"
        x-cloak
        x-transition.opacity.duration.100ms
        class="absolute start-0 end-0 z-50 mt-2 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-lg"
        @if (! $active) style="display: none" @endif
    >
        @if ($active)
            <div class="max-h-96 overflow-y-auto py-1 text-sm">
                @if (count($pages))
                    <div class="px-3 pt-2 pb-1 text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Pages') }}</div>
                    @foreach ($pages as $page)
                        <a href="{{ $page['route'] }}" wire:navigate @click="open = false" class="flex items-center gap-2 px-3 py-2 text-gray-700 hover:bg-gray-50">
                            <svg class="h-4 w-4 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                            </svg>
                            {{ __($page['label']) }}
                        </a>
                    @endforeach
                @endif

                @if ($courses->isNotEmpty())
                    <div class="px-3 pt-2 pb-1 text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Courses') }}</div>
                    @foreach ($courses as $course)
                        <a href="{{ route('courses.show', $course->slug) }}" wire:navigate @click="open = false" class="flex items-center gap-2 px-3 py-2 text-gray-700 hover:bg-gray-50">
                            <svg class="h-4 w-4 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                            </svg>
                            {{ $course->title }}
                        </a>
                    @endforeach
                @endif

                @if ($lecturers->isNotEmpty())
                    <div class="px-3 pt-2 pb-1 text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Lecturers') }}</div>
                    @foreach ($lecturers as $lecturer)
                        <a href="{{ route('lecturers.show', $lecturer) }}" wire:navigate @click="open = false" class="flex items-center gap-2 px-3 py-2 text-gray-700 hover:bg-gray-50">
                            <svg class="h-4 w-4 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                            </svg>
                            {{ $lecturer->name }}
                        </a>
                    @endforeach
                @endif

                @if ($users->isNotEmpty())
                    <div class="px-3 pt-2 pb-1 text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Users') }}</div>
                    @foreach ($users as $result)
                        <a href="{{ route('admin.users.index', ['search' => $result->email]) }}" @click="open = false" class="flex items-center justify-between gap-2 px-3 py-2 text-gray-700 hover:bg-gray-50">
                            <span>{{ $result->name }}</span>
                            <span class="text-xs text-gray-500" dir="ltr">{{ $result->email }}</span>
                        </a>
                    @endforeach
                @endif

                @if (! count($pages) && $courses->isEmpty() && $lecturers->isEmpty() && $users->isEmpty())
                    <p class="px-3 py-4 text-center text-gray-500">{{ __('No results found.') }}</p>
                @endif
            </div>
        @endif
    </div>
</div>
