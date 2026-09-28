<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component
{
    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Top row: logo, personal-details menu, hamburger (mobile only) -->
        <div class="flex justify-between items-center h-16">
            <!-- Logo -->
            <div class="shrink-0 flex items-center">
                <a href="{{ route('dashboard') }}" wire:navigate>
                    <img src="{{ \App\Models\Setting::logoUrl() }}" alt="{{ \App\Models\Setting::get('branding.platform_name', config('app.name')) }}" class="block h-12 w-auto object-contain">
                </a>
            </div>

            <!-- Global search -->
            <div class="hidden sm:block flex-1 max-w-md mx-6">
                <livewire:global-search key="desktop-search" />
            </div>

            <!-- Personal details / profile menu -->
            <div class="hidden sm:flex sm:items-center gap-2">
                <livewire:notification-bell />

                <x-language-switcher />

                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-600 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name" x-on:profile-updated.window="name = $event.detail.name"></div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile')" wire:navigate>
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <button wire:click="logout" class="w-full text-start">
                            <x-dropdown-link>
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </button>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-500 hover:text-gray-600 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-600 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Second row: all navigation links, always visible, wraps onto extra lines when needed -->
        <div class="hidden sm:flex sm:flex-wrap sm:items-center gap-x-6 gap-y-1 pb-3">
            <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" wire:navigate>
                {{ __('Dashboard') }}
            </x-nav-link>

            <x-nav-link :href="route('home')" :active="request()->routeIs('home')" wire:navigate>
                {{ __('Home') }}
            </x-nav-link>

            <x-nav-link :href="route('support-tickets.index')" :active="request()->routeIs('support-tickets.*')" wire:navigate>
                {{ __('Support') }}
            </x-nav-link>

            @if (auth()->user()->hasRole('student'))
                <x-nav-link :href="route('my-courses.index')" :active="request()->routeIs('my-courses.*')" wire:navigate>
                    {{ __('My Learning') }}
                </x-nav-link>
                <x-nav-link :href="route('my-assessments.index')" :active="request()->routeIs('my-assessments.*')" wire:navigate>
                    {{ __('My Assessments') }}
                </x-nav-link>
                <x-nav-link :href="route('my-schedule.index')" :active="request()->routeIs('my-schedule.*')" wire:navigate>
                    {{ __('My Schedule') }}
                </x-nav-link>
                <x-nav-link :href="route('my-subscriptions.index')" :active="request()->routeIs('my-subscriptions.*')" wire:navigate>
                    {{ __('My Subscriptions') }}
                </x-nav-link>
                <x-nav-link :href="route('my-certificates.index')" :active="request()->routeIs('my-certificates.*')" wire:navigate>
                    {{ __('My Certificates') }}
                </x-nav-link>
            @endif

            @if (auth()->user()->lecturerProfile?->status !== \App\Models\LecturerProfile::STATUS_APPROVED)
                <x-nav-link :href="route('lecturer-application')" :active="request()->routeIs('lecturer-application')" wire:navigate>
                    {{ auth()->user()->can('manage own courses') ? __('Teaching Profile') : __('Become a Lecturer') }}
                </x-nav-link>
            @endif

            @can('manage own courses')
                <x-nav-link :href="route('lecturer.courses.index')" :active="request()->routeIs('lecturer.courses.*')" wire:navigate>
                    {{ __('My Courses') }}
                </x-nav-link>
                <x-nav-link :href="route('lecturer.earnings')" :active="request()->routeIs('lecturer.earnings')" wire:navigate>
                    {{ __('My Earnings') }}
                </x-nav-link>
            @endcan

            @can('review courses')
                <x-nav-link :href="route('admin.courses.index')" :active="request()->routeIs('admin.courses.*')" wire:navigate>
                    {{ __('Course Reviews') }}
                </x-nav-link>
            @endcan

            @can('manage lecturer applications')
                <x-nav-link :href="route('admin.lecturer-applications.index')" :active="request()->routeIs('admin.lecturer-applications.*')" wire:navigate>
                    {{ __('Lecturer Applications') }}
                </x-nav-link>
            @endcan

            @can('manage categories')
                <x-nav-link :href="route('admin.categories')" :active="request()->routeIs('admin.categories')" wire:navigate>
                    {{ __('Categories') }}
                </x-nav-link>

                <x-nav-link :href="route('admin.lecture-rooms')" :active="request()->routeIs('admin.lecture-rooms')" wire:navigate>
                    {{ __('Lecture Rooms') }}
                </x-nav-link>
            @endcan

            @can('manage enrolments')
                <x-nav-link :href="route('admin.enrolments')" :active="request()->routeIs('admin.enrolments')" wire:navigate>
                    {{ __('Enrolments') }}
                </x-nav-link>
            @endcan

            @can('manage finances')
                <x-nav-link :href="route('admin.finance.transactions')" :active="request()->routeIs('admin.finance.*')" wire:navigate>
                    {{ __('Finance') }}
                </x-nav-link>
            @endcan

            @can('manage certificates')
                <x-nav-link :href="route('admin.certificates')" :active="request()->routeIs('admin.certificates')" wire:navigate>
                    {{ __('Certificates') }}
                </x-nav-link>
            @endcan

            @can('manage support tickets')
                <x-nav-link :href="route('admin.support-tickets.index')" :active="request()->routeIs('admin.support-tickets.*')" wire:navigate>
                    {{ __('Support Tickets') }}
                </x-nav-link>
            @endcan

            @can('view reports')
                <x-nav-link :href="route('admin.reports')" :active="request()->routeIs('admin.reports')" wire:navigate>
                    {{ __('Reports') }}
                </x-nav-link>
            @endcan

            @can('manage content')
                <x-nav-link :href="route('admin.pages')" :active="request()->routeIs('admin.pages') || request()->routeIs('admin.faqs')" wire:navigate>
                    {{ __('Content') }}
                </x-nav-link>
            @endcan

            @can('view audit logs')
                <x-nav-link :href="route('admin.audit-logs')" :active="request()->routeIs('admin.audit-logs')" wire:navigate>
                    {{ __('Audit Logs') }}
                </x-nav-link>
            @endcan

            @can('manage users')
                <x-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.index')" wire:navigate>
                    {{ __('Users') }}
                </x-nav-link>
            @endcan

            @can('manage settings')
                <x-nav-link :href="route('admin.settings')" :active="request()->routeIs('admin.settings')" wire:navigate>
                    {{ __('Settings') }}
                </x-nav-link>
            @endcan
        </div>
    </div>

    <!-- Responsive Navigation Menu (mobile only) -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="px-3 pt-3">
            <livewire:global-search key="mobile-search" />
        </div>

        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" wire:navigate>
                {{ __('Dashboard') }}
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('home')" :active="request()->routeIs('home')" wire:navigate>
                {{ __('Home') }}
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('support-tickets.index')" :active="request()->routeIs('support-tickets.*')" wire:navigate>
                {{ __('Support') }}
            </x-responsive-nav-link>

            @if (auth()->user()->hasRole('student'))
                <x-responsive-nav-link :href="route('my-courses.index')" :active="request()->routeIs('my-courses.*')" wire:navigate>
                    {{ __('My Learning') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('my-assessments.index')" :active="request()->routeIs('my-assessments.*')" wire:navigate>
                    {{ __('My Assessments') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('my-schedule.index')" :active="request()->routeIs('my-schedule.*')" wire:navigate>
                    {{ __('My Schedule') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('my-subscriptions.index')" :active="request()->routeIs('my-subscriptions.*')" wire:navigate>
                    {{ __('My Subscriptions') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('my-certificates.index')" :active="request()->routeIs('my-certificates.*')" wire:navigate>
                    {{ __('My Certificates') }}
                </x-responsive-nav-link>
            @endif

            @if (auth()->user()->lecturerProfile?->status !== \App\Models\LecturerProfile::STATUS_APPROVED)
                <x-responsive-nav-link :href="route('lecturer-application')" :active="request()->routeIs('lecturer-application')" wire:navigate>
                    {{ auth()->user()->can('manage own courses') ? __('Teaching Profile') : __('Become a Lecturer') }}
                </x-responsive-nav-link>
            @endif

            @can('manage own courses')
                <x-responsive-nav-link :href="route('lecturer.courses.index')" :active="request()->routeIs('lecturer.courses.*')" wire:navigate>
                    {{ __('My Courses') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('lecturer.earnings')" :active="request()->routeIs('lecturer.earnings')" wire:navigate>
                    {{ __('My Earnings') }}
                </x-responsive-nav-link>
            @endcan

            @canany(['review courses', 'manage lecturer applications', 'manage categories', 'manage enrolments', 'manage finances', 'manage certificates', 'manage support tickets', 'view reports', 'manage content', 'view audit logs', 'manage settings'])
                <div class="px-3 pt-3 pb-1 text-xs font-semibold text-gray-600 uppercase tracking-wide border-t border-gray-200 mt-2">
                    {{ __('Admin') }}
                </div>
            @endcanany

            @can('review courses')
                <x-responsive-nav-link :href="route('admin.courses.index')" :active="request()->routeIs('admin.courses.*')" wire:navigate>
                    {{ __('Course Reviews') }}
                </x-responsive-nav-link>
            @endcan

            @can('manage lecturer applications')
                <x-responsive-nav-link :href="route('admin.lecturer-applications.index')" :active="request()->routeIs('admin.lecturer-applications.*')" wire:navigate>
                    {{ __('Lecturer Applications') }}
                </x-responsive-nav-link>
            @endcan

            @can('manage categories')
                <x-responsive-nav-link :href="route('admin.categories')" :active="request()->routeIs('admin.categories')" wire:navigate>
                    {{ __('Categories') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('admin.lecture-rooms')" :active="request()->routeIs('admin.lecture-rooms')" wire:navigate>
                    {{ __('Lecture Rooms') }}
                </x-responsive-nav-link>
            @endcan

            @can('manage enrolments')
                <x-responsive-nav-link :href="route('admin.enrolments')" :active="request()->routeIs('admin.enrolments')" wire:navigate>
                    {{ __('Enrolments') }}
                </x-responsive-nav-link>
            @endcan

            @can('manage finances')
                <x-responsive-nav-link :href="route('admin.finance.transactions')" :active="request()->routeIs('admin.finance.*')" wire:navigate>
                    {{ __('Finance') }}
                </x-responsive-nav-link>
            @endcan

            @can('manage certificates')
                <x-responsive-nav-link :href="route('admin.certificates')" :active="request()->routeIs('admin.certificates')" wire:navigate>
                    {{ __('Certificates') }}
                </x-responsive-nav-link>
            @endcan

            @can('manage support tickets')
                <x-responsive-nav-link :href="route('admin.support-tickets.index')" :active="request()->routeIs('admin.support-tickets.*')" wire:navigate>
                    {{ __('Support Tickets') }}
                </x-responsive-nav-link>
            @endcan

            @can('view reports')
                <x-responsive-nav-link :href="route('admin.reports')" :active="request()->routeIs('admin.reports')" wire:navigate>
                    {{ __('Reports') }}
                </x-responsive-nav-link>
            @endcan

            @can('manage content')
                <x-responsive-nav-link :href="route('admin.pages')" :active="request()->routeIs('admin.pages') || request()->routeIs('admin.faqs')" wire:navigate>
                    {{ __('Content') }}
                </x-responsive-nav-link>
            @endcan

            @can('view audit logs')
                <x-responsive-nav-link :href="route('admin.audit-logs')" :active="request()->routeIs('admin.audit-logs')" wire:navigate>
                    {{ __('Audit Logs') }}
                </x-responsive-nav-link>
            @endcan

            @can('manage users')
                <x-responsive-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.index')" wire:navigate>
                    {{ __('Users') }}
                </x-responsive-nav-link>
            @endcan

            @can('manage settings')
                <x-responsive-nav-link :href="route('admin.settings')" :active="request()->routeIs('admin.settings')" wire:navigate>
                    {{ __('Settings') }}
                </x-responsive-nav-link>
            @endcan
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800" x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name" x-on:profile-updated.window="name = $event.detail.name"></div>
                <div class="font-medium text-sm text-gray-600">{{ auth()->user()->email }}</div>
            </div>

            <div class="px-4 pt-3 flex items-center gap-2">
                <livewire:notification-bell />
                <x-language-switcher />
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile')" wire:navigate>
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <button wire:click="logout" class="w-full text-start">
                    <x-responsive-nav-link>
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </button>
            </div>
        </div>
    </div>
</nav>
