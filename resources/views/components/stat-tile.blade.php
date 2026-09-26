@props(['label', 'value', 'accent' => 'indigo'])

@php
$accents = [
    'indigo' => 'border-indigo-500',
    'emerald' => 'border-emerald-500',
    'amber' => 'border-amber-500',
    'rose' => 'border-rose-500',
];
$accentClass = $accents[$accent] ?? $accents['indigo'];
@endphp

<div {{ $attributes->merge(['class' => "bg-white shadow-sm rounded-xl p-4 border-s-4 $accentClass hover:shadow-md transition"]) }}>
    <div class="text-sm text-gray-600">{{ $label }}</div>
    <div class="mt-1 text-2xl font-bold text-gray-900">{{ $value }}</div>
</div>
