<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Lecturer Application') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                @if ($existingProfile && $existingProfile->status === \App\Models\LecturerProfile::STATUS_APPROVED)
                    <h3 class="font-medium text-gray-900">{{ __('Your teaching profile is live') }}</h3>
                    <p class="mt-2 text-gray-700">{{ __('You are an approved lecturer on Dr. Nada Center.') }}</p>
                    <a href="{{ route('lecturers.show', auth()->user()) }}" class="mt-3 inline-block underline text-indigo-600" wire:navigate>{{ __('View my public profile') }}</a>

                @elseif ($submitted || ($existingProfile && $existingProfile->status === \App\Models\LecturerProfile::STATUS_PENDING))
                    <h3 class="font-medium text-gray-900">{{ __('Application submitted') }}</h3>
                    <p class="mt-2 text-sm text-gray-600">{{ __('Thanks for applying. An administrator will review your application and you will be notified of the decision.') }}</p>
                    @can('manage lecturer applications')
                        <p class="mt-2 text-sm text-gray-600">
                            {{ __('As an administrator you can review it yourself:') }}
                            <a href="{{ route('admin.lecturer-applications.index') }}" class="underline text-indigo-600" wire:navigate>{{ __('Lecturer Applications') }}</a>
                        </p>
                    @endcan

                @else
                    @if ($existingProfile && $existingProfile->status === \App\Models\LecturerProfile::STATUS_REJECTED)
                        <div class="mb-6 rounded-md bg-amber-50 px-4 py-3 text-sm text-amber-800">
                            <p class="font-medium">{{ __('Your previous application was not approved.') }}</p>
                            @if ($existingProfile->rejection_reason)
                                <p class="mt-1">{{ $existingProfile->rejection_reason }}</p>
                            @endif
                            <p class="mt-1">{{ __('You can update your details below and resubmit.') }}</p>
                        </div>
                    @else
                        <p class="text-sm text-gray-600 mb-6">{{ __('Tell us about your teaching background. An administrator will review your application before you can create courses.') }}</p>
                    @endif

                    <form wire:submit="apply" class="space-y-4">
                        <div>
                            <x-input-label for="headline" :value="__('Professional Headline')" />
                            <x-text-input wire:model="headline" id="headline" class="block mt-1 w-full" type="text" placeholder="e.g. Senior Mathematics Lecturer" />
                            <x-input-error :messages="$errors->get('headline')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="biography" :value="__('Biography')" />
                            <textarea wire:model="biography" id="biography" rows="4"
                                class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full"></textarea>
                            <x-input-error :messages="$errors->get('biography')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="qualifications" :value="__('Qualifications and Experience')" />
                            <textarea wire:model="qualifications" id="qualifications" rows="4"
                                class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full"></textarea>
                            <x-input-error :messages="$errors->get('qualifications')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="areas_of_expertise" :value="__('Areas of Expertise')" />
                            <x-text-input wire:model="areas_of_expertise" id="areas_of_expertise" class="block mt-1 w-full" type="text" placeholder="e.g. Algebra, Statistics, Calculus" />
                            <x-input-error :messages="$errors->get('areas_of_expertise')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="photo" :value="__('Photo')" />
                            <input wire:model="photo" id="photo" type="file" accept="image/*"
                                class="block mt-1 w-full text-sm text-gray-600" />
                            <x-input-error :messages="$errors->get('photo')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="documents" :value="__('Verification Documents (certificates, ID)')" />
                            <input wire:model="documents" id="documents" type="file" multiple accept=".pdf,.jpg,.jpeg,.png"
                                class="block mt-1 w-full text-sm text-gray-600" />
                            <x-input-error :messages="$errors->get('documents.*')" class="mt-2" />
                        </div>

                        <div class="flex justify-end">
                            <x-primary-button>{{ __('Submit Application') }}</x-primary-button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
