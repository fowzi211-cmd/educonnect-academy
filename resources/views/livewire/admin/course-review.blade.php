<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Review Course') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="font-medium text-lg text-gray-900">{{ $course->title }}</h3>
                        <p class="text-lg text-gray-800">
                            {{ __('by') }} {{ $course->creator->name }}
                            &middot; {{ $course->category?->name ?? __('No category') }}
                        </p>
                    </div>
                    <x-course-status-badge :status="$course->status" />
                </div>

                <dl class="mt-6 space-y-4 text-lg">
                    <div>
                        <dt class="font-medium text-gray-800">{{ __('Short Description') }}</dt>
                        <dd class="mt-1 text-gray-800">{{ $course->short_description }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-800">{{ __('Full Description') }}</dt>
                        <dd class="mt-1 text-gray-800 whitespace-pre-line">{{ $course->full_description }}</dd>
                    </div>
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <dt class="font-medium text-gray-800">{{ __('Level') }}</dt>
                            <dd class="mt-1 text-gray-800">{{ ucfirst($course->level) }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-800">{{ __('Delivery Format') }}</dt>
                            <dd class="mt-1 text-gray-800">{{ ucfirst($course->delivery_format) }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-800">{{ __('Monthly Price') }}</dt>
                            <dd class="mt-1 text-gray-800">
                                {{ $course->monthly_price ? number_format($course->monthly_price, 2).' '.$course->currency : __('Free') }}
                            </dd>
                        </div>
                    </div>
                </dl>
            </div>

            @if ($course->status === \App\Models\Course::STATUS_UNDER_REVIEW)
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <h4 class="font-medium text-gray-900 mb-3">{{ __('Review Decision') }}</h4>

                    <x-input-label for="comments" :value="__('Comments (required to request revisions)')" />
                    <textarea wire:model="comments" id="comments" rows="3"
                        class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full"></textarea>
                    <x-input-error :messages="$errors->get('comments')" class="mt-2" />

                    <div class="mt-4 flex gap-3">
                        <x-primary-button wire:click="approve" wire:confirm="{{ __('Approve this course?') }}">
                            {{ __('Approve') }}
                        </x-primary-button>
                        <x-secondary-button wire:click="requestRevision">
                            {{ __('Request Revision') }}
                        </x-secondary-button>
                    </div>
                </div>
            @endif

            @can('publish courses')
                @if ($course->status === \App\Models\Course::STATUS_APPROVED)
                    <div class="bg-white shadow-sm sm:rounded-lg p-6">
                        <h4 class="font-medium text-gray-900 mb-3">{{ __('Publication') }}</h4>
                        <x-primary-button wire:click="publish" wire:confirm="{{ __('Publish this course to the public catalogue?') }}">
                            {{ __('Publish') }}
                        </x-primary-button>
                    </div>
                @elseif ($course->status === \App\Models\Course::STATUS_PUBLISHED)
                    <div class="bg-white shadow-sm sm:rounded-lg p-6">
                        <h4 class="font-medium text-gray-900 mb-3">{{ __('Publication') }}</h4>
                        <x-secondary-button wire:click="unpublish" wire:confirm="{{ __('Unpublish this course?') }}">
                            {{ __('Unpublish') }}
                        </x-secondary-button>
                    </div>
                @endif

                @if (in_array($course->status, ['published', 'approved']))
                    <div class="bg-white shadow-sm sm:rounded-lg p-6">
                        <h4 class="font-medium text-gray-900 mb-3">{{ __('Suspend Course') }}</h4>
                        <x-input-label for="statusNote" :value="__('Reason')" />
                        <textarea wire:model="statusNote" id="statusNote" rows="2"
                            class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full"></textarea>
                        <x-input-error :messages="$errors->get('statusNote')" class="mt-2" />
                        <x-danger-button class="mt-3" wire:click="suspend" wire:confirm="{{ __('Suspend this course?') }}">
                            {{ __('Suspend') }}
                        </x-danger-button>
                    </div>
                @endif

                @if (in_array($course->status, ['published', 'unpublished', 'suspended']))
                    <div class="bg-white shadow-sm sm:rounded-lg p-6">
                        <h4 class="font-medium text-gray-900 mb-3">{{ __('Archive Course') }}</h4>
                        <x-secondary-button wire:click="archive" wire:confirm="{{ __('Archive this course? This is a long-term storage state.') }}">
                            {{ __('Archive') }}
                        </x-secondary-button>
                    </div>
                @endif
            @endcan

            @if ($course->revision_notes && $course->status !== \App\Models\Course::STATUS_UNDER_REVIEW)
                <div class="bg-amber-50 rounded-lg p-4 text-lg text-amber-800">
                    <p class="font-medium">{{ __('Latest note:') }}</p>
                    <p class="mt-1 whitespace-pre-line">{{ $course->revision_notes }}</p>
                </div>
            @endif

            <a href="{{ route('admin.courses.index') }}" wire:navigate class="text-lg text-gray-800 hover:text-gray-900 underline">
                {{ __('Back to review queue') }}
            </a>
        </div>
    </div>
</div>
