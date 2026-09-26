<?php

use App\Models\ConsentRecord;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $name = '';
    public string $email = '';
    public string $mobile_number = '';
    public string $country = '';
    public string $preferred_language = 'en';
    public string $password = '';
    public string $password_confirmation = '';
    public bool $terms_accepted = false;

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'mobile_number' => ['required', 'string', 'max:30'],
            'country' => ['required', 'string', 'max:100'],
            'preferred_language' => ['required', 'string', 'in:'.implode(',', array_keys(config('platform.locales')))],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
            'terms_accepted' => ['accepted'],
        ]);

        $user = DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            $user->assignRole('student');

            $user->profile()->create([
                'mobile_number' => $validated['mobile_number'],
                'country' => $validated['country'],
                'preferred_language' => $validated['preferred_language'],
            ]);

            ConsentRecord::create([
                'user_id' => $user->id,
                'policy_type' => 'terms_and_privacy',
                'policy_version' => Setting::get('platform.policy_version', config('platform.policy_version')),
                'accepted_at' => now(),
                'ip_address' => request()->ip(),
            ]);

            return $user;
        });

        event(new Registered($user));

        Auth::login($user);

        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <form wire:submit="register">
        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input wire:model="name" id="name" class="block mt-1 w-full" type="text" name="name" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input wire:model="email" id="email" class="block mt-1 w-full" type="email" name="email" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Mobile Number -->
        <div class="mt-4">
            <x-input-label for="mobile_number" :value="__('Mobile Number')" />
            <x-text-input wire:model="mobile_number" id="mobile_number" class="block mt-1 w-full" type="tel" name="mobile_number" required autocomplete="tel" />
            <x-input-error :messages="$errors->get('mobile_number')" class="mt-2" />
        </div>

        <!-- Country -->
        <div class="mt-4">
            <x-input-label for="country" :value="__('Country')" />
            <x-text-input wire:model="country" id="country" class="block mt-1 w-full" type="text" name="country" required autocomplete="country-name" />
            <x-input-error :messages="$errors->get('country')" class="mt-2" />
        </div>

        <!-- Preferred Language -->
        <div class="mt-4">
            <x-input-label for="preferred_language" :value="__('Preferred Language')" />
            <select wire:model="preferred_language" id="preferred_language" name="preferred_language" required
                class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full">
                @foreach (config('platform.locales') as $code => $locale)
                    <option value="{{ $code }}">{{ $locale['native'] }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('preferred_language')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-password-input wire:model="password" id="password" class="block mt-1 w-full"
                            name="password"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

            <x-password-input wire:model="password_confirmation" id="password_confirmation" class="block mt-1 w-full"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <!-- Terms Acceptance -->
        <div class="mt-4">
            <label class="flex items-start gap-2">
                <input wire:model="terms_accepted" type="checkbox" name="terms_accepted" class="mt-1 rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" />
                <span class="text-sm text-gray-600">
                    {{ __('I agree to the') }}
                    <a href="{{ route('terms') }}" target="_blank" class="underline">{{ __('Terms and Conditions') }}</a>
                    {{ __('and') }}
                    <a href="{{ route('privacy') }}" target="_blank" class="underline">{{ __('Privacy Policy') }}</a>
                </span>
            </label>
            <x-input-error :messages="$errors->get('terms_accepted')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}" wire:navigate>
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
</div>
