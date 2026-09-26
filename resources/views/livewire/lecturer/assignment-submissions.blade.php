<div>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Submissions') }}: {{ $assignment->title }}
            </h2>
            <a href="{{ route('lecturer.courses.assignments', $assignment->course) }}" wire:navigate class="text-lg text-gray-800 hover:text-gray-900 underline">
                {{ __('Back to assignments') }}
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
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Submitted') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('File') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Score') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Status') }}</th>
                            <th class="px-4 py-2"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($submissions as $submission)
                            <tr wire:key="sub-{{ $submission->id }}">
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-900">{{ $submission->user->name }}</div>
                                    <div class="text-gray-800">{{ $submission->user->email }}</div>
                                </td>
                                <td class="px-4 py-3 text-gray-800">
                                    {{ $submission->submitted_at->format('Y-m-d H:i') }}
                                    @if ($submission->is_late)
                                        <span class="text-amber-600">({{ __('Late') }})</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <a href="{{ route('assignment-submissions.download', $submission) }}" class="text-indigo-600 hover:text-indigo-800 underline">
                                        {{ $submission->file_name }}
                                    </a>
                                </td>
                                <td class="px-4 py-3 text-gray-800">
                                    {{ $submission->status === 'graded' ? $submission->score.' / '.$assignment->max_points : '—' }}
                                </td>
                                <td class="px-4 py-3">
                                    <span @class([
                                        'inline-flex items-center rounded-full px-2.5 py-0.5 text-sm font-medium',
                                        'bg-amber-100 text-amber-800' => $submission->status === 'submitted',
                                        'bg-green-100 text-green-800' => $submission->status === 'graded',
                                    ])>
                                        {{ $submission->status === 'graded' ? __('Graded') : __('Needs Grading') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-end">
                                    <button wire:click="startGrade({{ $submission->id }})" class="text-indigo-600 hover:text-indigo-800 underline">
                                        {{ $submission->status === 'graded' ? __('Update Grade') : __('Grade') }}
                                    </button>
                                </td>
                            </tr>

                            @if ($gradingId === $submission->id)
                                <tr>
                                    <td colspan="6" class="px-4 py-4 bg-indigo-50">
                                        <div class="grid sm:grid-cols-4 gap-3 items-start">
                                            <div>
                                                <x-input-label for="score" :value="__('Score')" />
                                                <x-text-input wire:model="score" id="score" type="number" min="0" max="{{ $assignment->max_points }}" step="0.5" class="mt-1 block w-full" />
                                                <p class="text-sm text-gray-800 mt-1">{{ __('Out of :n', ['n' => $assignment->max_points]) }}</p>
                                                <x-input-error :messages="$errors->get('score')" class="mt-1" />
                                            </div>
                                            <div class="sm:col-span-2">
                                                <x-input-label for="feedback" :value="__('Feedback (optional)')" />
                                                <textarea wire:model="feedback" id="feedback" rows="2"
                                                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full"></textarea>
                                            </div>
                                        </div>
                                        <div class="mt-3 flex gap-2">
                                            <x-primary-button wire:click="saveGrade">{{ __('Save Grade') }}</x-primary-button>
                                            <x-secondary-button wire:click="cancelGrade">{{ __('Cancel') }}</x-secondary-button>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-6 text-center text-gray-800">{{ __('No submissions yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
