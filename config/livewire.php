<?php

// Only the settings this app overrides; the rest come from Livewire's defaults.
return [
    'temporary_file_upload' => [
        'disk' => null,
        // Livewire's default cap is 12 MB, which blocks real lesson videos. The
        // per-feature rules (e.g. video max:512000) still apply on top of this.
        'rules' => ['required', 'file', 'max:614400'],
        'directory' => null,
        'middleware' => null,
        'preview_mimes' => [
            'png', 'gif', 'bmp', 'svg', 'wav', 'mp4',
            'mov', 'avi', 'wmv', 'mp3', 'm4a',
            'jpg', 'jpeg', 'mpga', 'webp', 'wma',
        ],
        'max_upload_time' => 30,
        'cleanup' => true,
    ],
];
