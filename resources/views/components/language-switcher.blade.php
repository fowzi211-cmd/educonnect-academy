@php
    $currentLocale = app()->getLocale();
    $locales = config('platform.locales');
@endphp

<div class="relative" x-data="{ open: false }" @click.outside="open = false">
    <button
        type="button"
        @click="open = !open"
        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition"
    >
        {{ $locales[$currentLocale]['native'] ?? $currentLocale }}
        <svg class="ms-1 -me-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
        </svg>
    </button>

    <div
        x-show="open"
        x-cloak
        class="absolute {{ $currentLocale === 'ar' ? 'start-0' : 'end-0' }} z-50 mt-2 w-36 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5"
    >
        <div class="py-1">
            @foreach ($locales as $code => $locale)
                <a
                    href="{{ route('locale.update', $code) }}"
                    class="block px-4 py-2 text-sm {{ $code === $currentLocale ? 'font-semibold text-gray-900' : 'text-gray-700' }} hover:bg-gray-100"
                >
                    {{ $locale['native'] }}
                </a>
            @endforeach
        </div>
    </div>
</div>
