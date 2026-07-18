<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use App\Models\Setting;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class Settings extends Component
{
    use WithFileUploads;

    public string $platform_name = '';
    public string $primary_color = '';
    public string $secondary_color = '';
    public string $contact_email = '';
    public string $contact_phone = '';
    public string $default_locale = '';
    public string $default_currency = '';
    public string $timezone = '';

    public $logo;

    public ?string $current_logo_path = null;

    public bool $saved = false;

    public function mount(): void
    {
        $this->platform_name = Setting::get('branding.platform_name', config('app.name'));
        $this->primary_color = Setting::get('branding.primary_color', '#4f46e5');
        $this->secondary_color = Setting::get('branding.secondary_color', '#0ea5e9');
        $this->contact_email = Setting::get('branding.contact_email', '');
        $this->contact_phone = Setting::get('branding.contact_phone', '');
        $this->default_locale = Setting::get('branding.default_locale', 'en');
        $this->default_currency = Setting::get('branding.default_currency', config('platform.default_currency'));
        $this->timezone = Setting::get('branding.timezone', config('app.timezone'));
        $this->current_logo_path = Setting::get('branding.logo_path');
    }

    public function save(): void
    {
        $validated = $this->validate([
            'platform_name' => ['required', 'string', 'max:255'],
            'primary_color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'secondary_color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'contact_email' => ['required', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:30'],
            'default_locale' => ['required', 'string', 'in:'.implode(',', array_keys(config('platform.locales')))],
            'default_currency' => ['required', 'string', 'size:3'],
            'timezone' => ['required', 'timezone'],
            'logo' => ['nullable', 'image', 'max:2048'],
        ]);

        $old = [
            'platform_name' => Setting::get('branding.platform_name'),
            'primary_color' => Setting::get('branding.primary_color'),
            'secondary_color' => Setting::get('branding.secondary_color'),
            'contact_email' => Setting::get('branding.contact_email'),
            'contact_phone' => Setting::get('branding.contact_phone'),
            'default_locale' => Setting::get('branding.default_locale'),
            'default_currency' => Setting::get('branding.default_currency'),
            'timezone' => Setting::get('branding.timezone'),
        ];

        Setting::set('branding.platform_name', $validated['platform_name'], 'branding');
        Setting::set('branding.primary_color', $validated['primary_color'], 'branding');
        Setting::set('branding.secondary_color', $validated['secondary_color'], 'branding');
        Setting::set('branding.contact_email', $validated['contact_email'], 'branding');
        Setting::set('branding.contact_phone', $validated['contact_phone'] ?? '', 'branding');
        Setting::set('branding.default_locale', $validated['default_locale'], 'branding');
        Setting::set('branding.default_currency', strtoupper($validated['default_currency']), 'branding');
        Setting::set('branding.timezone', $validated['timezone'], 'branding');

        if ($this->logo) {
            $path = $this->logo->store('branding', 'public');
            Setting::set('branding.logo_path', $path, 'branding');
            $this->current_logo_path = $path;
            $this->logo = null;
        }

        AuditLog::record('settings.branding.updated', old: $old, new: $validated);

        $this->saved = true;
    }

    public function render()
    {
        return view('livewire.admin.settings');
    }
}
