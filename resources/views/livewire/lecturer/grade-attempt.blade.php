<div>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Grading') }}: {{ $attempt->quiz->title }} &mdash; {{ $attempt->user->name }}
            </h2>
            <a href="{{ route('lecturer.quizzes.grading', $attempt->quiz) }}" wire:navigate class="text-lg text-gray-800 hover:text-gray-900 underline">
                {{ __('Back to attempts') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <div class="bg-white shadow-sm sm:rounded-lg p-4 flex items-center justify-between text-lg">
                <span class="text-gray-800">{{ __('Attempt #:n', ['n' => $attempt->attempt_number]) }} &middot; {{ $attempt->submitted_at?->format('Y-m-d H:i') }}</span>
                <span class="font-medium text-gray-900">
                    {{ $attempt->status === 'graded' ? __('Total') . ': ' . $attempt->score_percent . '%' : __('Awaiting manual grading') }}
                </span>
            </div>

            @foreach ($answers as $answer)
                @php $question = $answer->question; @endphp
                <div class="bg-white shadow-sm sm:rounded-lg p-6" wire:key="answer-{{ $answer->id }}">
                    <div class="flex justify-between text-sm text-gray-600 mb-2">
                        <span>{{ ucfirst(str_replace('_', ' ', $question->type)) }}</span>
                        <span>{{ $question->points }} {{ __('pts') }}</span>
                    </div>
                    <p class="text-gray-900 mb-3">{{ $question->prompt }}</p>

                    @if (in_array($question->type, ['mcq_single', 'mcq_multi', 'true_false']))
                        <div class="text-lg space-y-1">
                            @foreach ($question->options as $option)
                                @php $wasSelected = in_array($option->id, $answer->selected_option_ids ?? []); @endphp
                                <div @class([
                                    'px-2 py-1 rounded',
                                    'bg-green-50 text-green-800' => $option->is_correct,
                                    'bg-red-50 text-red-800' => $wasSelected && ! $option->is_correct,
                                    'text-gray-800' => ! $wasSelected && ! $option->is_correct,
                                ])>
                                    {{ $wasSelected ? '➜ ' : '' }}{{ $option->text }} {{ $option->is_correct ? '('.__('correct').')' : '' }}
                                </div>
                            @endforeach
                        </div>
                    @elseif ($question->type === 'matching')
                        <div class="text-lg space-y-1">
                            @foreach ($question->matching_pairs ?? [] as $pair)
                                @php $studentAnswer = $answer->matching_answer[$pair['left']] ?? null; @endphp
                                <div class="{{ $studentAnswer === $pair['right'] ? 'text-green-700' : 'text-red-700' }}">
                                    {{ $pair['left'] }} &rarr; {{ $studentAnswer ?? __('(no answer)') }}
                                    @if ($studentAnswer !== $pair['right'])
                                        <span class="text-gray-800">({{ __('correct') }}: {{ $pair['right'] }})</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @elseif ($question->type === 'numerical')
                        <p class="text-lg {{ $answer->is_correct ? 'text-green-700' : 'text-red-700' }}">
                            {{ __('Answered') }}: {{ $answer->numerical_answer ?? __('(no answer)') }}
                            <span class="text-gray-800">({{ __('correct') }}: {{ $question->correct_numerical }})</span>
                        </p>
                    @elseif ($question->type === 'short_answer')
                        <p class="text-lg {{ $answer->is_correct ? 'text-green-700' : 'text-red-700' }}">
                            {{ __('Answered') }}: {{ $answer->short_answer_text ?? __('(no answer)') }}
                            <span class="text-gray-800">({{ __('correct') }}: {{ $question->correct_short_answer }})</span>
                        </p>
                    @elseif ($question->type === 'essay')
                        <p class="text-lg text-gray-800 whitespace-pre-line bg-gray-50 rounded p-3">{{ $answer->short_answer_text ?? __('(no answer)') }}</p>
                    @elseif ($question->type === 'file_upload')
                        @if ($answer->file_path)
                            <a href="{{ route('lecturer.quiz-submissions.download', $answer) }}" class="text-indigo-600 hover:text-indigo-800 underline text-lg">
                                {{ $answer->file_name }}
                            </a>
                        @else
                            <p class="text-lg text-gray-800 italic">{{ __('No file submitted.') }}</p>
                        @endif
                    @endif

                    @if ($question->isAutoGraded())
                        <p class="mt-2 text-sm text-gray-600">
                            {{ __('Auto-graded') }}: {{ $answer->points_awarded }} / {{ $question->points }} {{ __('pts') }}
                        </p>
                    @else
                        <div class="mt-3 grid sm:grid-cols-4 gap-3 items-start border-t pt-3">
                            <div>
                                <x-input-label :for="'score-'.$answer->id" :value="__('Points')" />
                                <x-text-input wire:model="scores.{{ $answer->id }}" :id="'score-'.$answer->id" type="number" step="0.25" min="0" max="{{ $question->points }}" class="mt-1 block w-full" />
                                <x-input-error :messages="$errors->get('scores.'.$answer->id)" class="mt-1" />
                            </div>
                            <div class="sm:col-span-2">
                                <x-input-label :for="'feedback-'.$answer->id" :value="__('Feedback (optional)')" />
                                <textarea wire:model="feedback.{{ $answer->id }}" :id="'feedback-'.$answer->id" rows="1"
                                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full"></textarea>
                            </div>
                            <div class="self-end">
                                <x-primary-button wire:click="gradeAnswer({{ $answer->id }})">
                                    {{ $answer->graded_at ? __('Update') : __('Save') }}
                                </x-primary-button>
                            </div>
                        </div>
                        @if ($answer->graded_at)
                            <p class="mt-1 text-sm text-gray-600">{{ __('Last graded') }}: {{ $answer->graded_at->format('Y-m-d H:i') }}</p>
                        @endif
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>
