<div>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Live Classes') }}: {{ $course->title }}
            </h2>
            <a href="{{ route('lecturer.courses.index') }}" wire:navigate class="text-sm text-gray-600 hover:text-gray-900 underline">
                {{ __('Back to my courses') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if ($showForm)
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <h3 class="font-medium text-gray-900 mb-4">{{ $editingId ? __('Edit Live Class') : __('Schedule Live Class') }}</h3>
                    <form wire:submit="save" class="space-y-4">
                        <div>
                            <x-input-label for="lc_title" :value="__('Title')" />
                            <x-text-input wire:model="title" id="lc_title" class="block mt-1 w-full" type="text" />
                            <x-input-error :messages="$errors->get('title')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="lc_description" :value="__('Description')" />
                            <textarea wire:model="description" id="lc_description" rows="2"
                                class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full"></textarea>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="lc_starts_at" :value="__('Starts At')" />
                                <input wire:model="starts_at" id="lc_starts_at" type="datetime-local"
                                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full" />
                                <x-input-error :messages="$errors->get('starts_at')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="lc_ends_at" :value="__('Ends At')" />
                                <input wire:model="ends_at" id="lc_ends_at" type="datetime-local"
                                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full" />
                                <x-input-error :messages="$errors->get('ends_at')" class="mt-2" />
                            </div>
                        </div>

                        <p class="text-xs text-gray-500">{{ __('Times are in the platform timezone (:tz).', ['tz' => config('app.timezone')]) }}</p>

                        <div>
                            <x-input-label for="lc_provider" :value="__('Provider')" />
                            <select wire:model="provider" id="lc_provider" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full">
                                <option value="zoom">Zoom</option>
                                <option value="google_meet">Google Meet</option>
                                <option value="teams">Microsoft Teams</option>
                                <option value="jitsi">Jitsi Meet</option>
                                <option value="other">{{ __('Other') }}</option>
                            </select>
                            <x-input-error :messages="$errors->get('provider')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="lc_meeting_link" :value="__('Student Join Link')" />
                            <x-text-input wire:model="meeting_link" id="lc_meeting_link" class="block mt-1 w-full" type="url" placeholder="https://" />
                            <p class="text-xs text-gray-500 mt-1">{{ __('Shown to enrolled students shortly before the class starts.') }}</p>
                            <x-input-error :messages="$errors->get('meeting_link')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="lc_host_link" :value="__('Host Link (optional)')" />
                            <x-text-input wire:model="host_link" id="lc_host_link" class="block mt-1 w-full" type="url" placeholder="https://" />
                            <p class="text-xs text-gray-500 mt-1">{{ __('Only visible to you. Never shown to students.') }}</p>
                            <x-input-error :messages="$errors->get('host_link')" class="mt-2" />
                        </div>

                        <div class="flex gap-2">
                            <x-primary-button>{{ __('Save') }}</x-primary-button>
                            <x-secondary-button type="button" wire:click="cancelForm">{{ __('Cancel') }}</x-secondary-button>
                        </div>
                    </form>
                </div>
            @else
                <div class="flex justify-end">
                    <x-primary-button wire:click="newForm">{{ __('Schedule Live Class') }}</x-primary-button>
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-start font-medium text-gray-500">{{ __('Title') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-500">{{ __('Starts') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-500">{{ __('Status') }}</th>
                            <th class="px-4 py-2"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($liveClasses as $liveClass)
                            <tr wire:key="lc-{{ $liveClass->id }}">
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $liveClass->title }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $liveClass->starts_at->format('Y-m-d H:i') }}</td>
                                <td class="px-4 py-3">
                                    <span @class([
                                        'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium',
                                        'bg-blue-100 text-blue-800' => $liveClass->status === 'scheduled',
                                        'bg-red-100 text-red-800' => $liveClass->status === 'cancelled',
                                        'bg-gray-100 text-gray-600' => $liveClass->status === 'completed',
                                    ])>
                                        {{ ucfirst($liveClass->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-end space-x-2 rtl:space-x-reverse">
                                    <a href="{{ route('lecturer.live-classes.attendance', $liveClass) }}" wire:navigate class="text-indigo-600 hover:text-indigo-800 underline">{{ __('Attendance') }}</a>
                                    @if ($liveClass->status === 'scheduled')
                                        <button wire:click="edit({{ $liveClass->id }})" class="text-indigo-600 hover:text-indigo-800 underline">{{ __('Edit') }}</button>
                                        <button wire:click="startCancel({{ $liveClass->id }})" class="text-red-600 hover:text-red-800 underline">{{ __('Cancel') }}</button>
                                    @endif
                                </td>
                            </tr>

                            @if ($cancellingId === $liveClass->id)
                                <tr>
                                    <td colspan="4" class="px-4 py-3 bg-red-50">
                                        <x-input-label for="cancellation_reason" :value="__('Cancellation Reason')" />
                                        <textarea wire:model="cancellation_reason" id="cancellation_reason" rows="2"
                                            class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full"></textarea>
                                        <x-input-error :messages="$errors->get('cancellation_reason')" class="mt-2" />
                                        <div class="mt-2 flex gap-2">
                                            <x-danger-button wire:click="confirmCancel">{{ __('Confirm Cancellation') }}</x-danger-button>
                                            <x-secondary-button wire:click="$set('cancellingId', null)">{{ __('Never mind') }}</x-secondary-button>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-gray-500">{{ __('No live classes scheduled yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
