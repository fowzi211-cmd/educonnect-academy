<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Bank Transfer') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="font-semibold text-gray-900">{{ __('Course') }}</h3>
                <p class="mt-1 text-sm text-gray-600">{{ $subscription->course->title }}</p>
                <p class="mt-3 text-sm text-gray-600">
                    {{ __('Amount due') }}: <span class="font-semibold text-gray-900">{{ number_format($subscription->monthly_price, 2) }} {{ $subscription->currency }}</span>
                </p>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="font-semibold text-gray-900">{{ __('1. Transfer to one of our bank accounts') }}</h3>

                @if ($bankAccounts->isEmpty())
                    <p class="mt-3 text-sm text-gray-600">{{ __('No bank accounts are currently available. Please contact support.') }}</p>
                @else
                    <div class="mt-3 space-y-3">
                        @foreach ($bankAccounts as $account)
                            <label class="block rounded-lg border p-4 cursor-pointer transition {{ (string) $bankAccountId === (string) $account->id ? 'border-brand-primary ring-2 ring-brand-primary bg-indigo-50/40' : 'border-gray-300 hover:border-gray-400' }}">
                                <div class="flex items-start gap-3">
                                    <input type="radio" wire:model="bankAccountId" value="{{ $account->id }}" class="mt-1 text-brand-primary focus:ring-brand-primary">
                                    <div class="flex-1 text-sm">
                                        <p class="font-medium text-gray-900">{{ $account->bank_name }}</p>
                                        <dl class="mt-2 grid grid-cols-3 gap-x-2 gap-y-1 text-gray-600">
                                            <dt>{{ __('Account Name') }}</dt>
                                            <dd class="col-span-2 text-gray-900" dir="ltr">{{ $account->account_name }}</dd>
                                            <dt>{{ __('Account Number') }}</dt>
                                            <dd class="col-span-2 text-gray-900" dir="ltr">{{ $account->account_number }}</dd>
                                            @if ($account->iban)
                                                <dt>{{ __('IBAN') }}</dt>
                                                <dd class="col-span-2 text-gray-900" dir="ltr">{{ $account->iban }}</dd>
                                            @endif
                                            @if ($account->swift_code)
                                                <dt>{{ __('SWIFT Code') }}</dt>
                                                <dd class="col-span-2 text-gray-900" dir="ltr">{{ $account->swift_code }}</dd>
                                            @endif
                                            <dt>{{ __('Currency') }}</dt>
                                            <dd class="col-span-2 text-gray-900" dir="ltr">{{ $account->currency }}</dd>
                                        </dl>
                                        @if ($account->notes)
                                            <p class="mt-2 text-xs text-gray-600">{{ $account->notes }}</p>
                                        @endif
                                    </div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                    <x-input-error :messages="$errors->get('bankAccountId')" class="mt-2" />
                @endif
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="font-semibold text-gray-900">{{ __('2. Enter your transfer details') }}</h3>
                <p class="mt-1 text-sm text-gray-600">{{ __('This helps us match your receipt to your payment quickly.') }}</p>

                <div class="mt-3 grid sm:grid-cols-2 gap-3">
                    <div>
                        <x-input-label for="declaredAmount" :value="__('Amount Transferred')" />
                        <x-text-input wire:model="declaredAmount" id="declaredAmount" type="number" step="0.01" min="0" class="mt-1 block w-full" dir="ltr" />
                        <x-input-error :messages="$errors->get('declaredAmount')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label for="bankReferenceNumber" :value="__('Bank Reference Number')" />
                        <x-text-input wire:model="bankReferenceNumber" id="bankReferenceNumber" class="mt-1 block w-full" dir="ltr" placeholder="{{ __('The unique transfer number given by your bank') }}" />
                        <x-input-error :messages="$errors->get('bankReferenceNumber')" class="mt-1" />
                    </div>
                </div>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="font-semibold text-gray-900">{{ __('3. Upload your transfer receipt') }}</h3>
                <p class="mt-1 text-sm text-gray-600">{{ __('Accepted formats: JPG, PNG, PDF. Maximum size 5MB.') }}</p>

                <div class="mt-3">
                    <input type="file" wire:model="receipt" accept=".jpg,.jpeg,.png,.pdf" class="block w-full text-sm text-gray-700 file:me-3 file:rounded-md file:border-0 file:bg-gray-100 file:px-3 file:py-2 file:text-sm file:font-medium hover:file:bg-gray-200">
                    <div wire:loading wire:target="receipt" class="mt-2 text-xs text-gray-600">{{ __('Uploading...') }}</div>
                    <x-input-error :messages="$errors->get('receipt')" class="mt-2" />
                </div>

                <div class="mt-6">
                    <x-primary-button wire:click="submitReceipt" wire:loading.attr="disabled" wire:target="submitReceipt,receipt" class="w-full justify-center">
                        {{ __('Submit for Review') }}
                    </x-primary-button>
                    <p class="text-xs text-gray-600 mt-2 text-center">
                        {{ __("We'll review your receipt and activate your subscription within 1 business day.") }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
