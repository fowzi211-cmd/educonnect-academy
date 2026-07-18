@props(['percent'])

<div>
    <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
        <span>{{ __('Progress') }}</span>
        <span>{{ $percent }}%</span>
    </div>
    <div class="w-full bg-gray-100 rounded-full h-1.5">
        <div class="bg-gray-900 h-1.5 rounded-full" style="width: {{ max(0, min(100, $percent)) }}%"></div>
    </div>
</div>
