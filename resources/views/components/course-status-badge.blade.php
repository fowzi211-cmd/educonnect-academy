@props(['status'])

@php
    $labels = [
        'draft' => __('Draft'),
        'under_review' => __('Under Review'),
        'revision_requested' => __('Revision Requested'),
        'approved' => __('Approved'),
        'published' => __('Published'),
        'unpublished' => __('Unpublished'),
        'suspended' => __('Suspended'),
        'archived' => __('Archived'),
    ];

    $colors = [
        'draft' => 'bg-gray-100 text-gray-700',
        'under_review' => 'bg-blue-100 text-blue-800',
        'revision_requested' => 'bg-amber-100 text-amber-800',
        'approved' => 'bg-teal-100 text-teal-800',
        'published' => 'bg-green-100 text-green-800',
        'unpublished' => 'bg-gray-100 text-gray-700',
        'suspended' => 'bg-red-100 text-red-800',
        'archived' => 'bg-gray-100 text-gray-700',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium '.($colors[$status] ?? 'bg-gray-100 text-gray-700')]) }}>
    {{ $labels[$status] ?? $status }}
</span>
