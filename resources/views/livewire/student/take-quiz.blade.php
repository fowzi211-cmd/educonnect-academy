<div @if ($attempt && $attempt->status === 'in_progress' && $attempt->time_limit_expires_at) wire:poll.10s="checkExpiry" @endif>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $quiz->title }}
            </h2>
            <a href="{{ route('my-courses.classroom', $quiz->course) }}" wire:navigate class="text-lg text-gray-800 hover:text-gray-900 underline">
                {{ __('Back to classroom') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <x-protected-area class="max-w-5xl mx-auto sm:px-6 lg:px-8">
        <div class="space-y-6">

            @if (! $attempt || $attempt->status !== 'in_progress')
                {{-- Start screen / results of the most recent finished attempt --}}
                <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-4">
                    @if ($quiz->description)
                        <p class="text-gray-800">{{ $quiz->description }}</p>
                    @endif

                    <dl class="grid sm:grid-cols-2 gap-3 text-lg">
                        <div>
                            <dt class="text-gray-800">{{ __('Time limit') }}</dt>
                            <dd class="text-gray-900">{{ $quiz->time_limit_minutes ? __(':n minutes', ['n' => $quiz->time_limit_minutes]) : __('Untimed') }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-800">{{ __('Attempts') }}</dt>
                            <dd class="text-gray-900">{{ $attemptsUsed }} / {{ $quiz->max_attempts ?? __('Unlimited') }}</dd>
                        </div>
                        @if ($quiz->pass_mark_percent !== null)
                            <div>
                                <dt class="text-gray-800">{{ __('Pass mark') }}</dt>
                                <dd class="text-gray-900">{{ $quiz->pass_mark_percent }}%</dd>
                            </div>
                        @endif
                    </dl>

                    @if ($canAttempt)
                        @if ($quiz->integrity_acknowledgement_text)
                            <label class="flex items-start gap-2 text-lg text-gray-800">
                                <input type="checkbox" wire:model="integrityAccepted" class="mt-1 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span>{{ $quiz->integrity_acknowledgement_text }}</span>
                            </label>
                            <x-input-error :messages="$errors->get('integrityAccepted')" />
                        @endif

                        <x-primary-button wire:click="startAttempt">
                            {{ $attemptsUsed > 0 ? __('Start New Attempt') : __('Start') }}
                        </x-primary-button>
                    @else
                        <p class="text-gray-800 italic">{{ __('This assessment is not currently available to you (closed, not yet open, or attempt limit reached).') }}</p>
                    @endif
                </div>

                @if ($pastAttempts->isNotEmpty())
                    <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                        <table class="min-w-full divide-y divide-gray-200 text-lg">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Attempt') }}</th>
                                    <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Submitted') }}</th>
                                    <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Score') }}</th>
                                    <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Result') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($pastAttempts as $past)
                                    <tr>
                                        <td class="px-4 py-3 text-gray-800">#{{ $past->attempt_number }}</td>
                                        <td class="px-4 py-3 text-gray-800">{{ $past->submitted_at?->format('Y-m-d H:i') }}</td>
                                        <td class="px-4 py-3 text-gray-800">
                                            @if ($past->status === 'graded' && $quiz->isGraded())
                                                @if ($quiz->resultsVisibleNow())
                                                    {{ $past->score_percent }}%
                                                @else
                                                    {{ __('Pending release') }}
                                                @endif
                                            @elseif ($past->status === 'submitted')
                                                {{ __('Awaiting grading') }}
                                            @else
                                                &mdash;
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            @if ($past->status === 'graded' && $quiz->pass_mark_percent !== null && $quiz->resultsVisibleNow())
                                                <span @class(['font-medium', 'text-green-700' => $past->passed, 'text-red-700' => ! $past->passed])>
                                                    {{ $past->passed ? __('Passed') : __('Failed') }}
                                                </span>
                                            @else
                                                &mdash;
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            @else
                {{-- In-progress attempt: the question form --}}
                <div class="bg-white shadow-sm sm:rounded-lg p-4 flex items-center justify-between sticky top-0 z-10">
                    <span class="text-lg text-gray-800">{{ __('Attempt #:n', ['n' => $attempt->attempt_number]) }}</span>
                    @if ($attempt->time_limit_expires_at)
                        <span class="text-lg font-medium text-amber-700" x-data="{
                            expiresAt: new Date('{{ $attempt->time_limit_expires_at->toIso8601String() }}').getTime(),
                            remaining: '',
                            tick() {
                                const diff = Math.max(0, this.expiresAt - Date.now());
                                const m = Math.floor(diff / 60000);
                                const s = Math.floor((diff % 60000) / 1000);
                                this.remaining = m + ':' + String(s).padStart(2, '0');
                            }
                        }" x-init="tick(); setInterval(() => tick(), 1000)">
                            {{ __('Time remaining') }}: <span x-text="remaining"></span>
                        </span>
                    @endif
                </div>

                <form wire:submit="submit" class="space-y-4">
                    @foreach ($orderedQuestions as $index => $question)
                        <div class="bg-white shadow-sm sm:rounded-lg p-6" wire:key="q-{{ $question->id }}">
                            <div class="flex justify-between text-sm text-gray-600 mb-2">
                                <span>{{ __('Question :n', ['n' => $index + 1]) }}</span>
                                <span>{{ $question->points }} {{ __('pts') }}</span>
                            </div>
                            <p class="text-gray-900 mb-3">{{ $question->prompt }}</p>

                            @if (in_array($question->type, ['mcq_single', 'true_false']))
                                <div class="space-y-2">
                                    @foreach ($question->options as $option)
                                        <label class="flex items-center gap-2 text-lg text-gray-800">
                                            <input type="radio" wire:click="selectSingle({{ $question->id }}, {{ $option->id }})"
                                                @checked(($answers[$question->id][0] ?? null) == $option->id)
                                                class="text-indigo-600 focus:ring-indigo-500">
                                            {{ $option->text }}
                                        </label>
                                    @endforeach
                                </div>
                            @elseif ($question->type === 'mcq_multi')
                                <div class="space-y-2">
                                    @foreach ($question->options as $option)
                                        <label class="flex items-center gap-2 text-lg text-gray-800">
                                            <input type="checkbox" wire:click="toggleMulti({{ $question->id }}, {{ $option->id }})"
                                                @checked(in_array($option->id, $answers[$question->id] ?? []))
                                                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                            {{ $option->text }}
                                        </label>
                                    @endforeach
                                </div>
                            @elseif ($question->type === 'matching')
                                <div class="space-y-2">
                                    @foreach ($question->matching_pairs ?? [] as $pair)
                                        <div class="flex items-center gap-2 text-lg">
                                            <span class="text-gray-800 w-1/3">{{ $pair['left'] }}</span>
                                            <select wire:change="setMatch({{ $question->id }}, '{{ $pair['left'] }}', $event.target.value)"
                                                class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm flex-1">
                                                <option value="">{{ __('Select a match') }}</option>
                                                @foreach (collect($question->matching_pairs)->pluck('right')->shuffle($attempt->id + $question->id) as $rightOption)
                                                    <option value="{{ $rightOption }}" @selected(($answers[$question->id][$pair['left']] ?? null) === $rightOption)>{{ $rightOption }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endforeach
                                </div>
                            @elseif ($question->type === 'short_answer')
                                <x-text-input wire:model="answers.{{ $question->id }}" class="block w-full" />
                            @elseif ($question->type === 'numerical')
                                <x-text-input wire:model="answers.{{ $question->id }}" type="number" step="any" class="block w-full" />
                            @elseif ($question->type === 'essay')
                                <textarea wire:model="answers.{{ $question->id }}" rows="5"
                                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full"></textarea>
                            @elseif ($question->type === 'file_upload')
                                <input type="file" wire:model="files.{{ $question->id }}"
                                    class="block w-full text-lg text-gray-800 file:me-3 file:py-2 file:px-3 file:rounded-md file:border-0 file:bg-indigo-50 file:text-indigo-700">
                                <x-input-error :messages="$errors->get('files.'.$question->id)" class="mt-1" />
                            @endif
                        </div>
                    @endforeach

                    <div class="bg-white shadow-sm sm:rounded-lg p-4">
                        <x-primary-button wire:confirm="{{ __('Submit this attempt? You will not be able to change your answers afterwards.') }}">
                            {{ __('Submit Attempt') }}
                        </x-primary-button>
                    </div>
                </form>
            @endif
        </div>
        </x-protected-area>
    </div>
</div>
