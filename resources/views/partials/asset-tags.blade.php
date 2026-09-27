{{-- Tab icon, generated from the logo emblem (public/images/logo.png). --}}
<link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/favicon-32.png') }}">
<link rel="apple-touch-icon" href="{{ asset('images/apple-touch-icon.png') }}">
@if (file_exists(public_path('build/manifest.json')))
    @vite(['resources/css/app.css', 'resources/js/app.js'])
@else
    {{-- Temporary fallback while Node.js/npm isn't installed on this machine and the Vite build hasn't run.
         Remove once `npm install && npm run build` has produced public/build/manifest.json.
         No external CDN here on purpose — this dev machine's browser preview has no outbound internet,
         and the real app must never depend on a CDN anyway. This is bare-bones readability only. --}}
    <style>
        body { max-width: 1024px; margin: 0 auto; padding: 0 1rem; }
        nav a, footer a { margin-inline-end: 1rem; }
        input, select, textarea, button { font: inherit; padding: .4rem .6rem; }
        button, .button, a[class*="rounded"] { cursor: pointer; }
        img { max-width: 100%; }
    </style>
@endif
