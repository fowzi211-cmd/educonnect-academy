<div>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $assignment->title }}
            </h2>
            <a href="{{ route('my-courses.classroom', $assignment->course) }}" wire:navigate class="text-lg text-gray-800 hover:text-gray-900 underline">
                {{ __('Back to classroom') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-4">
                @if ($assignment->description)
                    <p class="text-gray-800 whitespace-pre-line">{{ $assignment->description }}</p>
                @endif

                @if ($assignment->attachment_path)
                    <a href="{{ route('assignments.attachment.download', $assignment) }}" class="inline-flex items-center gap-1 text-lg text-indigo-600 hover:text-indigo-800 underline">
                        &#128206; {{ __('Download assignment attachment') }}: {{ $assignment->attachment_name }}
                    </a>
                @endif

                <dl class="grid sm:grid-cols-2 gap-3 text-lg">
                    <div>
                        <dt class="text-gray-800">{{ __('Due date') }}</dt>
                        <dd class="text-gray-900">{{ $assignment->due_at?->format('Y-m-d H:i') ?? __('No due date') }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-800">{{ __('Max points') }}</dt>
                        <dd class="text-gray-900">{{ $assignment->max_points }}</dd>
                    </div>
                    @if ($assignment->allowed_file_types)
                        <div>
                            <dt class="text-gray-800">{{ __('Allowed file types') }}</dt>
                            <dd class="text-gray-900">{{ $assignment->allowed_file_types }}</dd>
                        </div>
                    @endif
                </dl>

                @if ($submission)
                    <div class="border-t pt-4">
                        <p class="text-lg text-gray-800">
                            {{ __('Submitted') }}: <a href="{{ route('assignment-submissions.download', $submission) }}" class="text-indigo-600 hover:text-indigo-800 underline">{{ $submission->file_name }}</a>
                            {{ $submission->submitted_at->format('Y-m-d H:i') }}
                            @if ($submission->is_late)
                                <span class="text-amber-600">({{ __('Late') }})</span>
                            @endif
                        </p>

                        @if ($submission->status === 'graded')
                            <p class="mt-2 text-lg font-medium text-gray-900">
                                {{ __('Score') }}: {{ $submission->score }} / {{ $assignment->max_points }}
                            </p>
                            @if ($submission->feedback)
                                <p class="mt-1 text-lg text-gray-800 bg-gray-50 rounded p-3">{{ $submission->feedback }}</p>
                            @endif
                        @else
                            <p class="mt-2 text-lg text-amber-700">{{ __('Awaiting grading.') }}</p>
                        @endif
                    </div>
                @endif

                @if (! $submission || $submission->status !== 'graded')
                    <form wire:submit="submit" class="border-t pt-4 space-y-3">
                        <x-input-label for="file" :value="$submission ? __('Resubmit File') : __('Upload File')" />
                        <input type="file" wire:model="file" id="file"
                            class="block w-full text-lg text-gray-800 file:me-3 file:py-2 file:px-3 file:rounded-md file:border-0 file:bg-indigo-50 file:text-indigo-700">
                        <x-input-error :messages="$errors->get('file')" class="mt-1" />
                        <x-primary-button>{{ __('Submit') }}</x-primary-button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
