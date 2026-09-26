<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My Assessments') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-10">

            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-3">{{ __('Quizzes & Exams') }}</h3>

                @if ($quizzes->isEmpty())
                    <p class="text-gray-800 italic">{{ __('No quizzes or exams yet in your enrolled courses.') }}</p>
                @else
                    <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-lg">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Course') }}</th>
                                    <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Title') }}</th>
                                    <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Type') }}</th>
                                    <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Closes') }}</th>
                                    <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Status') }}</th>
                                    <th class="px-4 py-2"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($quizzes as $row)
                                    @php
                                        $quiz = $row['quiz'];
                                        $attempt = $row['attempt'];
                                    @endphp
                                    <tr>
                                        <td class="px-4 py-3 text-gray-800">{{ $quiz->course->title }}</td>
                                        <td class="px-4 py-3 font-medium text-gray-900">{{ $quiz->title }}</td>
                                        <td class="px-4 py-3 text-gray-800">{{ __(\Illuminate\Support\Str::headline($quiz->type)) }}</td>
                                        <td class="px-4 py-3 text-gray-800">{{ $quiz->closes_at?->format('Y-m-d H:i') ?? __('No deadline') }}</td>
                                        <td class="px-4 py-3">
                                            @if (! $attempt)
                                                <span class="text-gray-600">{{ __('Not started') }}</span>
                                            @elseif ($attempt->status === 'in_progress')
                                                <span class="font-medium text-amber-700">{{ __('In progress') }}</span>
                                            @elseif ($attempt->status === 'submitted')
                                                <span class="font-medium text-amber-700">{{ __('Awaiting grading') }}</span>
                                            @elseif ($quiz->isGraded() && $quiz->resultsVisibleNow())
                                                <span @class(['font-medium', 'text-green-700' => $attempt->passed, 'text-red-700' => ! $attempt->passed])>
                                                    {{ $attempt->score_percent }}% &middot; {{ $attempt->passed ? __('Passed') : __('Failed') }}
                                                </span>
                                            @else
                                                <span class="text-gray-600">{{ __('Graded') }}</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-end">
                                            <a href="{{ route('my-courses.quizzes.take', $quiz) }}" wire:navigate class="text-indigo-600 hover:text-indigo-800 underline">
                                                {{ $attempt && $attempt->status === 'in_progress' ? __('Continue') : __('View') }}
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-3">{{ __('Assignments') }}</h3>

                @if ($assignments->isEmpty())
                    <p class="text-gray-800 italic">{{ __('No assignments yet in your enrolled courses.') }}</p>
                @else
                    <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-lg">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Course') }}</th>
                                    <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Title') }}</th>
                                    <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Due') }}</th>
                                    <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Status') }}</th>
                                    <th class="px-4 py-2"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($assignments as $row)
                                    @php
                                        $assignment = $row['assignment'];
                                        $submission = $row['submission'];
                                    @endphp
                                    <tr>
                                        <td class="px-4 py-3 text-gray-800">{{ $assignment->course->title }}</td>
                                        <td class="px-4 py-3 font-medium text-gray-900">{{ $assignment->title }}</td>
                                        <td class="px-4 py-3 text-gray-800">{{ $assignment->due_at?->format('Y-m-d H:i') ?? __('No deadline') }}</td>
                                        <td class="px-4 py-3">
                                            @if (! $submission)
                                                @if ($assignment->isPastDue())
                                                    <span class="font-medium text-red-700">{{ __('Missed') }}</span>
                                                @else
                                                    <span class="text-gray-600">{{ __('Not submitted') }}</span>
                                                @endif
                                            @elseif ($submission->status === 'graded')
                                                <span class="font-medium text-green-700">
                                                    {{ $submission->score }} / {{ $assignment->max_points }} {{ __('pts') }}
                                                </span>
                                            @else
                                                <span class="font-medium text-amber-700">
                                                    {{ $submission->is_late ? __('Submitted late') : __('Submitted') }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-end">
                                            <a href="{{ route('my-courses.assignments.submit', $assignment) }}" wire:navigate class="text-indigo-600 hover:text-indigo-800 underline">
                                                {{ __('View') }}
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

        </div>
    </div>
</div>
