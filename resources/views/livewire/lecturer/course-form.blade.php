<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $course ? __('Edit Course') : __('New Course') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if ($course)
                <div class="flex items-center justify-between bg-white shadow-sm sm:rounded-lg p-4">
                    <x-course-status-badge :status="$course->status" />

                    <div class="flex items-center gap-2">
                        @can('publish courses')
                            @if (in_array($course->status, [
                                \App\Models\Course::STATUS_DRAFT,
                                \App\Models\Course::STATUS_UNDER_REVIEW,
                                \App\Models\Course::STATUS_REVISION_REQUESTED,
                                \App\Models\Course::STATUS_APPROVED,
                                \App\Models\Course::STATUS_UNPUBLISHED,
                            ]))
                                <x-primary-button type="button" wire:click="publishNow" wire:confirm="{{ __('Publish this course now? It will appear on the site immediately, without a review step.') }}">
                                    {{ __('Publish Now') }}
                                </x-primary-button>
                            @endif
                        @endcan

                        @if (in_array($course->status, [\App\Models\Course::STATUS_DRAFT, \App\Models\Course::STATUS_REVISION_REQUESTED]))
                            <x-primary-button type="button" wire:click="submitForReview" wire:confirm="{{ __('Submit this course for review? You will not be able to edit it while it is under review.') }}">
                                {{ __('Submit for Review') }}
                            </x-primary-button>
                        @endif
                    </div>
                </div>

                @if ($published)
                    <div class="rounded-md bg-green-50 px-4 py-3 text-lg text-green-700">
                        {{ __('Course published. It is now visible on the site.') }}
                        <a href="{{ route('courses.show', $course->slug) }}" class="underline" target="_blank">{{ __('View public page') }}</a>
                    </div>
                @endif

                @if ($course->status === \App\Models\Course::STATUS_REVISION_REQUESTED && $course->revision_notes)
                    <div class="bg-amber-50 rounded-lg p-4 text-lg text-amber-800">
                        <p class="font-medium">{{ __('Revisions requested:') }}</p>
                        <p class="mt-1 whitespace-pre-line">{{ $course->revision_notes }}</p>
                    </div>
                @endif
            @endif

            @if ($saved)
                <div class="rounded-md bg-green-50 px-4 py-3 text-lg text-green-700">
                    {{ __('Course saved.') }}
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form wire:submit="save" class="space-y-4">
                    <div>
                        <x-input-label for="title" :value="__('Course Title')" />
                        <x-text-input wire:model="title" id="title" class="block mt-1 w-full" type="text" />
                        <x-input-error :messages="$errors->get('title')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="short_description" :value="__('Short Description')" />
                        <x-text-input wire:model="short_description" id="short_description" class="block mt-1 w-full" type="text" maxlength="255" />
                        <x-input-error :messages="$errors->get('short_description')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="full_description" :value="__('Full Description')" />
                        <textarea wire:model="full_description" id="full_description" rows="5"
                            class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full"></textarea>
                        <x-input-error :messages="$errors->get('full_description')" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="category_id" :value="__('Category')" />
                            <select wire:model="category_id" id="category_id" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full">
                                <option value="">{{ __('Select a category') }}</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('category_id')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="level" :value="__('Level')" />
                            <select wire:model="level" id="level" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full">
                                <option value="beginner">{{ __('Beginner') }}</option>
                                <option value="intermediate">{{ __('Intermediate') }}</option>
                                <option value="advanced">{{ __('Advanced') }}</option>
                            </select>
                            <x-input-error :messages="$errors->get('level')" class="mt-2" />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="teaching_language" :value="__('Teaching Language')" />
                            <select wire:model="teaching_language" id="teaching_language" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full">
                                <option value="en">{{ __('English') }}</option>
                                <option value="ar">{{ __('Arabic') }}</option>
                                <option value="both">{{ __('Arabic and English') }}</option>
                            </select>
                            <x-input-error :messages="$errors->get('teaching_language')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="delivery_format" :value="__('Delivery Format')" />
                            <select wire:model="delivery_format" id="delivery_format" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full">
                                <option value="live">{{ __('Live') }}</option>
                                <option value="recorded">{{ __('Recorded') }}</option>
                                <option value="blended">{{ __('Blended') }}</option>
                            </select>
                            <x-input-error :messages="$errors->get('delivery_format')" class="mt-2" />
                        </div>
                    </div>

                    @php
                        $platformCurrency = $course?->currency ?? \App\Models\Setting::get('branding.default_currency', config('platform.default_currency'));
                    @endphp
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="monthly_price" :value="__('Monthly Price (:currency)', ['currency' => $platformCurrency])" />
                            <x-text-input wire:model="monthly_price" id="monthly_price" class="block mt-1 w-full" type="number" step="0.01" min="0" placeholder="{{ __('Leave blank if free') }}" />
                            <x-input-error :messages="$errors->get('monthly_price')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="one_time_price" :value="__('One-time Price (:currency)', ['currency' => $platformCurrency])" />
                            <x-text-input wire:model="one_time_price" id="one_time_price" class="block mt-1 w-full" type="number" step="0.01" min="0" />
                            <x-input-error :messages="$errors->get('one_time_price')" class="mt-2" />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="trial_period_days" :value="__('Trial Period (days)')" />
                            <x-text-input wire:model="trial_period_days" id="trial_period_days" class="block mt-1 w-full" type="number" min="0" />
                            <x-input-error :messages="$errors->get('trial_period_days')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="max_students" :value="__('Maximum Students')" />
                            <x-text-input wire:model="max_students" id="max_students" class="block mt-1 w-full" type="number" min="1" placeholder="{{ __('Leave blank for unlimited') }}" />
                            <x-input-error :messages="$errors->get('max_students')" class="mt-2" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="image" :value="__('Course Image')" />
                        @if ($course?->image_path)
                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($course->image_path) }}" alt="" class="h-24 my-2 rounded">
                        @endif
                        <input wire:model="image" id="image" type="file" accept="image/*" class="block mt-1 w-full text-lg text-gray-800" />
                        <x-input-error :messages="$errors->get('image')" class="mt-2" />
                    </div>

                    <label class="flex items-center gap-2">
                        <input wire:model="certificate_available" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" />
                        <span class="text-lg text-gray-800">{{ __('Issue a certificate on completion') }}</span>
                    </label>

                    <div class="flex justify-between items-center">
                        <a href="{{ route('lecturer.courses.index') }}" wire:navigate class="text-lg text-gray-800 hover:text-gray-900 underline">
                            {{ __('Back to my courses') }}
                        </a>
                        <x-primary-button>{{ __('Save') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
