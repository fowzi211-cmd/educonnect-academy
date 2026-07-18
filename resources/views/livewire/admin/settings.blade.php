<div>
    <div class="max-w-3xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div class="bg-white shadow sm:rounded-lg p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-1">{{ __('Platform Branding & Settings') }}</h2>
            <p class="text-sm text-gray-600 mb-6">{{ __('These values control the name, look, and defaults shown across the public site and dashboards.') }}</p>

            @if ($saved)
                <div class="mb-4 rounded-md bg-green-50 px-4 py-3 text-sm text-green-700">
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
                        class="block mt-1 w-full text-sm text-gray-600 file:me-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:bg-indigo-50 file:text-indigo-700" />
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
    </div>
</div>
