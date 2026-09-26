<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Course Review Queue') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-4 flex flex-wrap gap-2 text-lg">
                @foreach ([
                    \App\Models\Course::STATUS_UNDER_REVIEW => __('Under Review'),
                    \App\Models\Course::STATUS_REVISION_REQUESTED => __('Revision Requested'),
                    \App\Models\Course::STATUS_APPROVED => __('Approved'),
                    \App\Models\Course::STATUS_PUBLISHED => __('Published'),
                    \App\Models\Course::STATUS_SUSPENDED => __('Suspended'),
                    'all' => __('All'),
                ] as $value => $label)
                    <button
                        wire:click="$set('status', '{{ $value }}')"
                        class="px-3 py-1.5 rounded-md border {{ $status === $value ? 'bg-gray-900 text-white border-gray-900' : 'bg-white text-gray-800 border-gray-300' }}"
                    >
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 text-lg">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Title') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Lecturer') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Category') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Status') }}</th>
                            <th class="px-4 py-2"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($courses as $course)
                            <tr>
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $course->title }}</td>
                                <td class="px-4 py-3 text-gray-800">{{ $course->creator->name }}</td>
                                <td class="px-4 py-3 text-gray-800">{{ $course->category?->name ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    <x-course-status-badge :status="$course->status" />
                                </td>
                                <td class="px-4 py-3 text-end">
                                    <a href="{{ route('admin.courses.show', $course) }}" wire:navigate class="text-indigo-600 hover:text-indigo-800 underline">
                                        {{ __('Review') }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-gray-800">{{ __('No courses found.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $courses->links() }}
            </div>
        </div>
    </div>
</div>
