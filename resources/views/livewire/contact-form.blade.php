<div class="max-w-xl">
    @if ($sent)
        <div class="mb-6 rounded-md bg-green-50 px-4 py-3 text-sm text-green-700">
            {{ __("Thanks for reaching out — we'll reply to your email address soon.") }}
        </div>
    @endif

    <form wire:submit="send" class="space-y-4">
        <div>
            <x-input-label for="contact_name" :value="__('Name')" />
            <x-text-input wire:model="name" id="contact_name" class="block mt-1 w-full" type="text" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="contact_email" :value="__('Email')" />
            <x-text-input wire:model="email" id="contact_email" class="block mt-1 w-full" type="email" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="contact_subject" :value="__('Subject')" />
            <x-text-input wire:model="subject" id="contact_subject" class="block mt-1 w-full" type="text" />
            <x-input-error :messages="$errors->get('subject')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="contact_message" :value="__('Message')" />
            <textarea wire:model="message" id="contact_message" rows="5"
                class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full"></textarea>
            <x-input-error :messages="$errors->get('message')" class="mt-2" />
        </div>

        <x-primary-button>{{ __('Send Message') }}</x-primary-button>
    </form>
</div>
