<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Enrolments') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="font-medium text-gray-900 mb-4">{{ __('Grant Complimentary Access') }}</h3>
                <form wire:submit="enrol" class="flex flex-wrap gap-3 items-start">
                    <div class="flex-1 min-w-[200px]">
                        <x-text-input wire:model="studentEmail" placeholder="{{ __('Student email') }}" class="block w-full" type="email" />
                        <x-input-error :messages="$errors->get('studentEmail')" class="mt-1" />
                    </div>
                    <div class="flex-1 min-w-[200px]">
                        <select wire:model="courseId" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full">
                            <option value="">{{ __('Select a course') }}</option>
                            @foreach ($courses as $course)
                                <option value="{{ $course->id }}">{{ $course->title }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('courseId')" class="mt-1" />
                    </div>
                    <x-primary-button>{{ __('Enrol') }}</x-primary-button>
                </form>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 text-lg">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Student') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Course') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Source') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Status') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Enrolled') }}</th>
                            <th class="px-4 py-2"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($enrolments as $enrolment)
                            <tr wire:key="enrol-{{ $enrolment->id }}">
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-900">{{ $enrolment->user->name }}</div>
                                    <div class="text-gray-800">{{ $enrolment->user->email }}</div>
                                </td>
                                <td class="px-4 py-3 text-gray-800">{{ $enrolment->course->title }}</td>
                                <td class="px-4 py-3 text-gray-800">{{ ucfirst($enrolment->source) }}</td>
                                <td class="px-4 py-3">
                                    <span @class([
                                        'inline-flex items-center rounded-full px-2.5 py-0.5 text-sm font-medium',
                                        'bg-green-100 text-green-800' => $enrolment->status === 'active',
                                        'bg-gray-100 text-gray-800' => $enrolment->status !== 'active',
                                    ])>
                                        {{ ucfirst($enrolment->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-800">{{ $enrolment->enrolled_at->format('Y-m-d') }}</td>
                                <td class="px-4 py-3 text-end">
                                    @if ($enrolment->status === 'active')
                                        <button wire:click="startCancel({{ $enrolment->id }})" class="text-red-600 hover:text-red-800 underline">{{ __('Remove Access') }}</button>
                                    @endif
                                </td>
                            </tr>

                            @if ($cancellingId === $enrolment->id)
                                <tr>
                                    <td colspan="6" class="px-4 py-3 bg-red-50">
                                        <x-input-label for="cancelReason" :value="__('Reason')" />
                                        <textarea wire:model="cancelReason" id="cancelReason" rows="2"
                                            class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full"></textarea>
                                        <x-input-error :messages="$errors->get('cancelReason')" class="mt-2" />
                                        <div class="mt-2 flex gap-2">
                                            <x-danger-button wire:click="confirmCancel">{{ __('Confirm Removal') }}</x-danger-button>
                                            <x-secondary-button wire:click="$set('cancellingId', null)">{{ __('Never mind') }}</x-secondary-button>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-6 text-center text-gray-800">{{ __('No enrolments yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div>
                {{ $enrolments->links() }}
            </div>
        </div>
    </div>
</div>
