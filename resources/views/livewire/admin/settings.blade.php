<div>
    <div class="max-w-5xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div class="bg-white shadow sm:rounded-lg p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-1">{{ __('Platform Branding & Settings') }}</h2>
            <p class="text-lg text-gray-800 mb-6">{{ __('These values control the name, look, and defaults shown across the public site and dashboards.') }}</p>

            @if ($saved)
                <div class="mb-4 rounded-md bg-green-50 px-4 py-3 text-lg text-green-700">
                    {{ __('Settings saved.') }}
                </div>
            @endif

            <form wire:submit="save" class="space-y-6">
                <div>
                    <x-input-label for="platform_name" :value="__('Platform Name')" />
                    <x-text-input wire:model="platform_name" id="platform_name" class="block mt-1 w-full" type="text" />
                    <x-input-error :messages="$errors->get('platform_name')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="logo" :value="__('Logo')" />

                    @if ($current_logo_path)
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($current_logo_path) }}" alt="{{ __('Current logo') }}" class="h-12 my-2">
                    @endif

                    <input wire:model="logo" id="logo" type="file" accept="image/*"
                        class="block mt-1 w-full text-lg text-gray-800 file:me-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:bg-indigo-50 file:text-indigo-700" />
                    <x-input-error :messages="$errors->get('logo')" class="mt-2" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="primary_color" :value="__('Primary Color')" />
                        <input wire:model="primary_color" id="primary_color" type="color" class="block mt-1 w-full h-10 rounded-md border-gray-300" />
                        <x-input-error :messages="$errors->get('primary_color')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="secondary_color" :value="__('Secondary Color')" />
                        <input wire:model="secondary_color" id="secondary_color" type="color" class="block mt-1 w-full h-10 rounded-md border-gray-300" />
                        <x-input-error :messages="$errors->get('secondary_color')" class="mt-2" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="contact_email" :value="__('Contact Email')" />
                        <x-text-input wire:model="contact_email" id="contact_email" class="block mt-1 w-full" type="email" />
                        <x-input-error :messages="$errors->get('contact_email')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="contact_phone" :value="__('Contact Phone')" />
                        <x-text-input wire:model="contact_phone" id="contact_phone" class="block mt-1 w-full" type="tel" />
                        <x-input-error :messages="$errors->get('contact_phone')" class="mt-2" />
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <x-input-label for="default_locale" :value="__('Default Language')" />
                        <select wire:model="default_locale" id="default_locale" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full">
                            @foreach (config('platform.locales') as $code => $locale)
                                <option value="{{ $code }}">{{ $locale['native'] }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('default_locale')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="default_currency" :value="__('Default Currency')" />
                        <x-text-input wire:model="default_currency" id="default_currency" class="block mt-1 w-full uppercase" type="text" maxlength="3" />
                        <x-input-error :messages="$errors->get('default_currency')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="timezone" :value="__('Timezone')" />
                        <x-text-input wire:model="timezone" id="timezone" class="block mt-1 w-full" type="text" />
                        <x-input-error :messages="$errors->get('timezone')" class="mt-2" />
                    </div>
                </div>

                <div class="flex justify-end">
                    <x-primary-button>{{ __('Save') }}</x-primary-button>
                </div>
            </form>
        </div>

        <div class="bg-white shadow sm:rounded-lg p-6 mt-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-1">{{ __('Payment Gateway') }}</h2>
            <p class="text-lg text-gray-800 mb-6">{{ __('Choose which payment provider handles course subscriptions. Switching takes effect immediately for new checkouts.') }}</p>

            @if ($paymentGatewaySaved)
                <div class="mb-4 rounded-md bg-green-50 px-4 py-3 text-lg text-green-700">
                    {{ __('Payment gateway updated.') }}
                </div>
            @endif

            <form wire:submit="savePaymentGateway" class="space-y-4">
                <div>
                    <x-input-label for="payment_gateway" :value="__('Active Gateway')" />
                    <select wire:model="payment_gateway" id="payment_gateway" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full">
                        @foreach ($availableGateways as $driver)
                            <option value="{{ $driver }}">
                                {{ match ($driver) {
                                    'test' => __('Sandbox (no external account needed)'),
                                    'stripe' => __('Stripe'),
                                    'paypal' => __('PayPal'),
                                    default => ucfirst($driver),
                                } }}
                            </option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('payment_gateway')" class="mt-2" />
                    <p class="text-sm text-gray-800 mt-2">
                        {{ __('Stripe requires STRIPE_KEY and STRIPE_SECRET to be set in the environment before it can be selected.') }}
                        {{ __('PayPal requires PAYPAL_CLIENT_ID and PAYPAL_CLIENT_SECRET to be set in the environment before it can be selected.') }}
                    </p>
                </div>

                <div class="flex justify-end">
                    <x-primary-button>{{ __('Save') }}</x-primary-button>
                </div>
            </form>
        </div>

        <div class="bg-white shadow sm:rounded-lg p-6 mt-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-1">{{ __('Lecturer Commission Defaults') }}</h2>
            <p class="text-lg text-gray-800 mb-6">{{ __('The default share a lecturer earns from each successful payment. Set a different rate for a specific lecturer or course from the Commissions page.') }}</p>

            @if ($commissionSaved)
                <div class="mb-4 rounded-md bg-green-50 px-4 py-3 text-lg text-green-700">
                    {{ __('Commission defaults saved.') }}
                </div>
            @endif

            <form wire:submit="saveCommissionDefaults" class="space-y-4">
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <x-input-label for="commission_default_rate_type" :value="__('Rate Type')" />
                        <select wire:model="commission_default_rate_type" id="commission_default_rate_type" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full">
                            <option value="percentage">{{ __('Percentage') }}</option>
                            <option value="fixed">{{ __('Fixed amount') }}</option>
                        </select>
                        <x-input-error :messages="$errors->get('commission_default_rate_type')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="commission_default_rate_value" :value="__('Rate Value')" />
                        <x-text-input wire:model="commission_default_rate_value" id="commission_default_rate_value" class="block mt-1 w-full" type="number" step="0.01" min="0" />
                        <x-input-error :messages="$errors->get('commission_default_rate_value')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="commission_payout_threshold" :value="__('Payout Threshold')" />
                        <x-text-input wire:model="commission_payout_threshold" id="commission_payout_threshold" class="block mt-1 w-full" type="number" step="0.01" min="0" />
                        <x-input-error :messages="$errors->get('commission_payout_threshold')" class="mt-2" />
                    </div>
                </div>
                <p class="text-sm text-gray-800">{{ __('Payout threshold is the minimum unpaid earnings balance a lecturer must reach before they can request a payout.') }}</p>

                <div class="flex justify-end">
                    <x-primary-button>{{ __('Save') }}</x-primary-button>
                </div>
            </form>
        </div>

        <div class="bg-white shadow sm:rounded-lg p-6 mt-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-1">{{ __('Platform Configuration') }}</h2>
            <p class="text-lg text-gray-800 mb-6">{{ __('Operational settings that control policy versioning, live class access, and payment retry behaviour.') }}</p>

            @if ($platformConfigSaved)
                <div class="mb-4 rounded-md bg-green-50 px-4 py-3 text-lg text-green-700">
                    {{ __('Platform configuration saved.') }}
                </div>
            @endif

            <form wire:submit="savePlatformConfig" class="space-y-4">
                <div class="grid sm:grid-cols-3 gap-4">
                    <div>
                        <x-input-label for="policy_version" :value="__('Policy Version')" />
                        <x-text-input wire:model="policy_version" id="policy_version" class="block mt-1 w-full" />
                        <x-input-error :messages="$errors->get('policy_version')" class="mt-2" />
                        <p class="text-sm text-gray-800 mt-1">{{ __('Bump this when Terms or Privacy content changes materially. New consent records are stamped with the current version.') }}</p>
                    </div>
                    <div>
                        <x-input-label for="live_class_link_release_minutes" :value="__('Live Class Link Release (minutes)')" />
                        <x-text-input wire:model="live_class_link_release_minutes" id="live_class_link_release_minutes" class="block mt-1 w-full" type="number" min="1" max="1440" />
                        <x-input-error :messages="$errors->get('live_class_link_release_minutes')" class="mt-2" />
                        <p class="text-sm text-gray-800 mt-1">{{ __('How many minutes before a live class starts the student join link becomes visible.') }}</p>
                    </div>
                    <div>
                        <x-input-label for="payment_grace_period_days" :value="__('Payment Grace Period (days)')" />
                        <x-text-input wire:model="payment_grace_period_days" id="payment_grace_period_days" class="block mt-1 w-full" type="number" min="0" max="90" />
                        <x-input-error :messages="$errors->get('payment_grace_period_days')" class="mt-2" />
                        <p class="text-sm text-gray-800 mt-1">{{ __('How many days a subscription keeps access after a failed renewal payment.') }}</p>
                    </div>
                </div>

                <div class="flex justify-end">
                    <x-primary-button>{{ __('Save') }}</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</div>
