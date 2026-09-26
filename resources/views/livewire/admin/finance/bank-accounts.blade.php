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
                <h3 class="font-medium text-gray-900 mb-4">{{ __('Add Bank Account') }}</h3>
                <p class="text-lg text-gray-800 mb-4">{{ __('Active accounts shown here are displayed to students during checkout when they choose bank transfer.') }}</p>
                <form wire:submit="create" class="grid sm:grid-cols-2 lg:grid-cols-4 gap-3 items-start">
                    <div>
                        <x-input-label for="bankName" :value="__('Bank Name')" />
                        <x-text-input wire:model="bankName" id="bankName" class="mt-1 block w-full" />
                        <x-input-error :messages="$errors->get('bankName')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label for="accountName" :value="__('Account Name')" />
                        <x-text-input wire:model="accountName" id="accountName" class="mt-1 block w-full" dir="ltr" />
                        <x-input-error :messages="$errors->get('accountName')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label for="accountNumber" :value="__('Account Number')" />
                        <x-text-input wire:model="accountNumber" id="accountNumber" class="mt-1 block w-full" dir="ltr" />
                        <x-input-error :messages="$errors->get('accountNumber')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label for="iban" :value="__('IBAN (optional)')" />
                        <x-text-input wire:model="iban" id="iban" class="mt-1 block w-full" dir="ltr" />
                        <x-input-error :messages="$errors->get('iban')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label for="swiftCode" :value="__('SWIFT Code (optional)')" />
                        <x-text-input wire:model="swiftCode" id="swiftCode" class="mt-1 block w-full" dir="ltr" />
                        <x-input-error :messages="$errors->get('swiftCode')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label for="currency" :value="__('Currency')" />
                        <x-text-input wire:model="currency" id="currency" class="mt-1 block w-full uppercase" dir="ltr" maxlength="3" />
                        <x-input-error :messages="$errors->get('currency')" class="mt-1" />
                    </div>
                    <div class="lg:col-span-2">
                        <x-input-label for="notes" :value="__('Notes (optional)')" />
                        <x-text-input wire:model="notes" id="notes" class="mt-1 block w-full" placeholder="{{ __('Shown to students, e.g. transfer instructions') }}" />
                        <x-input-error :messages="$errors->get('notes')" class="mt-1" />
                    </div>
                    <div class="self-end">
                        <x-primary-button>{{ __('Add Account') }}</x-primary-button>
                    </div>
                </form>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 text-lg">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Bank Name') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Account Name') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Account Number') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Currency') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Status') }}</th>
                            <th class="px-4 py-2"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($accounts as $account)
                            <tr wire:key="bank-account-{{ $account->id }}">
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $account->bank_name }}</td>
                                <td class="px-4 py-3 text-gray-800" dir="ltr">{{ $account->account_name }}</td>
                                <td class="px-4 py-3 text-gray-800" dir="ltr">{{ $account->account_number }}</td>
                                <td class="px-4 py-3 text-gray-800" dir="ltr">{{ $account->currency }}</td>
                                <td class="px-4 py-3">
                                    <span @class([
                                        'inline-flex items-center rounded-full px-2.5 py-0.5 text-sm font-medium',
                                        'bg-green-100 text-green-800' => $account->is_active,
                                        'bg-gray-100 text-gray-800' => ! $account->is_active,
                                    ])>
                                        {{ $account->is_active ? __('Active') : __('Inactive') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-end space-x-2 rtl:space-x-reverse">
                                    <button wire:click="edit({{ $account->id }})" class="text-indigo-600 hover:text-indigo-800 underline">{{ __('Edit') }}</button>
                                    <button wire:click="toggleActive({{ $account->id }})" class="text-gray-800 hover:text-gray-800 underline">
                                        {{ $account->is_active ? __('Deactivate') : __('Activate') }}
                                    </button>
                                </td>
                            </tr>

                            @if ($editingId === $account->id)
                                <tr>
                                    <td colspan="6" class="px-4 py-4 bg-indigo-50">
                                        <form wire:submit="update" class="grid sm:grid-cols-2 lg:grid-cols-4 gap-3 items-start">
                                            <div>
                                                <x-input-label for="editingBankName" :value="__('Bank Name')" />
                                                <x-text-input wire:model="editingBankName" id="editingBankName" class="mt-1 block w-full" />
                                                <x-input-error :messages="$errors->get('editingBankName')" class="mt-1" />
                                            </div>
                                            <div>
                                                <x-input-label for="editingAccountName" :value="__('Account Name')" />
                                                <x-text-input wire:model="editingAccountName" id="editingAccountName" class="mt-1 block w-full" dir="ltr" />
                                                <x-input-error :messages="$errors->get('editingAccountName')" class="mt-1" />
                                            </div>
                                            <div>
                                                <x-input-label for="editingAccountNumber" :value="__('Account Number')" />
                                                <x-text-input wire:model="editingAccountNumber" id="editingAccountNumber" class="mt-1 block w-full" dir="ltr" />
                                                <x-input-error :messages="$errors->get('editingAccountNumber')" class="mt-1" />
                                            </div>
                                            <div>
                                                <x-input-label for="editingIban" :value="__('IBAN (optional)')" />
                                                <x-text-input wire:model="editingIban" id="editingIban" class="mt-1 block w-full" dir="ltr" />
                                                <x-input-error :messages="$errors->get('editingIban')" class="mt-1" />
                                            </div>
                                            <div>
                                                <x-input-label for="editingSwiftCode" :value="__('SWIFT Code (optional)')" />
                                                <x-text-input wire:model="editingSwiftCode" id="editingSwiftCode" class="mt-1 block w-full" dir="ltr" />
                                                <x-input-error :messages="$errors->get('editingSwiftCode')" class="mt-1" />
                                            </div>
                                            <div>
                                                <x-input-label for="editingCurrency" :value="__('Currency')" />
                                                <x-text-input wire:model="editingCurrency" id="editingCurrency" class="mt-1 block w-full uppercase" dir="ltr" maxlength="3" />
                                                <x-input-error :messages="$errors->get('editingCurrency')" class="mt-1" />
                                            </div>
                                            <div class="lg:col-span-2">
                                                <x-input-label for="editingNotes" :value="__('Notes (optional)')" />
                                                <x-text-input wire:model="editingNotes" id="editingNotes" class="mt-1 block w-full" />
                                                <x-input-error :messages="$errors->get('editingNotes')" class="mt-1" />
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
                                <td colspan="6" class="px-4 py-6 text-center text-gray-800">{{ __('No bank accounts yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
