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

    /*
    |--------------------------------------------------------------------------
    | Live Class Link Release
    |--------------------------------------------------------------------------
    |
    | How many minutes before a live class starts the student join link
    | becomes visible (spec section 9: "Release the student meeting link
    | only shortly before the class"). The lecturer host link is never
    | exposed to students at all.
    |
    */

    'live_class_link_release_minutes' => env('LIVE_CLASS_LINK_RELEASE_MINUTES', 15),

];
