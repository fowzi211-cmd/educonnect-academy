@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center px-3 py-1.5 rounded-md text-sm font-medium leading-5 bg-indigo-50 text-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-400 transition duration-150 ease-in-out'
            : 'inline-flex items-center px-3 py-1.5 rounded-md text-sm font-medium leading-5 text-gray-600 hover:text-gray-900 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-400 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
