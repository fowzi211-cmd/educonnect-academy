<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Supported Interface Languages
    |--------------------------------------------------------------------------
    |
    | Locale codes the interface can be switched to, each with its writing
    | direction. Add a locale here (and its lang/{code} files) to support it.
    |
    */

    'locales' => [
        'en' => ['name' => 'English', 'native' => 'English', 'direction' => 'ltr'],
        'ar' => ['name' => 'Arabic', 'native' => 'العربية', 'direction' => 'rtl'],
    ],

    'default_currency' => env('PLATFORM_DEFAULT_CURRENCY', 'SAR'),

    /*
    |--------------------------------------------------------------------------
    | Policy Version
    |--------------------------------------------------------------------------
    |
    | Bumped whenever terms/privacy content changes materially, so consent
    | records show exactly which version a user agreed to (spec section 26).
    |
    */

    'policy_version' => '1.0',

];
