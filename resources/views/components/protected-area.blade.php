@props(['fullscreen' => false])

<div x-data="protectedArea" {{ $attributes->merge(['class' => 'protected-area relative print:hidden']) }}>
    {{ $slot }}

    <x-watermark />

    @if ($fullscreen)
        <button type="button" x-on:click="toggleFullscreen()"
                class="absolute top-2 end-2 z-30 rounded bg-black/60 px-2 py-1 text-sm text-white hover:bg-black/80"
                title="{{ __('Full screen') }}">&#x26F6;</button>
    @endif

    <div x-show="covered" x-cloak x-on:click="covered = false"
         class="absolute inset-0 z-30 flex cursor-pointer items-center justify-center bg-white/90 p-4 text-center text-gray-800 backdrop-blur-2xl">
        {{ __('Content is hidden while this window is not active. Click here to continue.') }}
    </div>
</div>
