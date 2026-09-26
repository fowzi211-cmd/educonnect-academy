<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    @php
        $roleLabels = [
            'super_administrator' => __('Super Administrator'),
            'administrator' => __('Administrator'),
            'finance_officer' => __('Finance Officer'),
            'support_officer' => __('Support Officer'),
            'course_reviewer' => __('Course Reviewer'),
            'lecturer' => __('Lecturer'),
            'student' => __('Student'),
        ];
        $roles = auth()->user()->getRoleNames();
    @endphp

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            <div class="relative overflow-hidden bg-brand-gradient shadow-sm sm:rounded-xl">
                <div class="pointer-events-none absolute inset-0" style="background-image: radial-gradient(circle at 85% 15%, rgba(255,255,255,0.18), transparent 45%);"></div>
                <div class="relative p-6 sm:p-8 text-white">
                    <p class="text-xl font-semibold">
                        {{ __('Welcome, :name.', ['name' => auth()->user()->name]) }}
                    </p>
                    <p class="mt-2 text-sm text-white/80">
                        {{ __('Your role(s):') }}
                        @foreach ($roles as $role)
                            <span class="inline-flex items-center rounded-full bg-white/15 ring-1 ring-white/25 px-2.5 py-0.5 text-xs font-medium text-white">
                                {{ $roleLabels[$role] ?? $role }}
                            </span>
                        @endforeach
                    </p>
                </div>
            </div>

            @if ($isStaff)
                @if ($operational)
                    <div>
                        <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide mb-3">{{ __('Platform Overview') }}</h3>
                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                            <x-stat-tile accent="indigo" :label="__('Total Users')" :value="number_format($operational['total_users'])" />
                            <x-stat-tile accent="indigo" :label="__('Active Students')" :value="number_format($operational['active_students'])" />
                            <x-stat-tile accent="indigo" :label="__('Active Lecturers')" :value="number_format($operational['active_lecturers'])" />
                            <x-stat-tile accent="indigo" :label="__('Published Courses')" :value="number_format($operational['published_courses'])" />
                            <x-stat-tile accent="indigo" :label="__('Courses Awaiting Review')" :value="number_format($operational['courses_awaiting_review'])" />
                            <x-stat-tile accent="indigo" :label="__('Upcoming Live Classes')" :value="number_format($operational['upcoming_live_classes'])" />
                            <x-stat-tile accent="indigo" :label="__('Overall Completion Rate')" :value="$operational['completion_rate'].'%'" />
                        </div>
                    </div>
                @endif

                @if ($financial)
                    <div>
                        <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide mb-3">{{ __('Finance') }}</h3>
                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                            <x-stat-tile accent="emerald" :label="__('Active Subscriptions')" :value="number_format($financial['active_subscriptions'])" />
                            <x-stat-tile accent="emerald" :label="__('Monthly Recurring Revenue')" :value="number_format($financial['mrr'], 2)" />
                            <x-stat-tile accent="emerald" :label="__('Total Revenue')" :value="number_format($financial['total_revenue'], 2)" />
                            <x-stat-tile accent="rose" :label="__('Failed Payments (30d)')" :value="number_format($financial['failed_payments'])" />
                            <x-stat-tile accent="rose" :label="__('Refunds (30d)')" :value="number_format($financial['refunds_30d'], 2)" />
                            <x-stat-tile accent="emerald" :label="__('Outstanding Lecturer Payouts')" :value="number_format($financial['outstanding_payouts'], 2)" />
                        </div>
                    </div>
                @endif

                @if ($support)
                    <div>
                        <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide mb-3">{{ __('Support') }}</h3>
                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                            <x-stat-tile accent="amber" :label="__('Open Support Tickets')" :value="number_format($support['open_tickets'])" />
                            <x-stat-tile accent="amber" :label="__('Unassigned Tickets')" :value="number_format($support['unassigned_tickets'])" />
                        </div>
                    </div>
                @endif
            @else
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl">
                    <div class="p-6 text-gray-900">
                        <h3 class="font-semibold text-gray-900 text-lg">{{ __('What you can do right now') }}</h3>

                        <ul class="mt-4 space-y-1">
                            <li>
                                <a href="{{ route('profile') }}" wire:navigate class="flex items-center gap-2 rounded-lg px-3 py-2.5 -mx-3 text-sm text-gray-700 hover:bg-gray-50 hover:text-brand-primary transition">
                                    <svg class="h-4 w-4 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
                                    {{ __('Manage your profile') }}
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('support-tickets.index') }}" wire:navigate class="flex items-center gap-2 rounded-lg px-3 py-2.5 -mx-3 text-sm text-gray-700 hover:bg-gray-50 hover:text-brand-primary transition">
                                    <svg class="h-4 w-4 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z" /></svg>
                                    {{ __('View your support tickets') }}
                                </a>
                            </li>
                            @can('manage own courses')
                                <li>
                                    <a href="{{ route('lecturer.courses.index') }}" wire:navigate class="flex items-center gap-2 rounded-lg px-3 py-2.5 -mx-3 text-sm text-gray-700 hover:bg-gray-50 hover:text-brand-primary transition">
                                        <svg class="h-4 w-4 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" /></svg>
                                        {{ __('Manage your courses') }}
                                    </a>
                                </li>
                            @endcan
                            @if (auth()->user()->hasRole('student'))
                                <li>
                                    <a href="{{ route('my-courses.index') }}" wire:navigate class="flex items-center gap-2 rounded-lg px-3 py-2.5 -mx-3 text-sm text-gray-700 hover:bg-gray-50 hover:text-brand-primary transition">
                                        <svg class="h-4 w-4 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.62 48.62 0 0112 20.904a48.62 48.62 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.636 50.636 0 00-2.658-.813A59.906 59.906 0 0112 3.493a59.903 59.903 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5" /></svg>
                                        {{ __('Continue learning') }}
                                    </a>
                                </li>
                            @endif
                            @can('review courses')
                                <li>
                                    <a href="{{ route('admin.courses.index') }}" wire:navigate class="flex items-center gap-2 rounded-lg px-3 py-2.5 -mx-3 text-sm text-gray-700 hover:bg-gray-50 hover:text-brand-primary transition">
                                        <svg class="h-4 w-4 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z" /></svg>
                                        {{ __('Review pending courses') }}
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
