@props(['url'])

<div x-data="pdfViewer(@js($url))" class="space-y-2">
    <p x-show="loading" class="text-gray-600">{{ __('Loading document…') }}</p>
    <p x-show="failed" x-cloak class="text-red-700">{{ __('This document could not be displayed.') }}</p>
    <div x-ref="pages"></div>
</div>
