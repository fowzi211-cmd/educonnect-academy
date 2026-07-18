<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ config('platform.locales.'.app()->getLocale().'.direction', 'ltr') }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ? $title.' - ' : '' }}{{ \App\Models\Setting::get('branding.platform_name', config('app.name')) }}</title>
        @if ($description)
            <meta name="description" content="{{ $description }}">
        @endif

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @include('partials.asset-tags')
    </head>
    <body class="font-sans text-gray-900 antialiased">
        @php
            $platformName = \App\Models\Setting::get('branding.platform_name', config('app.name'));
        @endphp

        <div class="min-h-screen flex flex-col bg-white">
            <header class="border-b border-gray-100">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between items-center h-16">
                        <a href="{{ route('home') }}" wire:navigate class="flex items-center gap-2 font-semibold text-lg text-gray-900">
                            @if ($logo = \App\Models\Setting::get('branding.logo_path'))
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($logo) }}" alt="{{ $platformName }}" class="h-8 w-auto">
                            @endif
                            {{ $platformName }}
                        </a>

                        <nav class="hidden md:flex items-center gap-6 text-sm font-medium text-gray-600">
                            <a href="{{ route('home') }}" wire:navigate class="hover:text-gray-900 {{ request()->routeIs('home') ? 'text-gray-900' : '' }}">{{ __('Home') }}</a>
                            <a href="{{ route('about') }}" wire:navigate class="hover:text-gray-900 {{ request()->routeIs('about') ? 'text-gray-900' : '' }}">{{ __('About') }}</a>
                            <a href="{{ route('how-it-works') }}" wire:navigate class="hover:text-gray-900 {{ request()->routeIs('how-it-works') ? 'text-gray-900' : '' }}">{{ __('How It Works') }}</a>
                            <a href="{{ route('faq') }}" wire:navigate class="hover:text-gray-900 {{ request()->routeIs('faq') ? 'text-gray-900' : '' }}">{{ __('FAQ') }}</a>
                            <a href="{{ route('contact') }}" wire:navigate class="hover:text-gray-900 {{ request()->routeIs('contact') ? 'text-gray-900' : '' }}">{{ __('Contact') }}</a>
                        </nav>

                        <div class="flex items-center gap-3">
                            <x-language-switcher />

                            @auth
                                <a href="{{ route('dashboard') }}" wire:navigate class="rounded-md px-3 py-2 text-sm font-medium text-white bg-gray-900 hover:bg-gray-700">
                                    {{ __('Dashboard') }}
                                </a>
                            @else
                                <a href="{{ route('login') }}" wire:navigate class="hidden sm:inline text-sm font-medium text-gray-600 hover:text-gray-900">
                                    {{ __('Log in') }}
                                </a>
                                <a href="{{ route('register') }}" wire:navigate class="rounded-md px-3 py-2 text-sm font-medium text-white bg-gray-900 hover:bg-gray-700">
                                    {{ __('Register') }}
                                </a>
                            @endauth
                        </div>
                    </div>
                </div>
            </header>

            <main class="flex-1">
                {{ $slot }}
            </main>

            <footer class="border-t border-gray-100 bg-gray-50">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 grid grid-cols-1 sm:grid-cols-3 gap-8">
                    <div>
                        <div class="font-semibold text-gray-900">{{ $platformName }}</div>
                        <p class="mt-2 text-sm text-gray-600">{{ __('Live and recorded courses connecting students with lecturers, built for a bilingual Arabic/English audience.') }}</p>
                    </div>

                    <div>
                        <div class="font-semibold text-gray-900 text-sm">{{ __('Contact') }}</div>
                        <ul class="mt-2 space-y-1 text-sm text-gray-600">
                            @if ($email = \App\Models\Setting::get('branding.contact_email'))
                                <li><a href="mailto:{{ $email }}" class="hover:text-gray-900">{{ $email }}</a></li>
                            @endif
                            @if ($phone = \App\Models\Setting::get('branding.contact_phone'))
                                <li><a href="tel:{{ $phone }}" class="hover:text-gray-900">{{ $phone }}</a></li>
                            @endif
                        </ul>
                    </div>

                    <div>
                        <div class="font-semibold text-gray-900 text-sm">{{ __('Policies') }}</div>
                        <ul class="mt-2 space-y-1 text-sm text-gray-600">
                            <li><a href="{{ route('terms') }}" wire:navigate class="hover:text-gray-900">{{ __('Terms and Conditions') }}</a></li>
                            <li><a href="{{ route('privacy') }}" wire:navigate class="hover:text-gray-900">{{ __('Privacy Policy') }}</a></li>
                            <li><a href="{{ route('refund-policy') }}" wire:navigate class="hover:text-gray-900">{{ __('Refund and Cancellation Policy') }}</a></li>
                            <li><a href="{{ route('cookie-policy') }}" wire:navigate class="hover:text-gray-900">{{ __('Cookie Policy') }}</a></li>
                        </ul>
                    </div>
                </div>

                <div class="border-t border-gray-100 py-4 text-center text-xs text-gray-500">
                    &copy; {{ now()->year }} {{ $platformName }}. {{ __('All rights reserved.') }}
                </div>
            </footer>
        </div>
    </body>
</html>
