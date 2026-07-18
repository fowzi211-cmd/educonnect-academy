<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Seed the branding/platform defaults an administrator can later edit
     * from the settings panel, per development plan section 24 ("settings" table).
     */
    public function run(): void
    {
        $defaults = [
            'branding.platform_name' => ['value' => 'EduConnect Academy', 'type' => 'string'],
            'branding.logo_path' => ['value' => null, 'type' => 'string'],
            'branding.primary_color' => ['value' => '#4f46e5', 'type' => 'string'],
            'branding.secondary_color' => ['value' => '#0ea5e9', 'type' => 'string'],
            'branding.contact_email' => ['value' => 'hello@educonnect.academy', 'type' => 'string'],
            'branding.contact_phone' => ['value' => '', 'type' => 'string'],
            'branding.default_locale' => ['value' => 'en', 'type' => 'string'],
            'branding.default_currency' => ['value' => config('platform.default_currency'), 'type' => 'string'],
            'branding.timezone' => ['value' => config('app.timezone'), 'type' => 'string'],
        ];

        foreach ($defaults as $key => $setting) {
            Setting::query()->firstOrCreate(
                ['key' => $key],
                ['group' => 'branding', 'value' => $setting['value'], 'type' => $setting['type']]
            );
        }

        Setting::query()->firstOrCreate(
            ['key' => 'payment.default_gateway'],
            ['group' => 'payment', 'value' => config('services.payment.default_gateway', 'test'), 'type' => 'string']
        );
    }
}
