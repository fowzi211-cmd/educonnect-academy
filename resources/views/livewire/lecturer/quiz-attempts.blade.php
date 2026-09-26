<div>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Grading') }}: {{ $quiz->title }}
            </h2>
            <a href="{{ route('lecturer.courses.quizzes', $quiz->course) }}" wire:navigate class="text-lg text-gray-800 hover:text-gray-900 underline">
                {{ __('Back to assessments') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 text-lg">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Student') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Attempt') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Submitted') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Score') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Status') }}</th>
                            <th class="px-4 py-2"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($attempts as $attempt)
                            <tr wire:key="attempt-{{ $attempt->id }}">
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-900">{{ $attempt->user->name }}</div>
                                    <div class="text-gray-800">{{ $attempt->user->email }}</div>
                                </td>
                                <td class="px-4 py-3 text-gray-800">#{{ $attempt->attempt_number }}</td>
                                <td class="px-4 py-3 text-gray-800">{{ $attempt->submitted_at?->format('Y-m-d H:i') }}</td>
                                <td class="px-4 py-3 text-gray-800">
                                    {{ $attempt->status === 'graded' ? $attempt->score_percent.'%' : '—' }}
                                </td>
                                <td class="px-4 py-3">
                                    <span @class([
                                        'inline-flex items-center rounded-full px-2.5 py-0.5 text-sm font-medium',
                                        'bg-amber-100 text-amber-800' => $attempt->ungraded_count > 0,
                                        'bg-green-100 text-green-800' => $attempt->ungraded_count === 0,
                                    ])>
                                        {{ $attempt->ungraded_count > 0 ? __('Needs Grading') : __('Fully Graded') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-end">
                                    <a href="{{ route('lecturer.quiz-attempts.grade', $attempt) }}" wire:navigate class="text-indigo-600 hover:text-indigo-800 underline">
                                        {{ $attempt->ungraded_count > 0 ? __('Grade') : __('View') }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-6 text-center text-gray-800">{{ __('No submitted attempts yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
