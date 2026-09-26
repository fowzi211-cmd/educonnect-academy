<div>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('My Courses') }}
            </h2>
            <a href="{{ route('lecturer.courses.create') }}" wire:navigate class="rounded-md px-3 py-2 text-lg font-medium text-white bg-gray-900 hover:bg-gray-700">
                {{ __('New Course') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 text-lg">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Title') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Category') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Status') }}</th>
                            <th class="px-4 py-2"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($courses as $course)
                            <tr>
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $course->title }}</td>
                                <td class="px-4 py-3 text-gray-800">{{ $course->category?->name ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    <x-course-status-badge :status="$course->status" />
                                </td>
                                <td class="px-4 py-3 text-end space-x-3 rtl:space-x-reverse">
                                    <a href="{{ route('lecturer.courses.curriculum', $course) }}" wire:navigate class="text-indigo-600 hover:text-indigo-800 underline">
                                        {{ __('Curriculum') }}
                                    </a>
                                    <a href="{{ route('lecturer.courses.live-classes', $course) }}" wire:navigate class="text-indigo-600 hover:text-indigo-800 underline">
                                        {{ __('Live Classes') }}
                                    </a>
                                    <a href="{{ route('lecturer.courses.quizzes', $course) }}" wire:navigate class="text-indigo-600 hover:text-indigo-800 underline">
                                        {{ __('Assessments') }}
                                    </a>
                                    <a href="{{ route('lecturer.courses.assignments', $course) }}" wire:navigate class="text-indigo-600 hover:text-indigo-800 underline">
                                        {{ __('Assignments') }}
                                    </a>
                                    <a href="{{ route('lecturer.courses.completion-criteria', $course) }}" wire:navigate class="text-indigo-600 hover:text-indigo-800 underline">
                                        {{ __('Completion') }}
                                    </a>
                                    <a href="{{ route('lecturer.courses.announcements', $course) }}" wire:navigate class="text-indigo-600 hover:text-indigo-800 underline">
                                        {{ __('Announcements') }}
                                    </a>
                                    <a href="{{ route('courses.discussions.index', $course) }}" wire:navigate class="text-indigo-600 hover:text-indigo-800 underline">
                                        {{ __('Discussions') }}
                                    </a>
                                    <a href="{{ route('lecturer.courses.edit', $course) }}" wire:navigate class="text-indigo-600 hover:text-indigo-800 underline">
                                        {{ __('Edit') }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-gray-800">{{ __('You have not created any courses yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
