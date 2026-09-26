<div>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Completion Criteria') }}: {{ $course->title }}
            </h2>
            <a href="{{ route('lecturer.courses.index') }}" wire:navigate class="text-lg text-gray-800 hover:text-gray-900 underline">
                {{ __('Back to my courses') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <label class="flex items-center gap-2 text-lg font-medium text-gray-900 mb-4">
                    <input type="checkbox" wire:model.live="enabled" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    {{ __('Track completion for this course') }}
                </label>

                @if ($enabled)
                    <form wire:submit="save" class="space-y-4">
                        <p class="text-lg text-gray-800">{{ __('A student completes this course once all the requirements below are met. Leave a field blank to skip that requirement.') }}</p>

                        <div>
                            <x-input-label for="cc_lessons" :value="__('Minimum % of lessons completed')" />
                            <x-text-input wire:model="min_lessons_percent" id="cc_lessons" type="number" min="0" max="100" class="mt-1 block w-full" />
                            <x-input-error :messages="$errors->get('min_lessons_percent')" class="mt-1" />
                        </div>

                        <div>
                            <x-input-label for="cc_video" :value="__('Minimum % of videos watched')" />
                            <x-text-input wire:model="min_video_watch_percent" id="cc_video" type="number" min="0" max="100" class="mt-1 block w-full" />
                            <x-input-error :messages="$errors->get('min_video_watch_percent')" class="mt-1" />
                        </div>

                        <div>
                            <x-input-label for="cc_attendance" :value="__('Minimum % of live classes attended')" />
                            <x-text-input wire:model="min_attendance_percent" id="cc_attendance" type="number" min="0" max="100" class="mt-1 block w-full" />
                            <x-input-error :messages="$errors->get('min_attendance_percent')" class="mt-1" />
                        </div>

                        <label class="flex items-center gap-2 text-lg text-gray-800">
                            <input type="checkbox" wire:model="require_assignments" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            {{ __('Require submission of all published assignments') }}
                        </label>

                        <label class="flex items-center gap-2 text-lg text-gray-800">
                            <input type="checkbox" wire:model="require_passing_assessments" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            {{ __('Require a passing attempt on all published graded assessments') }}
                        </label>

                        <label class="flex items-center gap-2 text-lg text-gray-800">
                            <input type="checkbox" wire:model="require_payment_good_standing" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            {{ __('Require the student\'s payment account to be in good standing') }}
                        </label>

                        <x-primary-button>{{ __('Save') }}</x-primary-button>
                    </form>
                @else
                    <p class="text-lg text-gray-800">{{ __('Completion is not currently tracked for this course — no certificates will be issued automatically.') }}</p>
                    <x-primary-button wire:click="save" class="mt-4">{{ __('Save') }}</x-primary-button>
                @endif
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden mt-6">
                <h3 class="px-6 pt-4 text-lg font-medium text-gray-900">{{ __('Students Who Completed This Course') }}</h3>
                <table class="min-w-full divide-y divide-gray-200 text-lg mt-2">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Student') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Completed On') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($completions as $completion)
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-900">{{ $completion->user->name }}</div>
                                    <div class="text-gray-800">{{ $completion->user->email }}</div>
                                </td>
                                <td class="px-4 py-3 text-gray-800">{{ $completion->completed_at->format('Y-m-d') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="px-4 py-6 text-center text-gray-800">{{ __('No students have completed this course yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
