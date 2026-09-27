<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ config('platform.locales.'.app()->getLocale().'.direction', 'ltr') }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ ($title ?? null) ? $title.' - ' : '' }}{{ \App\Models\Setting::get('branding.platform_name', config('app.name')) }}</title>
        @if ($description ?? null)
            <meta name="description" content="{{ $description }}">
        @endif

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        @include('partials.brand-colors')

        <!-- Scripts -->
        @include('partials.asset-tags')
    </head>
    <body class="font-sans text-gray-900 antialiased">
        @php
            $platformName = \App\Models\Setting::get('branding.platform_name', config('app.name'));
        @endphp

        <div class="min-h-screen flex flex-col bg-white">
            <header class="sticky top-0 z-40 bg-white/90 backdrop-blur border-b border-gray-100">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between items-center h-16">
                        <a href="{{ route('home') }}" wire:navigate class="flex items-center gap-2.5 font-bold text-lg text-gray-900 shrink-0">
                            <img src="{{ \App\Models\Setting::logoUrl() }}" alt="{{ $platformName }}" class="h-12 w-auto">
                            <span class="hidden sm:inline">{{ $platformName }}</span>
                        </a>

                        <nav class="hidden md:flex items-center gap-1 text-sm font-medium text-gray-600">
                            <a href="{{ route('home') }}" wire:navigate @class(['px-3 py-2 rounded-md hover:text-gray-900 hover:bg-gray-50 transition', 'text-brand-primary font-semibold' => request()->routeIs('home')])>{{ __('Home') }}</a>
                            <a href="{{ route('courses.index') }}" wire:navigate @class(['px-3 py-2 rounded-md hover:text-gray-900 hover:bg-gray-50 transition', 'text-brand-primary font-semibold' => request()->routeIs('courses.*')])>{{ __('Courses') }}</a>
                            <a href="{{ route('lecturers.index') }}" wire:navigate @class(['px-3 py-2 rounded-md hover:text-gray-900 hover:bg-gray-50 transition', 'text-brand-primary font-semibold' => request()->routeIs('lecturers.*')])>{{ __('Lecturers') }}</a>
                            <a href="{{ route('about') }}" wire:navigate @class(['px-3 py-2 rounded-md hover:text-gray-900 hover:bg-gray-50 transition', 'text-brand-primary font-semibold' => request()->routeIs('about')])>{{ __('About') }}</a>
                            <a href="{{ route('how-it-works') }}" wire:navigate @class(['px-3 py-2 rounded-md hover:text-gray-900 hover:bg-gray-50 transition', 'text-brand-primary font-semibold' => request()->routeIs('how-it-works')])>{{ __('How It Works') }}</a>
                            <a href="{{ route('assessments') }}" wire:navigate @class(['px-3 py-2 rounded-md hover:text-gray-900 hover:bg-gray-50 transition', 'text-brand-primary font-semibold' => request()->routeIs('assessments')])>{{ __('Assessments') }}</a>
                            <a href="{{ route('faq') }}" wire:navigate @class(['px-3 py-2 rounded-md hover:text-gray-900 hover:bg-gray-50 transition', 'text-brand-primary font-semibold' => request()->routeIs('faq')])>{{ __('FAQ') }}</a>
                            <a href="{{ route('contact') }}" wire:navigate @class(['px-3 py-2 rounded-md hover:text-gray-900 hover:bg-gray-50 transition', 'text-brand-primary font-semibold' => request()->routeIs('contact')])>{{ __('Contact') }}</a>
                        </nav>

                        <div class="flex items-center gap-3">
                            <div class="hidden lg:block w-56">
                                <livewire:global-search key="public-desktop-search" />
                            </div>

                            <x-language-switcher />

                            @auth
                                <a href="{{ route('dashboard') }}" wire:navigate class="rounded-md px-4 py-2 text-sm font-semibold text-white btn-brand shadow-sm">
                                    {{ __('Dashboard') }}
                                </a>
                            @else
                                <a href="{{ route('login') }}" wire:navigate class="hidden sm:inline text-sm font-medium text-gray-600 hover:text-gray-900">
                                    {{ __('Log in') }}
                                </a>
                                <a href="{{ route('register') }}" wire:navigate class="rounded-md px-4 py-2 text-sm font-semibold text-white btn-brand shadow-sm">
                                    {{ __('Register') }}
                                </a>
                            @endauth
                        </div>
                    </div>

                    <div class="lg:hidden pb-3">
                        <livewire:global-search key="public-mobile-search" />
                    </div>

                    <nav class="md:hidden flex items-center gap-3 overflow-x-auto text-sm font-medium text-gray-600 pb-3 -mt-1">
                        <a href="{{ route('home') }}" wire:navigate class="whitespace-nowrap {{ request()->routeIs('home') ? 'text-brand-primary font-semibold' : '' }}">{{ __('Home') }}</a>
                        <a href="{{ route('courses.index') }}" wire:navigate class="whitespace-nowrap {{ request()->routeIs('courses.*') ? 'text-brand-primary font-semibold' : '' }}">{{ __('Courses') }}</a>
                        <a href="{{ route('lecturers.index') }}" wire:navigate class="whitespace-nowrap {{ request()->routeIs('lecturers.*') ? 'text-brand-primary font-semibold' : '' }}">{{ __('Lecturers') }}</a>
                        <a href="{{ route('about') }}" wire:navigate class="whitespace-nowrap {{ request()->routeIs('about') ? 'text-brand-primary font-semibold' : '' }}">{{ __('About') }}</a>
                        <a href="{{ route('how-it-works') }}" wire:navigate class="whitespace-nowrap {{ request()->routeIs('how-it-works') ? 'text-brand-primary font-semibold' : '' }}">{{ __('How It Works') }}</a>
                        <a href="{{ route('assessments') }}" wire:navigate class="whitespace-nowrap {{ request()->routeIs('assessments') ? 'text-brand-primary font-semibold' : '' }}">{{ __('Assessments') }}</a>
                        <a href="{{ route('faq') }}" wire:navigate class="whitespace-nowrap {{ request()->routeIs('faq') ? 'text-brand-primary font-semibold' : '' }}">{{ __('FAQ') }}</a>
                        <a href="{{ route('contact') }}" wire:navigate class="whitespace-nowrap {{ request()->routeIs('contact') ? 'text-brand-primary font-semibold' : '' }}">{{ __('Contact') }}</a>
                    </nav>
                </div>
            </header>

            <main class="flex-1">
                {{ $slot }}
            </main>

            <footer class="bg-gray-900 text-gray-300">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-10">
                    <div class="lg:col-span-2">
                        <a href="{{ route('home') }}" wire:navigate class="flex items-center gap-2.5 font-bold text-lg text-white">
                            <img src="{{ \App\Models\Setting::logoUrl() }}" alt="{{ $platformName }}" class="h-11 w-auto object-contain">
                            {{ $platformName }}
                        </a>
                        <p class="mt-3 text-sm text-gray-400 max-w-sm">{{ __('Live and recorded courses connecting students with lecturers, built for a bilingual Arabic/English audience.') }}</p>
                    </div>

                    <div>
                        <div class="font-semibold text-white text-sm">{{ __('Explore') }}</div>
                        <ul class="mt-3 space-y-2 text-sm">
                            <li><a href="{{ route('courses.index') }}" wire:navigate class="hover:text-white transition">{{ __('Courses') }}</a></li>
                            <li><a href="{{ route('lecturers.index') }}" wire:navigate class="hover:text-white transition">{{ __('Lecturers') }}</a></li>
                            <li><a href="{{ route('how-it-works') }}" wire:navigate class="hover:text-white transition">{{ __('How It Works') }}</a></li>
                            <li><a href="{{ route('assessments') }}" wire:navigate class="hover:text-white transition">{{ __('Assessments') }}</a></li>
                            <li><a href="{{ route('faq') }}" wire:navigate class="hover:text-white transition">{{ __('FAQ') }}</a></li>
                            <li><a href="{{ route('contact') }}" wire:navigate class="hover:text-white transition">{{ __('Contact') }}</a></li>
                        </ul>
                    </div>

                    <div>
                        <div class="font-semibold text-white text-sm">{{ __('Legal') }}</div>
                        <ul class="mt-3 space-y-2 text-sm">
                            <li><a href="{{ route('terms') }}" wire:navigate class="hover:text-white transition">{{ __('Terms and Conditions') }}</a></li>
                            <li><a href="{{ route('privacy') }}" wire:navigate class="hover:text-white transition">{{ __('Privacy Policy') }}</a></li>
                            <li><a href="{{ route('refund-policy') }}" wire:navigate class="hover:text-white transition">{{ __('Refund and Cancellation Policy') }}</a></li>
                            <li><a href="{{ route('cookie-policy') }}" wire:navigate class="hover:text-white transition">{{ __('Cookie Policy') }}</a></li>
                        </ul>
                    </div>
                </div>

                @if (\App\Models\Setting::get('branding.contact_email') || \App\Models\Setting::get('branding.contact_phone'))
                    <div class="border-t border-white/10">
                        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex flex-wrap gap-x-6 gap-y-2 text-sm text-gray-400">
                            @if ($email = \App\Models\Setting::get('branding.contact_email'))
                                <a href="mailto:{{ $email }}" class="hover:text-white transition">{{ $email }}</a>
                            @endif
                            @if ($phone = \App\Models\Setting::get('branding.contact_phone'))
                                <a href="tel:{{ $phone }}" class="hover:text-white transition">{{ $phone }}</a>
                            @endif
                        </div>
                    </div>
                @endif

                <div class="border-t border-white/10 py-5 text-center text-xs text-gray-500">
                    &copy; {{ now()->year }} {{ $platformName }}. {{ __('All rights reserved.') }}
                </div>
            </footer>
        </div>
    </body>
</html>
