<x-app-layout>
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
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <p class="text-lg font-medium">
                        {{ __('Welcome, :name.', ['name' => auth()->user()->name]) }}
                    </p>
                    <p class="mt-1 text-sm text-gray-600">
                        {{ __('Your role(s):') }}
                        @foreach ($roles as $role)
                            <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-800">
                                {{ $roleLabels[$role] ?? $role }}
                            </span>
                        @endforeach
                    </p>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="font-medium text-gray-900">{{ __('What you can do right now') }}</h3>
                    <p class="mt-1 text-sm text-gray-600">
                        {{ __('EduConnect Academy is being built in phases. Course delivery, payments, assessments, and reporting are not available yet — this dashboard will grow with each phase.') }}
                    </p>

                    <ul class="mt-4 space-y-2 text-sm">
                        <li>
                            <a href="{{ route('profile') }}" wire:navigate class="text-indigo-600 hover:text-indigo-800 underline">
                                {{ __('Manage your profile') }}
                            </a>
                        </li>
                        @can('manage settings')
                            <li>
                                <a href="{{ route('admin.settings') }}" wire:navigate class="text-indigo-600 hover:text-indigo-800 underline">
                                    {{ __('Manage platform branding and settings') }}
                                </a>
                            </li>
                        @endcan
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
