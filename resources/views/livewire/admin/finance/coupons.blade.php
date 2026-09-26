<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Finance') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @include('livewire.admin.finance._nav')

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="font-medium text-gray-900 mb-4">{{ __('Create Coupon') }}</h3>
                <form wire:submit="create" class="grid sm:grid-cols-3 lg:grid-cols-6 gap-3 items-start">
                    <div>
                        <x-input-label for="code" :value="__('Code')" />
                        <x-text-input wire:model="code" id="code" class="mt-1 block w-full uppercase" placeholder="WELCOME20" />
                        <x-input-error :messages="$errors->get('code')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label for="type" :value="__('Type')" />
                        <select wire:model="type" id="type" class="mt-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full">
                            <option value="percentage">{{ __('Percentage') }}</option>
                            <option value="fixed">{{ __('Fixed amount') }}</option>
                        </select>
                    </div>
                    <div>
                        <x-input-label for="value" :value="__('Value')" />
                        <x-text-input wire:model="value" id="value" type="number" step="0.01" class="mt-1 block w-full" />
                        <x-input-error :messages="$errors->get('value')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label for="courseId" :value="__('Course (optional)')" />
                        <select wire:model="courseId" id="courseId" class="mt-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full">
                            <option value="">{{ __('Any course') }}</option>
                            @foreach ($courses as $course)
                                <option value="{{ $course->id }}">{{ $course->title }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('courseId')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label for="studentEmail" :value="__('Student (optional)')" />
                        <x-text-input wire:model="studentEmail" id="studentEmail" type="email" class="mt-1 block w-full" placeholder="{{ __('Student email') }}" />
                        <x-input-error :messages="$errors->get('studentEmail')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label for="maxRedemptions" :value="__('Usage limit (optional)')" />
                        <x-text-input wire:model="maxRedemptions" id="maxRedemptions" type="number" min="1" class="mt-1 block w-full" />
                        <x-input-error :messages="$errors->get('maxRedemptions')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label for="expiresAt" :value="__('Expires (optional)')" />
                        <x-text-input wire:model="expiresAt" id="expiresAt" type="date" class="mt-1 block w-full" />
                        <x-input-error :messages="$errors->get('expiresAt')" class="mt-1" />
                    </div>
                    <div class="self-end">
                        <x-primary-button>{{ __('Create Coupon') }}</x-primary-button>
                    </div>
                </form>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <x-input-error :messages="$errors->get('delete')" class="p-4" />
                <table class="min-w-full divide-y divide-gray-200 text-lg">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Code') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Discount') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Restriction') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Usage') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Expires') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Status') }}</th>
                            <th class="px-4 py-2"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($coupons as $coupon)
                            <tr wire:key="coupon-{{ $coupon->id }}">
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $coupon->code }}</td>
                                <td class="px-4 py-3 text-gray-800">
                                    {{ $coupon->type === 'percentage' ? $coupon->value.'%' : number_format($coupon->value, 2) }}
                                </td>
                                <td class="px-4 py-3 text-gray-800">
                                    {{ $coupon->course->title ?? '' }}
                                    {{ $coupon->user->email ?? '' }}
                                    @if (! $coupon->course && ! $coupon->user)
                                        &mdash;
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-800">
                                    {{ $coupon->times_redeemed }}{{ $coupon->max_redemptions ? ' / '.$coupon->max_redemptions : '' }}
                                </td>
                                <td class="px-4 py-3 text-gray-800">{{ $coupon->expires_at?->format('Y-m-d') ?? __('Never') }}</td>
                                <td class="px-4 py-3">
                                    <span @class([
                                        'inline-flex items-center rounded-full px-2.5 py-0.5 text-sm font-medium',
                                        'bg-green-100 text-green-800' => $coupon->is_active,
                                        'bg-gray-100 text-gray-800' => ! $coupon->is_active,
                                    ])>
                                        {{ $coupon->is_active ? __('Active') : __('Inactive') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-end space-x-2 rtl:space-x-reverse">
                                    <button wire:click="edit({{ $coupon->id }})" class="text-indigo-600 hover:text-indigo-800 underline">{{ __('Edit') }}</button>
                                    <button wire:click="toggleActive({{ $coupon->id }})" class="text-gray-800 hover:text-gray-800 underline">
                                        {{ $coupon->is_active ? __('Deactivate') : __('Activate') }}
                                    </button>
                                    @if ($coupon->redemptions_count === 0)
                                        <button wire:click="delete({{ $coupon->id }})" wire:confirm="{{ __('Delete this coupon?') }}" class="text-red-600 hover:text-red-800 underline">{{ __('Delete') }}</button>
                                    @endif
                                </td>
                            </tr>

                            @if ($editingId === $coupon->id)
                                <tr>
                                    <td colspan="7" class="px-4 py-4 bg-indigo-50">
                                        <form wire:submit="update" class="grid sm:grid-cols-3 lg:grid-cols-6 gap-3 items-start">
                                            <div>
                                                <x-input-label for="editingCode" :value="__('Code')" />
                                                <x-text-input wire:model="editingCode" id="editingCode" class="mt-1 block w-full uppercase" />
                                                <x-input-error :messages="$errors->get('editingCode')" class="mt-1" />
                                            </div>
                                            <div>
                                                <x-input-label for="editingType" :value="__('Type')" />
                                                <select wire:model="editingType" id="editingType" class="mt-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full">
                                                    <option value="percentage">{{ __('Percentage') }}</option>
                                                    <option value="fixed">{{ __('Fixed amount') }}</option>
                                                </select>
                                            </div>
                                            <div>
                                                <x-input-label for="editingValue" :value="__('Value')" />
                                                <x-text-input wire:model="editingValue" id="editingValue" type="number" step="0.01" class="mt-1 block w-full" />
                                                <x-input-error :messages="$errors->get('editingValue')" class="mt-1" />
                                            </div>
                                            <div>
                                                <x-input-label for="editingCourseId" :value="__('Course (optional)')" />
                                                <select wire:model="editingCourseId" id="editingCourseId" class="mt-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full">
                                                    <option value="">{{ __('Any course') }}</option>
                                                    @foreach ($courses as $course)
                                                        <option value="{{ $course->id }}">{{ $course->title }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div>
                                                <x-input-label for="editingStudentEmail" :value="__('Student (optional)')" />
                                                <x-text-input wire:model="editingStudentEmail" id="editingStudentEmail" type="email" class="mt-1 block w-full" />
                                                <x-input-error :messages="$errors->get('editingStudentEmail')" class="mt-1" />
                                            </div>
                                            <div>
                                                <x-input-label for="editingMaxRedemptions" :value="__('Usage limit (optional)')" />
                                                <x-text-input wire:model="editingMaxRedemptions" id="editingMaxRedemptions" type="number" min="1" class="mt-1 block w-full" />
                                            </div>
                                            <div>
                                                <x-input-label for="editingExpiresAt" :value="__('Expires (optional)')" />
                                                <x-text-input wire:model="editingExpiresAt" id="editingExpiresAt" type="date" class="mt-1 block w-full" />
                                            </div>
                                            <div class="flex gap-2 self-end">
                                                <x-primary-button>{{ __('Save') }}</x-primary-button>
                                                <x-secondary-button type="button" wire:click="cancelEdit">{{ __('Cancel') }}</x-secondary-button>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-6 text-center text-gray-800">{{ __('No coupons yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
