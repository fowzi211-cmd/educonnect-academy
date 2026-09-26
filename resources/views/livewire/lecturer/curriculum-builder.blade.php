<div>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Curriculum') }}: {{ $course->title }}
            </h2>
            <a href="{{ route('lecturer.courses.index') }}" wire:navigate class="text-lg text-gray-800 hover:text-gray-900 underline">
                {{ __('Back to my courses') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @foreach ($sections as $section)
                <div wire:key="section-{{ $section->id }}" class="bg-white shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center justify-between gap-3">
                        @if ($editingSectionId === $section->id)
                            <form wire:submit="saveSection" class="flex-1 flex gap-2">
                                <x-text-input wire:model="editingSectionTitle" class="block w-full" type="text" />
                                <x-primary-button>{{ __('Save') }}</x-primary-button>
                                <x-secondary-button type="button" wire:click="cancelEditSection">{{ __('Cancel') }}</x-secondary-button>
                            </form>
                        @else
                            <h3 class="font-semibold text-gray-900">{{ $section->title }}</h3>
                            <div class="flex items-center gap-1 text-lg">
                                <button wire:click="moveSection({{ $section->id }}, 'up')" class="px-2 py-1 text-gray-800 hover:text-gray-900">&uarr;</button>
                                <button wire:click="moveSection({{ $section->id }}, 'down')" class="px-2 py-1 text-gray-800 hover:text-gray-900">&darr;</button>
                                <button wire:click="startEditSection({{ $section->id }})" class="px-2 py-1 text-indigo-600 hover:text-indigo-800">{{ __('Edit') }}</button>
                                <button wire:click="deleteSection({{ $section->id }})" wire:confirm="{{ __('Delete this section and all its lessons?') }}" class="px-2 py-1 text-red-600 hover:text-red-800">{{ __('Delete') }}</button>
                            </div>
                        @endif
                    </div>

                    <div class="mt-4 space-y-2">
                        @foreach ($section->lessons as $lesson)
                            <div wire:key="lesson-{{ $lesson->id }}" class="border border-gray-100 rounded-md">
                                <div class="flex items-center justify-between gap-3 px-3 py-2">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm uppercase tracking-wide text-gray-600">{{ $lesson->content_type === 'video' ? __('Video') : __('Reading') }}</span>
                                        <span class="text-lg text-gray-900">{{ $lesson->title }}</span>
                                        @if ($lesson->content_type === 'video' && $lesson->video)
                                            <span class="text-sm text-green-700">&#10003; {{ __('Video uploaded') }}</span>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-1 text-lg">
                                        <button wire:click="moveLesson({{ $lesson->id }}, 'up')" class="px-2 py-1 text-gray-800 hover:text-gray-900">&uarr;</button>
                                        <button wire:click="moveLesson({{ $lesson->id }}, 'down')" class="px-2 py-1 text-gray-800 hover:text-gray-900">&darr;</button>
                                        <button wire:click="toggleExpand({{ $lesson->id }})" class="px-2 py-1 text-indigo-600 hover:text-indigo-800">
                                            {{ $expandedLessonId === $lesson->id ? __('Close') : __('Manage') }}
                                        </button>
                                        <button wire:click="deleteLesson({{ $lesson->id }})" wire:confirm="{{ __('Delete this lesson?') }}" class="px-2 py-1 text-red-600 hover:text-red-800">{{ __('Delete') }}</button>
                                    </div>
                                </div>

                                @if ($expandedLessonId === $lesson->id)
                                    <div class="border-t border-gray-100 px-3 py-4 bg-gray-50 space-y-4">
                                        @if ($lesson->description)
                                            <p class="text-lg text-gray-800">{{ $lesson->description }}</p>
                                        @endif

                                        @if ($lesson->content_type === 'video')
                                            <div>
                                                <h4 class="text-lg font-medium text-gray-900">{{ __('Video') }}</h4>
                                                @if ($lesson->video)
                                                    <p class="text-sm text-gray-800 mt-1">{{ __('Uploaded. Upload a new file below to replace it.') }}</p>
                                                @endif
                                                <form wire:submit="uploadVideo" class="mt-2 flex items-center gap-2">
                                                    <input wire:model="video" type="file" accept="video/mp4,video/quicktime,video/webm" class="text-lg text-gray-800" />
                                                    <x-primary-button>{{ __('Upload Video') }}</x-primary-button>
                                                </form>
                                                <x-input-error :messages="$errors->get('video')" class="mt-2" />
                                                <div wire:loading wire:target="video" class="text-sm text-gray-800 mt-1">{{ __('Uploading...') }}</div>
                                            </div>
                                        @endif

                                        <div>
                                            <h4 class="text-lg font-medium text-gray-900">{{ __('Lesson Materials') }}</h4>
                                            <p class="text-sm text-gray-600">{{ __('View-only for students (no download). Allowed: PDF, images, audio (mp3, m4a, wav, ogg) and video (mp4, webm).') }}</p>
                                            <ul class="mt-1 space-y-1">
                                                @forelse ($lesson->resources as $resource)
                                                    <li class="flex items-center justify-between text-lg">
                                                        <span class="text-gray-800">{{ $resource->original_name }}</span>
                                                        <button wire:click="deleteResource({{ $resource->id }})" wire:confirm="{{ __('Remove this resource?') }}" class="text-red-600 hover:text-red-800 text-sm">{{ __('Remove') }}</button>
                                                    </li>
                                                @empty
                                                    <li class="text-lg text-gray-800">{{ __('No resources yet.') }}</li>
                                                @endforelse
                                            </ul>
                                            <form wire:submit="uploadResources" class="mt-2 flex items-center gap-2">
                                                <input wire:model="resourceFiles" type="file" multiple accept=".pdf,.jpg,.jpeg,.png,.webp,.mp3,.m4a,.wav,.ogg,.mp4,.webm" class="text-lg text-gray-800" />
                                                <x-primary-button>{{ __('Upload') }}</x-primary-button>
                                            </form>
                                            <x-input-error :messages="$errors->get('resourceFiles.*')" class="mt-2" />
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach

                        @if ($addingLessonToSection === $section->id)
                            <form wire:submit="addLesson" class="border border-dashed border-gray-300 rounded-md p-3 space-y-2">
                                <x-text-input wire:model="newLessonTitle" placeholder="{{ __('Lesson title') }}" class="block w-full" type="text" />
                                <x-input-error :messages="$errors->get('newLessonTitle')" class="mt-1" />
                                <textarea wire:model="newLessonDescription" rows="2" placeholder="{{ __('Description (optional)') }}"
                                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full text-lg"></textarea>
                                <select wire:model="newLessonType" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-lg">
                                    <option value="reading">{{ __('Reading') }}</option>
                                    <option value="video">{{ __('Video') }}</option>
                                </select>
                                <div class="flex gap-2">
                                    <x-primary-button>{{ __('Add Lesson') }}</x-primary-button>
                                    <x-secondary-button type="button" wire:click="showAddLesson({{ $section->id }})">{{ __('Cancel') }}</x-secondary-button>
                                </div>
                            </form>
                        @else
                            <button wire:click="showAddLesson({{ $section->id }})" class="text-lg text-indigo-600 hover:text-indigo-800">
                                + {{ __('Add Lesson') }}
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="font-medium text-gray-900 mb-3">{{ __('Add Section') }}</h3>
                <form wire:submit="addSection" class="flex gap-2">
                    <x-text-input wire:model="newSectionTitle" placeholder="{{ __('Section title, e.g. Week 1') }}" class="block w-full" type="text" />
                    <x-primary-button>{{ __('Add') }}</x-primary-button>
                </form>
                <x-input-error :messages="$errors->get('newSectionTitle')" class="mt-2" />
            </div>
        </div>
    </div>
</div>
