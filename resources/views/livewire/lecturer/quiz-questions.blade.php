<div>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Questions') }}: {{ $quiz->title }}
            </h2>
            <a href="{{ route('lecturer.courses.quizzes', $quiz->course) }}" wire:navigate class="text-lg text-gray-800 hover:text-gray-900 underline">
                {{ __('Back to assessments') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if ($showForm)
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <h3 class="font-medium text-gray-900 mb-4">{{ $editingId ? __('Edit Question') : __('New Question') }}</h3>
                    <form wire:submit="save" class="space-y-4">
                        <div class="grid sm:grid-cols-3 gap-4">
                            <div class="sm:col-span-2">
                                <x-input-label for="qq_type" :value="__('Question Type')" />
                                <select wire:model.live="type" id="qq_type" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full">
                                    <option value="mcq_single">{{ __('Single-best-answer multiple choice') }}</option>
                                    <option value="mcq_multi">{{ __('Multiple-response') }}</option>
                                    <option value="true_false">{{ __('True or False') }}</option>
                                    <option value="matching">{{ __('Matching') }}</option>
                                    <option value="short_answer">{{ __('Short answer') }}</option>
                                    <option value="essay">{{ __('Essay (manually graded)') }}</option>
                                    <option value="numerical">{{ __('Numerical response') }}</option>
                                    <option value="file_upload">{{ __('File-upload assignment (manually graded)') }}</option>
                                </select>
                            </div>
                            <div>
                                <x-input-label for="qq_points" :value="__('Points')" />
                                <x-text-input wire:model="points" id="qq_points" type="number" min="0.25" step="0.25" class="block mt-1 w-full" />
                                <x-input-error :messages="$errors->get('points')" class="mt-2" />
                            </div>
                        </div>

                        <div>
                            <x-input-label for="qq_prompt" :value="__('Question Prompt')" />
                            <textarea wire:model="prompt" id="qq_prompt" rows="2"
                                class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full"></textarea>
                            <x-input-error :messages="$errors->get('prompt')" class="mt-2" />
                        </div>

                        @if (in_array($type, ['mcq_single', 'mcq_multi', 'true_false']))
                            <div>
                                <x-input-label :value="__('Answer Options')" />
                                <x-input-error :messages="$errors->get('options')" class="mt-1" />
                                <div class="space-y-2 mt-1">
                                    @foreach ($options as $index => $option)
                                        <div class="flex items-center gap-2" wire:key="opt-{{ $index }}">
                                            @if ($type === 'mcq_single' || $type === 'true_false')
                                                <input type="radio" wire:click="markSingleCorrect({{ $index }})" @checked($option['is_correct']) class="text-indigo-600 focus:ring-indigo-500">
                                            @else
                                                <input type="checkbox" wire:model="options.{{ $index }}.is_correct" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                            @endif

                                            @if ($type === 'true_false')
                                                <span class="flex-1 text-lg text-gray-800">{{ $option['text'] }}</span>
                                            @else
                                                <x-text-input wire:model="options.{{ $index }}.text" class="flex-1" placeholder="{{ __('Option text') }}" />
                                                @if (count($options) > 2)
                                                    <button type="button" wire:click="removeOptionRow({{ $index }})" class="text-red-600 hover:text-red-800 text-lg">{{ __('Remove') }}</button>
                                                @endif
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                                @if ($type !== 'true_false')
                                    <button type="button" wire:click="addOptionRow" class="text-indigo-600 hover:text-indigo-800 text-lg mt-2">{{ __('+ Add option') }}</button>
                                @endif
                            </div>
                        @elseif ($type === 'matching')
                            <div>
                                <x-input-label :value="__('Matching Pairs')" />
                                <x-input-error :messages="$errors->get('matchingPairs')" class="mt-1" />
                                <div class="space-y-2 mt-1">
                                    @foreach ($matchingPairs as $index => $pair)
                                        <div class="flex items-center gap-2" wire:key="pair-{{ $index }}">
                                            <x-text-input wire:model="matchingPairs.{{ $index }}.left" class="flex-1" placeholder="{{ __('Item') }}" />
                                            <span class="text-gray-600">&harr;</span>
                                            <x-text-input wire:model="matchingPairs.{{ $index }}.right" class="flex-1" placeholder="{{ __('Matches with') }}" />
                                            @if (count($matchingPairs) > 2)
                                                <button type="button" wire:click="removeMatchingPairRow({{ $index }})" class="text-red-600 hover:text-red-800 text-lg">{{ __('Remove') }}</button>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                                <button type="button" wire:click="addMatchingPairRow" class="text-indigo-600 hover:text-indigo-800 text-lg mt-2">{{ __('+ Add pair') }}</button>
                            </div>
                        @elseif ($type === 'short_answer')
                            <div>
                                <x-input-label for="qq_short_answer" :value="__('Correct Answer (exact match, case-insensitive)')" />
                                <x-text-input wire:model="correctShortAnswer" id="qq_short_answer" class="block mt-1 w-full" />
                                <x-input-error :messages="$errors->get('correctShortAnswer')" class="mt-2" />
                            </div>
                        @elseif ($type === 'numerical')
                            <div class="grid sm:grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="qq_numerical" :value="__('Correct Answer')" />
                                    <x-text-input wire:model="correctNumerical" id="qq_numerical" type="number" step="any" class="block mt-1 w-full" />
                                    <x-input-error :messages="$errors->get('correctNumerical')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="qq_tolerance" :value="__('Tolerance (+/-)')" />
                                    <x-text-input wire:model="numericalTolerance" id="qq_tolerance" type="number" step="any" min="0" class="block mt-1 w-full" />
                                </div>
                            </div>
                        @elseif ($type === 'essay' || $type === 'file_upload')
                            <p class="text-lg text-gray-800 italic">{{ __('This question type is graded manually by the lecturer after submission.') }}</p>
                        @endif

                        <div>
                            <x-input-label for="qq_explanation" :value="__('Explanation (shown per answer-visibility setting)')" />
                            <textarea wire:model="explanation" id="qq_explanation" rows="2"
                                class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full"></textarea>
                        </div>

                        <div class="flex gap-2">
                            <x-primary-button>{{ __('Save Question') }}</x-primary-button>
                            <x-secondary-button type="button" wire:click="cancelForm">{{ __('Cancel') }}</x-secondary-button>
                        </div>
                    </form>
                </div>
            @elseif ($showBulkAdd)
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center justify-between gap-4 mb-3">
                        <h3 class="font-medium text-gray-900">{{ __('Bulk Add Questions') }}</h3>
                        <button type="button" wire:click="toggleBulkAdd" class="text-lg text-indigo-600 hover:text-indigo-800 underline whitespace-nowrap">
                            {{ __('Add one at a time instead') }}
                        </button>
                    </div>

                    <p class="text-lg text-gray-800 mb-1">
                        {{ __('One question per line: type | points | prompt | ...answers. Supported types: mcq_single, mcq_multi, true_false, short_answer — other types still need the full form. Prefix a correct MCQ option with *. Examples:') }}
                    </p>
                    <div class="text-sm text-gray-600 font-mono space-y-0.5 mb-3">
                        <div>mcq_single | 1 | {{ __('Capital of Sudan?') }} | Cairo | *Khartoum | Nairobi</div>
                        <div>mcq_multi | 2 | {{ __('Which are primes?') }} | *2 | *3 | 4 | *5</div>
                        <div>true_false | 1 | {{ __('The Nile flows through Sudan.') }} | true</div>
                        <div>short_answer | 1 | H2O? | water</div>
                    </div>

                    <textarea wire:model="bulkAddText" rows="8"
                        class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full font-mono text-lg"></textarea>
                    <x-input-error :messages="$errors->get('bulkAddText')" class="mt-1" />

                    <div class="mt-3">
                        <x-primary-button wire:click="runBulkAdd" wire:loading.attr="disabled" wire:target="runBulkAdd">
                            {{ __('Import') }}
                        </x-primary-button>
                    </div>

                    <div class="border-t pt-4 mt-6">
                        <div class="flex items-center justify-between gap-4 mb-1">
                            <h4 class="font-medium text-gray-900">{{ __('Or Upload a Spreadsheet') }}</h4>
                            <a href="{{ asset('templates/bulk-questions-example.xlsx') }}" download class="text-lg text-indigo-600 hover:text-indigo-800 underline whitespace-nowrap">
                                &#128190; {{ __('Download example spreadsheet') }}
                            </a>
                        </div>
                        <p class="text-lg text-gray-800 mb-2">
                            {{ __('Same columns as above, one per spreadsheet column instead of separated by "|": Type, Points, Prompt, then the answer columns. An optional header row is fine — the first column just needs to say "type".') }}
                        </p>
                        <input type="file" wire:model="bulkAddFile" accept=".xlsx,.xls,.csv"
                            class="block w-full text-lg text-gray-800 file:me-3 file:py-2 file:px-3 file:rounded-md file:border-0 file:bg-indigo-50 file:text-indigo-700">
                        <x-input-error :messages="$errors->get('bulkAddFile')" class="mt-1" />

                        <div class="mt-3">
                            <x-secondary-button wire:click="importBulkFile" wire:loading.attr="disabled" wire:target="importBulkFile">
                                {{ __('Import File') }}
                            </x-secondary-button>
                        </div>
                    </div>

                    @if ($bulkAddCreated > 0)
                        <div class="mt-4 rounded-md bg-green-50 px-4 py-3 text-lg text-green-700">
                            {{ __('Questions created: :count', ['count' => $bulkAddCreated]) }}
                        </div>
                    @endif

                    @if (count($bulkAddFailures))
                        <div class="mt-4">
                            <p class="text-lg font-medium text-red-700 mb-2">
                                {{ __('Rows skipped: :count', ['count' => count($bulkAddFailures)]) }}
                            </p>
                            <ul class="space-y-1 text-lg">
                                @foreach ($bulkAddFailures as $failure)
                                    <li class="rounded-md bg-red-50 px-3 py-2 text-red-700">
                                        <span class="font-mono text-sm">{{ $failure['line'] }}</span>
                                        — {{ $failure['error'] }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            @else
                <div class="flex justify-end gap-3">
                    <button type="button" wire:click="toggleBulkAdd" class="text-lg text-indigo-600 hover:text-indigo-800 underline">
                        {{ __('Bulk Add') }}
                    </button>
                    <x-primary-button wire:click="newForm">{{ __('New Question') }}</x-primary-button>
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg divide-y divide-gray-100">
                @forelse ($questions as $index => $question)
                    <div class="p-4" wire:key="question-{{ $question->id }}">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <span class="text-sm uppercase tracking-wide text-gray-600">{{ ucfirst(str_replace('_', ' ', $question->type)) }} &middot; {{ $question->points }} {{ __('pts') }}</span>
                                <p class="text-gray-900 mt-1">{{ $question->prompt }}</p>

                                @if (in_array($question->type, ['mcq_single', 'mcq_multi', 'true_false']))
                                    <ul class="mt-2 text-lg text-gray-800 space-y-1">
                                        @foreach ($question->options as $option)
                                            <li class="flex items-center gap-1">
                                                @if ($option->is_correct)
                                                    <span class="text-green-600">&#10003;</span>
                                                @else
                                                    <span class="text-gray-300">&#9675;</span>
                                                @endif
                                                {{ $option->text }}
                                            </li>
                                        @endforeach
                                    </ul>
                                @elseif ($question->type === 'matching')
                                    <ul class="mt-2 text-lg text-gray-800 space-y-1">
                                        @foreach ($question->matching_pairs ?? [] as $pair)
                                            <li>{{ $pair['left'] }} &harr; {{ $pair['right'] }}</li>
                                        @endforeach
                                    </ul>
                                @elseif ($question->type === 'short_answer')
                                    <p class="mt-2 text-lg text-gray-800">{{ __('Correct answer') }}: {{ $question->correct_short_answer }}</p>
                                @elseif ($question->type === 'numerical')
                                    <p class="mt-2 text-lg text-gray-800">{{ __('Correct answer') }}: {{ $question->correct_numerical }} &plusmn; {{ $question->numerical_tolerance }}</p>
                                @endif
                            </div>
                            <div class="flex flex-col items-end gap-1 shrink-0">
                                <div class="flex gap-2 text-lg">
                                    <button wire:click="moveQuestion({{ $question->id }}, 'up')" class="text-gray-800 hover:text-gray-800">&uarr;</button>
                                    <button wire:click="moveQuestion({{ $question->id }}, 'down')" class="text-gray-800 hover:text-gray-800">&darr;</button>
                                    <button wire:click="edit({{ $question->id }})" class="text-indigo-600 hover:text-indigo-800 underline">{{ __('Edit') }}</button>
                                    <button wire:click="delete({{ $question->id }})" wire:confirm="{{ __('Delete this question?') }}" class="text-red-600 hover:text-red-800 underline">{{ __('Delete') }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="p-6 text-center text-gray-800">{{ __('No questions yet.') }}</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
