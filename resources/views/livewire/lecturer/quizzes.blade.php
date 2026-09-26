<div>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Assessments') }}: {{ $course->title }}
            </h2>
            <a href="{{ route('lecturer.courses.index') }}" wire:navigate class="text-lg text-gray-800 hover:text-gray-900 underline">
                {{ __('Back to my courses') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if ($showForm)
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <h3 class="font-medium text-gray-900 mb-4">{{ $editingId ? __('Edit Assessment') : __('New Assessment') }}</h3>
                    <form wire:submit="save" class="space-y-4">
                        <div class="grid sm:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="q_title" :value="__('Title')" />
                                <x-text-input wire:model="title" id="q_title" class="block mt-1 w-full" />
                                <x-input-error :messages="$errors->get('title')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="q_type" :value="__('Type')" />
                                <select wire:model="type" id="q_type" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full">
                                    <option value="quiz">{{ __('Quiz') }}</option>
                                    <option value="exam">{{ __('Examination') }}</option>
                                    <option value="practice_test">{{ __('Practice Test') }}</option>
                                    <option value="survey">{{ __('Survey (ungraded)') }}</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <x-input-label for="q_description" :value="__('Description')" />
                            <textarea wire:model="description" id="q_description" rows="2"
                                class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full"></textarea>
                        </div>

                        <div class="grid sm:grid-cols-3 gap-4">
                            <div>
                                <x-input-label for="q_time_limit" :value="__('Time Limit (minutes)')" />
                                <x-text-input wire:model="time_limit_minutes" id="q_time_limit" type="number" min="1" class="block mt-1 w-full" placeholder="{{ __('Untimed') }}" />
                                <x-input-error :messages="$errors->get('time_limit_minutes')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="q_max_attempts" :value="__('Max Attempts')" />
                                <x-text-input wire:model="max_attempts" id="q_max_attempts" type="number" min="1" class="block mt-1 w-full" placeholder="{{ __('Unlimited') }}" />
                                <x-input-error :messages="$errors->get('max_attempts')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="q_pass_mark" :value="__('Pass Mark (%)')" />
                                <x-text-input wire:model="pass_mark_percent" id="q_pass_mark" type="number" min="0" max="100" step="0.01" class="block mt-1 w-full" />
                                <x-input-error :messages="$errors->get('pass_mark_percent')" class="mt-2" />
                            </div>
                        </div>

                        <div class="grid sm:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="q_opens_at" :value="__('Opens At')" />
                                <input wire:model="opens_at" id="q_opens_at" type="datetime-local"
                                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full" />
                            </div>
                            <div>
                                <x-input-label for="q_closes_at" :value="__('Closes At')" />
                                <input wire:model="closes_at" id="q_closes_at" type="datetime-local"
                                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full" />
                                <x-input-error :messages="$errors->get('closes_at')" class="mt-2" />
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-6">
                            <label class="flex items-center gap-2 text-lg text-gray-800">
                                <input type="checkbox" wire:model="shuffle_questions" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                {{ __('Shuffle question order') }}
                            </label>
                            <label class="flex items-center gap-2 text-lg text-gray-800">
                                <input type="checkbox" wire:model="shuffle_options" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                {{ __('Shuffle answer options') }}
                            </label>
                            <label class="flex items-center gap-2 text-lg text-gray-800">
                                <input type="checkbox" wire:model="negative_marking" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                {{ __('Negative marking') }}
                            </label>
                        </div>

                        <div class="grid sm:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="q_result_visibility" :value="__('Result Visibility')" />
                                <select wire:model="result_visibility" id="q_result_visibility" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full">
                                    <option value="immediate">{{ __('Immediately after submission') }}</option>
                                    <option value="after_feedback_release">{{ __('After feedback release date') }}</option>
                                    <option value="manual">{{ __('Manually released by lecturer') }}</option>
                                </select>
                            </div>
                            <div>
                                <x-input-label for="q_answer_visibility" :value="__('Correct Answer Visibility')" />
                                <select wire:model="correct_answer_visibility" id="q_answer_visibility" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full">
                                    <option value="never">{{ __('Never') }}</option>
                                    <option value="after_submit">{{ __('After submission') }}</option>
                                    <option value="after_close">{{ __('After assessment closes') }}</option>
                                    <option value="after_feedback_release">{{ __('After feedback release date') }}</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <x-input-label for="q_feedback_release" :value="__('Feedback Release Date (optional)')" />
                            <input wire:model="feedback_release_at" id="q_feedback_release" type="datetime-local"
                                class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full" />
                        </div>

                        <div>
                            <x-input-label for="q_integrity" :value="__('Academic Integrity Acknowledgement (optional)')" />
                            <textarea wire:model="integrity_acknowledgement_text" id="q_integrity" rows="2"
                                placeholder="{{ __('E.g. \"I confirm I will complete this assessment without unauthorised assistance.\"') }}"
                                class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full"></textarea>
                            <p class="text-sm text-gray-800 mt-1">{{ __('If set, students must accept this statement before starting an attempt.') }}</p>
                        </div>

                        <div class="flex gap-2">
                            <x-primary-button>{{ __('Save') }}</x-primary-button>
                            <x-secondary-button type="button" wire:click="cancelForm">{{ __('Cancel') }}</x-secondary-button>
                        </div>
                    </form>
                </div>
            @else
                <div class="flex justify-end">
                    <x-primary-button wire:click="newForm">{{ __('New Assessment') }}</x-primary-button>
                </div>
            @endif

            <x-input-error :messages="$errors->get('publish')" class="px-1" />
            <x-input-error :messages="$errors->get('delete')" class="px-1" />

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 text-lg">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Title') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Type') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Questions') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Attempts') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Status') }}</th>
                            <th class="px-4 py-2"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($quizzes as $quiz)
                            <tr wire:key="quiz-{{ $quiz->id }}">
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $quiz->title }}</td>
                                <td class="px-4 py-3 text-gray-800">{{ ucfirst(str_replace('_', ' ', $quiz->type)) }}</td>
                                <td class="px-4 py-3 text-gray-800">{{ $quiz->questions_count }}</td>
                                <td class="px-4 py-3 text-gray-800">{{ $quiz->attempts_count }}</td>
                                <td class="px-4 py-3">
                                    <span @class([
                                        'inline-flex items-center rounded-full px-2.5 py-0.5 text-sm font-medium',
                                        'bg-green-100 text-green-800' => $quiz->is_published,
                                        'bg-gray-100 text-gray-800' => ! $quiz->is_published,
                                    ])>
                                        {{ $quiz->is_published ? __('Published') : __('Draft') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-end space-x-2 rtl:space-x-reverse">
                                    <a href="{{ route('lecturer.quizzes.questions', $quiz) }}" wire:navigate class="text-indigo-600 hover:text-indigo-800 underline">{{ __('Questions') }}</a>
                                    @if ($quiz->attempts_count > 0)
                                        <a href="{{ route('lecturer.quizzes.grading', $quiz) }}" wire:navigate class="text-indigo-600 hover:text-indigo-800 underline">{{ __('Grading') }}</a>
                                    @endif
                                    <button wire:click="edit({{ $quiz->id }})" class="text-indigo-600 hover:text-indigo-800 underline">{{ __('Edit') }}</button>
                                    <button wire:click="togglePublish({{ $quiz->id }})" class="text-gray-800 hover:text-gray-800 underline">
                                        {{ $quiz->is_published ? __('Unpublish') : __('Publish') }}
                                    </button>
                                    @if ($quiz->attempts_count === 0)
                                        <button wire:click="delete({{ $quiz->id }})" wire:confirm="{{ __('Delete this assessment?') }}" class="text-red-600 hover:text-red-800 underline">{{ __('Delete') }}</button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-6 text-center text-gray-800">{{ __('No assessments yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
