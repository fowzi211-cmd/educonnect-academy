<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Review Lecturer Application') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="font-medium text-gray-900">{{ $lecturerProfile->user->name }}</h3>
                        <p class="text-lg text-gray-800">{{ $lecturerProfile->user->email }}</p>
                    </div>
                    <span @class([
                        'inline-flex items-center rounded-full px-2.5 py-0.5 text-sm font-medium',
                        'bg-amber-100 text-amber-800' => $lecturerProfile->status === 'pending',
                        'bg-green-100 text-green-800' => $lecturerProfile->status === 'approved',
                        'bg-red-100 text-red-800' => $lecturerProfile->status === 'rejected',
                    ])>
                        {{ ucfirst($lecturerProfile->status) }}
                    </span>
                </div>

                <dl class="mt-6 space-y-4 text-lg">
                    <div>
                        <dt class="font-medium text-gray-800">{{ __('Professional Headline') }}</dt>
                        <dd class="mt-1 text-gray-800">{{ $lecturerProfile->headline }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-800">{{ __('Biography') }}</dt>
                        <dd class="mt-1 text-gray-800 whitespace-pre-line">{{ $lecturerProfile->biography }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-800">{{ __('Qualifications and Experience') }}</dt>
                        <dd class="mt-1 text-gray-800 whitespace-pre-line">{{ $lecturerProfile->qualifications }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-800">{{ __('Areas of Expertise') }}</dt>
                        <dd class="mt-1 text-gray-800">{{ $lecturerProfile->areas_of_expertise }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-800">{{ __('Verification Documents') }}</dt>
                        <dd class="mt-1">
                            @forelse ($lecturerProfile->documents as $document)
                                <a href="{{ route('admin.lecturer-documents.download', $document) }}" class="block text-indigo-600 hover:text-indigo-800 underline">
                                    {{ $document->original_name }}
                                </a>
                            @empty
                                <span class="text-gray-800">{{ __('None uploaded.') }}</span>
                            @endforelse
                        </dd>
                    </div>
                </dl>
            </div>

            @if ($lecturerProfile->status === 'pending')
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center gap-3">
                        <x-primary-button wire:click="approve" wire:confirm="{{ __('Approve this lecturer application?') }}">
                            {{ __('Approve') }}
                        </x-primary-button>
                    </div>

                    <div class="mt-6 border-t border-gray-100 pt-6">
                        <x-input-label for="rejection_reason" :value="__('Rejection Reason')" />
                        <textarea wire:model="rejection_reason" id="rejection_reason" rows="3"
                            class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full"
                            placeholder="{{ __('Explain what is missing or needs revision.') }}"></textarea>
                        <x-input-error :messages="$errors->get('rejection_reason')" class="mt-2" />

                        <x-danger-button class="mt-3" wire:click="reject" wire:confirm="{{ __('Reject this lecturer application?') }}">
                            {{ __('Reject') }}
                        </x-danger-button>
                    </div>
                </div>
            @elseif ($lecturerProfile->status === 'rejected' && $lecturerProfile->rejection_reason)
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <h4 class="font-medium text-gray-900">{{ __('Rejection Reason') }}</h4>
                    <p class="mt-1 text-lg text-gray-800">{{ $lecturerProfile->rejection_reason }}</p>
                </div>
            @endif

            <a href="{{ route('admin.lecturer-applications.index') }}" wire:navigate class="text-lg text-gray-800 hover:text-gray-900 underline">
                {{ __('Back to applications') }}
            </a>
        </div>
    </div>
</div>
