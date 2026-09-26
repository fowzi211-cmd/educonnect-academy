<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Users') }}
        </h2>
    </x-slot>

    @php
        $roleLabels = [
            'super_administrator' => __('Super Administrator'),
            'administrator' => __('Administrator'),
            'finance_officer' => __('Finance Officer'),
            'support_officer' => __('Support Officer'),
            'course_reviewer' => __('Course Reviewer'),
            'lecturer' => __('Lecturer'),
            'student' => __('Student'),
        ];
    @endphp

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <div class="flex items-start justify-between gap-4 mb-1">
                    <h3 class="font-medium text-gray-900">{{ __('Add User') }}</h3>
                    <button type="button" wire:click="toggleBulkImport" class="text-lg text-indigo-600 hover:text-indigo-800 underline whitespace-nowrap">
                        {{ $showBulkImport ? __('Add one at a time instead') : __('Bulk Import') }}
                    </button>
                </div>

                @if (! $showBulkImport)
                    <p class="text-lg text-gray-800 mb-4">{{ __('Creates the account directly and emails them a link to set their own password.') }}</p>
                    <form wire:submit="create" class="grid sm:grid-cols-2 lg:grid-cols-4 gap-3 items-start">
                        <div>
                            <x-input-label for="name" :value="__('Name')" />
                            <x-text-input wire:model="name" id="name" class="mt-1 block w-full" />
                            <x-input-error :messages="$errors->get('name')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="email" :value="__('Email')" />
                            <x-text-input wire:model="email" id="email" type="email" class="mt-1 block w-full" dir="ltr" />
                            <x-input-error :messages="$errors->get('email')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="role" :value="__('Role')" />
                            <select wire:model="role" id="role" class="mt-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full">
                                @foreach ($assignableRoles as $roleOption)
                                    <option value="{{ $roleOption }}">{{ $roleLabels[$roleOption] ?? \Illuminate\Support\Str::headline($roleOption) }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('role')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="preferredLanguage" :value="__('Preferred Language')" />
                            <select wire:model="preferredLanguage" id="preferredLanguage" class="mt-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full">
                                @foreach (config('platform.locales') as $code => $locale)
                                    <option value="{{ $code }}">{{ $locale['native'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="lg:col-span-4">
                            <x-primary-button>{{ __('Create Account') }}</x-primary-button>
                        </div>
                    </form>
                @else
                    <p class="text-lg text-gray-800 mb-4">
                        {{ __('One person per line: name, email, role. Role is optional — defaults to student. Example:') }}
                        <br>
                        <code class="text-sm text-gray-600" dir="ltr">Ahmed Ali, ahmed@example.com, student</code>
                    </p>
                    <textarea wire:model="bulkImportText" rows="6" dir="ltr" placeholder="Ahmed Ali, ahmed@example.com, student&#10;Sara Hassan, sara@example.com, lecturer"
                        class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full font-mono text-lg"></textarea>
                    <x-input-error :messages="$errors->get('bulkImportText')" class="mt-1" />

                    <div class="mt-3">
                        <x-primary-button wire:click="runBulkImport" wire:loading.attr="disabled" wire:target="runBulkImport">
                            {{ __('Import') }}
                        </x-primary-button>
                    </div>

                    @if ($bulkImportCreated > 0)
                        <div class="mt-4 rounded-md bg-green-50 px-4 py-3 text-lg text-green-700">
                            {{ __('Accounts created: :count', ['count' => $bulkImportCreated]) }}
                        </div>
                    @endif

                    @if (count($bulkImportFailures))
                        <div class="mt-4">
                            <p class="text-lg font-medium text-red-700 mb-2">
                                {{ __('Rows skipped: :count', ['count' => count($bulkImportFailures)]) }}
                            </p>
                            <ul class="space-y-1 text-lg">
                                @foreach ($bulkImportFailures as $failure)
                                    <li class="rounded-md bg-red-50 px-3 py-2 text-red-700">
                                        <span class="font-mono text-sm" dir="ltr">{{ $failure['line'] }}</span>
                                        — {{ $failure['error'] }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                @endif
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <div class="flex flex-wrap gap-3 items-end mb-4">
                    <div>
                        <x-input-label for="search" :value="__('Search')" />
                        <x-text-input wire:model.live.debounce.400ms="search" id="search" class="mt-1" placeholder="{{ __('Name or email') }}" />
                    </div>
                    <div>
                        <x-input-label for="roleFilter" :value="__('Role')" />
                        <select wire:model.live="roleFilter" id="roleFilter" class="mt-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="">{{ __('All') }}</option>
                            @foreach ($allRoles as $roleOption)
                                <option value="{{ $roleOption }}">{{ $roleLabels[$roleOption] ?? \Illuminate\Support\Str::headline($roleOption) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <x-input-error :messages="$errors->get('delete')" class="mb-3" />

                <table class="min-w-full divide-y divide-gray-200 text-lg">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Name') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Email') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Role') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Joined') }}</th>
                            <th class="px-4 py-2"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($users as $user)
                            <tr wire:key="user-{{ $user->id }}">
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $user->name }}</td>
                                <td class="px-4 py-3 text-gray-800" dir="ltr">{{ $user->email }}</td>
                                <td class="px-4 py-3">
                                    @foreach ($user->roles as $userRole)
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-sm font-medium bg-indigo-100 text-indigo-800">
                                            {{ $roleLabels[$userRole->name] ?? \Illuminate\Support\Str::headline($userRole->name) }}
                                        </span>
                                    @endforeach
                                    @if ($user->isSuspended())
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-sm font-medium bg-red-100 text-red-800">
                                            {{ __('Suspended') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-800">{{ $user->created_at->format('Y-m-d') }}</td>
                                <td class="px-4 py-3 text-end space-x-2 rtl:space-x-reverse whitespace-nowrap">
                                    @if ($user->id !== auth()->id() && ! ($user->hasRole('super_administrator') && ! auth()->user()->hasRole('super_administrator')))
                                        <button wire:click="startEdit({{ $user->id }})" class="text-indigo-600 hover:text-indigo-800 underline">{{ __('Edit') }}</button>
                                        <button wire:click="startChangeRole({{ $user->id }})" class="text-indigo-600 hover:text-indigo-800 underline">{{ __('Change Role') }}</button>
                                        @if ($user->isSuspended())
                                            <button wire:click="unsuspend({{ $user->id }})" class="text-green-700 hover:text-green-900 underline">{{ __('Activate') }}</button>
                                        @else
                                            <button wire:click="suspend({{ $user->id }})" wire:confirm="{{ __('Suspend this account? The user will be logged out and unable to sign in.') }}" class="text-amber-700 hover:text-amber-900 underline">{{ __('Suspend') }}</button>
                                        @endif
                                        <button wire:click="delete({{ $user->id }})" wire:confirm="{{ __('Permanently delete this account? This cannot be undone.') }}" class="text-red-600 hover:text-red-800 underline">{{ __('Delete') }}</button>
                                    @endif
                                </td>
                            </tr>

                            @if ($editingId === $user->id)
                                <tr>
                                    <td colspan="5" class="px-4 py-4 bg-indigo-50">
                                        <form wire:submit="confirmEdit" class="grid sm:grid-cols-2 lg:grid-cols-4 gap-3 items-start">
                                            <div>
                                                <x-input-label for="editingName" :value="__('Name')" />
                                                <x-text-input wire:model="editingName" id="editingName" class="mt-1 block w-full" />
                                                <x-input-error :messages="$errors->get('editingName')" class="mt-1" />
                                            </div>
                                            <div>
                                                <x-input-label for="editingEmail" :value="__('Email')" />
                                                <x-text-input wire:model="editingEmail" id="editingEmail" type="email" class="mt-1 block w-full" dir="ltr" />
                                                <x-input-error :messages="$errors->get('editingEmail')" class="mt-1" />
                                            </div>
                                            <div>
                                                <x-input-label for="editingPreferredLanguage" :value="__('Preferred Language')" />
                                                <select wire:model="editingPreferredLanguage" id="editingPreferredLanguage" class="mt-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full">
                                                    @foreach (config('platform.locales') as $code => $locale)
                                                        <option value="{{ $code }}">{{ $locale['native'] }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="flex gap-2 self-end">
                                                <x-primary-button>{{ __('Save') }}</x-primary-button>
                                                <x-secondary-button type="button" wire:click="cancelEdit">{{ __('Cancel') }}</x-secondary-button>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            @endif

                            @if ($changingRoleId === $user->id)
                                <tr>
                                    <td colspan="5" class="px-4 py-4 bg-indigo-50">
                                        <div class="flex items-end gap-3">
                                            <div>
                                                <x-input-label for="newRole" :value="__('New role for :name', ['name' => $user->name])" />
                                                <select wire:model="newRole" id="newRole" class="mt-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block">
                                                    @foreach ($assignableRoles as $roleOption)
                                                        <option value="{{ $roleOption }}">{{ $roleLabels[$roleOption] ?? \Illuminate\Support\Str::headline($roleOption) }}</option>
                                                    @endforeach
                                                </select>
                                                <x-input-error :messages="$errors->get('newRole')" class="mt-1" />
                                            </div>
                                            <x-primary-button wire:click="confirmChangeRole">{{ __('Save') }}</x-primary-button>
                                            <x-secondary-button wire:click="cancelChangeRole">{{ __('Cancel') }}</x-secondary-button>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-gray-800">{{ __('No users found.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div>
                {{ $users->links() }}
            </div>
        </div>
    </div>
</div>
