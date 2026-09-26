<div>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Assignments') }}: {{ $course->title }}
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
                    <h3 class="font-medium text-gray-900 mb-4">{{ $editingId ? __('Edit Assignment') : __('New Assignment') }}</h3>
                    <form wire:submit="save" class="space-y-4">
                        <div>
                            <x-input-label for="a_title" :value="__('Title')" />
                            <x-text-input wire:model="title" id="a_title" class="block mt-1 w-full" />
                            <x-input-error :messages="$errors->get('title')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="a_description" :value="__('Instructions')" />
                            <textarea wire:model="description" id="a_description" rows="3"
                                class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full"></textarea>
                        </div>

                        <div class="grid sm:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="a_max_points" :value="__('Max Points')" />
                                <x-text-input wire:model="max_points" id="a_max_points" type="number" min="1" step="0.5" class="block mt-1 w-full" />
                                <x-input-error :messages="$errors->get('max_points')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="a_max_file_size" :value="__('Max File Size (KB)')" />
                                <x-text-input wire:model="max_file_size_kb" id="a_max_file_size" type="number" min="1" class="block mt-1 w-full" />
                                <x-input-error :messages="$errors->get('max_file_size_kb')" class="mt-2" />
                            </div>
                        </div>

                        <div class="grid sm:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="a_opens_at" :value="__('Opens At')" />
                                <input wire:model="opens_at" id="a_opens_at" type="datetime-local"
                                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full" />
                            </div>
                            <div>
                                <x-input-label for="a_due_at" :value="__('Due At')" />
                                <input wire:model="due_at" id="a_due_at" type="datetime-local"
                                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full" />
                                <x-input-error :messages="$errors->get('due_at')" class="mt-2" />
                            </div>
                        </div>

                        <div>
                            <x-input-label for="a_late_policy" :value="__('Late Submission Policy')" />
                            <select wire:model.live="late_policy" id="a_late_policy" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full">
                                <option value="not_allowed">{{ __('Not allowed after due date') }}</option>
                                <option value="allowed_with_penalty">{{ __('Allowed with a daily penalty') }}</option>
                                <option value="allowed_no_penalty">{{ __('Allowed, no penalty') }}</option>
                            </select>
                        </div>

                        @if ($late_policy === 'allowed_with_penalty')
                            <div>
                                <x-input-label for="a_penalty" :value="__('Penalty per late day (%)')" />
                                <x-text-input wire:model="late_penalty_percent_per_day" id="a_penalty" type="number" min="0" max="100" step="0.5" class="block mt-1 w-full" />
                                <x-input-error :messages="$errors->get('late_penalty_percent_per_day')" class="mt-2" />
                            </div>
                        @endif

                        <div>
                            <x-input-label for="a_file_types" :value="__('Allowed File Extensions (optional, comma-separated)')" />
                            <x-text-input wire:model="allowed_file_types" id="a_file_types" class="block mt-1 w-full" placeholder="pdf,docx,zip" />
                            <p class="text-sm text-gray-800 mt-1">{{ __('Leave blank to allow any file type.') }}</p>
                            <x-input-error :messages="$errors->get('allowed_file_types')" class="mt-2" />
                        </div>

                        <div class="border-t pt-4">
                            <x-input-label for="a_attachment" :value="__('Assignment Attachment (optional — worksheet, brief, etc.)')" />

                            @if ($editingAttachmentName && ! $removeAttachment)
                                <p class="text-lg text-gray-800 mt-1">
                                    {{ __('Current file') }}: <a href="{{ route('assignments.attachment.download', $editingId) }}" class="text-indigo-600 hover:text-indigo-800 underline">{{ $editingAttachmentName }}</a>
                                    <button type="button" wire:click="$set('removeAttachment', true)" class="text-red-600 hover:text-red-800 underline ms-2">{{ __('Remove') }}</button>
                                </p>
                            @elseif ($editingAttachmentName && $removeAttachment)
                                <p class="text-lg text-amber-700 mt-1">
                                    {{ __('This attachment will be removed when you save.') }}
                                    <button type="button" wire:click="$set('removeAttachment', false)" class="text-indigo-600 hover:text-indigo-800 underline ms-2">{{ __('Undo') }}</button>
                                </p>
                            @endif

                            <input type="file" wire:model="attachmentFile" id="a_attachment"
                                class="block w-full text-lg text-gray-800 file:me-3 file:py-2 file:px-3 file:rounded-md file:border-0 file:bg-indigo-50 file:text-indigo-700 mt-2">
                            <p class="text-sm text-gray-800 mt-1">{{ __('Max 10 MB. Uploading a new file replaces the current one.') }}</p>
                            <x-input-error :messages="$errors->get('attachmentFile')" class="mt-2" />
                        </div>

                        <div class="flex gap-2">
                            <x-primary-button>{{ __('Save') }}</x-primary-button>
                            <x-secondary-button type="button" wire:click="cancelForm">{{ __('Cancel') }}</x-secondary-button>
                        </div>
                    </form>
                </div>
            @else
                <div class="flex justify-end">
                    <x-primary-button wire:click="newForm">{{ __('New Assignment') }}</x-primary-button>
                </div>
            @endif

            <x-input-error :messages="$errors->get('delete')" class="px-1" />

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 text-lg">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Title') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Due') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Submissions') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Status') }}</th>
                            <th class="px-4 py-2"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($assignments as $assignment)
                            <tr wire:key="assignment-{{ $assignment->id }}">
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $assignment->title }}</td>
                                <td class="px-4 py-3 text-gray-800">{{ $assignment->due_at?->format('Y-m-d H:i') ?? __('No due date') }}</td>
                                <td class="px-4 py-3 text-gray-800">{{ $assignment->submissions_count }}</td>
                                <td class="px-4 py-3">
                                    <span @class([
                                        'inline-flex items-center rounded-full px-2.5 py-0.5 text-sm font-medium',
                                        'bg-green-100 text-green-800' => $assignment->is_published,
                                        'bg-gray-100 text-gray-800' => ! $assignment->is_published,
                                    ])>
                                        {{ $assignment->is_published ? __('Published') : __('Draft') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-end space-x-2 rtl:space-x-reverse">
                                    @if ($assignment->submissions_count > 0)
                                        <a href="{{ route('lecturer.assignments.submissions', $assignment) }}" wire:navigate class="text-indigo-600 hover:text-indigo-800 underline">{{ __('Submissions') }}</a>
                                    @endif
                                    <button wire:click="edit({{ $assignment->id }})" class="text-indigo-600 hover:text-indigo-800 underline">{{ __('Edit') }}</button>
                                    <button wire:click="togglePublish({{ $assignment->id }})" class="text-gray-800 hover:text-gray-800 underline">
                                        {{ $assignment->is_published ? __('Unpublish') : __('Publish') }}
                                    </button>
                                    @if ($assignment->submissions_count === 0)
                                        <button wire:click="delete({{ $assignment->id }})" wire:confirm="{{ __('Delete this assignment?') }}" class="text-red-600 hover:text-red-800 underline">{{ __('Delete') }}</button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-gray-800">{{ __('No assignments yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
