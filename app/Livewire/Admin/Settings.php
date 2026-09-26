<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use App\Models\Setting;
use App\Services\Payments\PaymentGatewayManager;
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

    public string $payment_gateway = 'test';

    public bool $paymentGatewaySaved = false;

    public string $commission_default_rate_type = 'percentage';

    public float $commission_default_rate_value = 70;

    public float $commission_payout_threshold = 100;

    public bool $commissionSaved = false;

    public string $policy_version = '1.0';

    public int $live_class_link_release_minutes = 15;

    public int $payment_grace_period_days = 3;

    public bool $platformConfigSaved = false;

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
        $this->payment_gateway = Setting::get('payment.default_gateway', config('services.payment.default_gateway', 'test'));
        $this->commission_default_rate_type = Setting::get('commission.default_rate_type', 'percentage');
        $this->commission_default_rate_value = (float) Setting::get('commission.default_rate_value', 70);
        $this->commission_payout_threshold = (float) Setting::get('commission.payout_threshold', 100);
        $this->policy_version = Setting::get('platform.policy_version', config('platform.policy_version'));
        $this->live_class_link_release_minutes = (int) Setting::get('platform.live_class_link_release_minutes', config('platform.live_class_link_release_minutes'));
        $this->payment_grace_period_days = (int) Setting::get('platform.payment_grace_period_days', config('platform.payment_grace_period_days'));
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

    public function savePaymentGateway(PaymentGatewayManager $gateways): void
    {
        $validated = $this->validate([
            'payment_gateway' => ['required', 'string', 'in:'.implode(',', $gateways->onlineDrivers())],
        ]);

        if ($validated['payment_gateway'] === 'stripe' && ! config('services.stripe.secret')) {
            $this->addError('payment_gateway', __('Stripe is not configured yet — set STRIPE_KEY and STRIPE_SECRET in the environment first.'));

            return;
        }

        if ($validated['payment_gateway'] === 'paypal' && (! config('services.paypal.client_id') || ! config('services.paypal.client_secret'))) {
            $this->addError('payment_gateway', __('PayPal is not configured yet — set PAYPAL_CLIENT_ID and PAYPAL_CLIENT_SECRET in the environment first.'));

            return;
        }

        $old = ['payment_gateway' => Setting::get('payment.default_gateway')];

        Setting::set('payment.default_gateway', $validated['payment_gateway'], 'payment');

        AuditLog::record('settings.payment_gateway.updated', old: $old, new: $validated);

        $this->paymentGatewaySaved = true;
    }

    public function saveCommissionDefaults(): void
    {
        $validated = $this->validate([
            'commission_default_rate_type' => ['required', 'string', 'in:percentage,fixed'],
            'commission_default_rate_value' => ['required', 'numeric', 'min:0'],
            'commission_payout_threshold' => ['required', 'numeric', 'min:0'],
        ]);

        if ($validated['commission_default_rate_type'] === 'percentage' && $validated['commission_default_rate_value'] > 100) {
            $this->addError('commission_default_rate_value', __('A percentage rate cannot exceed 100.'));

            return;
        }

        $old = [
            'commission_default_rate_type' => Setting::get('commission.default_rate_type'),
            'commission_default_rate_value' => Setting::get('commission.default_rate_value'),
            'commission_payout_threshold' => Setting::get('commission.payout_threshold'),
        ];

        Setting::set('commission.default_rate_type', $validated['commission_default_rate_type'], 'commission');
        Setting::set('commission.default_rate_value', $validated['commission_default_rate_value'], 'commission');
        Setting::set('commission.payout_threshold', $validated['commission_payout_threshold'], 'commission');

        AuditLog::record('settings.commission_defaults.updated', old: $old, new: $validated);

        $this->commissionSaved = true;
    }

    public function savePlatformConfig(): void
    {
        $validated = $this->validate([
            'policy_version' => ['required', 'string', 'max:20'],
            'live_class_link_release_minutes' => ['required', 'integer', 'min:1', 'max:1440'],
            'payment_grace_period_days' => ['required', 'integer', 'min:0', 'max:90'],
        ]);

        $old = [
            'policy_version' => Setting::get('platform.policy_version', config('platform.policy_version')),
            'live_class_link_release_minutes' => Setting::get('platform.live_class_link_release_minutes', config('platform.live_class_link_release_minutes')),
            'payment_grace_period_days' => Setting::get('platform.payment_grace_period_days', config('platform.payment_grace_period_days')),
        ];

        Setting::set('platform.policy_version', $validated['policy_version'], 'platform');
        Setting::set('platform.live_class_link_release_minutes', $validated['live_class_link_release_minutes'], 'platform', 'integer');
        Setting::set('platform.payment_grace_period_days', $validated['payment_grace_period_days'], 'platform', 'integer');

        AuditLog::record('settings.platform_config.updated', old: $old, new: $validated);

        $this->platformConfigSaved = true;
    }

    public function render()
    {
        return view('livewire.admin.settings', [
            'availableGateways' => app(PaymentGatewayManager::class)->onlineDrivers(),
        ]);
    }
}
