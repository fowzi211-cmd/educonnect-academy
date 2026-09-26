@php
    $brandPrimary = \App\Models\Setting::get('branding.primary_color', '#4f46e5');
    $brandSecondary = \App\Models\Setting::get('branding.secondary_color', '#0ea5e9');
@endphp
<style>
    :root {
        --brand-primary: {{ $brandPrimary }};
        --brand-secondary: {{ $brandSecondary }};
    }
</style>
