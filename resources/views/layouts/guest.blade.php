<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ config('platform.locales.'.app()->getLocale().'.direction', 'ltr') }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ \App\Models\Setting::get('branding.platform_name', config('app.name')) }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @include('partials.brand-colors')

        <!-- Scripts -->
        @include('partials.asset-tags')
    </head>
    <body class="font-sans text-gray-900 antialiased">
        @php
            $platformName = \App\Models\Setting::get('branding.platform_name', config('app.name'));
            $logoPath = \App\Models\Setting::get('branding.logo_path');
        @endphp
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 relative overflow-hidden bg-gray-50">
            <div class="pointer-events-none absolute inset-0 bg-brand-gradient opacity-90"></div>
            <div class="pointer-events-none absolute inset-0" style="background-image: radial-gradient(circle at 20% 20%, rgba(255,255,255,0.18), transparent 45%), radial-gradient(circle at 80% 70%, rgba(255,255,255,0.12), transparent 40%);"></div>

            <div class="absolute top-4 end-4 z-10">
                <x-language-switcher />
            </div>

            <div class="relative z-10">
                <a href="/" wire:navigate class="flex items-center gap-2">
                    @if ($logoPath)
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($logoPath) }}" alt="{{ $platformName }}" class="w-28 h-28 object-contain drop-shadow-lg">
                    @else
                        <x-application-logo class="w-16 h-16 drop-shadow-lg" />
                    @endif
                </a>
            </div>

            <div class="relative z-10 w-full sm:max-w-md mt-6 px-6 py-8 bg-white shadow-xl overflow-hidden sm:rounded-2xl">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
