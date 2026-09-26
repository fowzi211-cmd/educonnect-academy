@props(['status'])

@php
    $labels = [
        'pending' => __('Pending'),
        'trial' => __('Trial'),
        'active' => __('Active'),
        'past_due' => __('Past Due'),
        'grace_period' => __('Grace Period'),
        'paused' => __('Paused'),
        'cancelled' => __('Cancelled'),
        'expired' => __('Expired'),
        'refunded' => __('Refunded'),
        'payment_failed' => __('Payment Failed'),
    ];

    $colors = [
        'pending' => 'bg-gray-100 text-gray-700',
        'trial' => 'bg-blue-100 text-blue-800',
        'active' => 'bg-green-100 text-green-800',
        'past_due' => 'bg-amber-100 text-amber-800',
        'grace_period' => 'bg-amber-100 text-amber-800',
        'paused' => 'bg-gray-100 text-gray-700',
        'cancelled' => 'bg-gray-100 text-gray-700',
        'expired' => 'bg-gray-100 text-gray-700',
        'refunded' => 'bg-purple-100 text-purple-800',
        'payment_failed' => 'bg-red-100 text-red-800',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium '.($colors[$status] ?? 'bg-gray-100 text-gray-700')]) }}>
    {{ $labels[$status] ?? $status }}
</span>
