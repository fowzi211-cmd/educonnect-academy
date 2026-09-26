<div class="flex gap-4 border-b border-gray-200 mb-6 text-sm font-medium">
    <a href="{{ route('admin.pages') }}" wire:navigate
        @class(['pb-3 border-b-2' => true, 'border-indigo-500 text-indigo-600' => request()->routeIs('admin.pages'), 'border-transparent text-gray-600 hover:text-gray-700' => ! request()->routeIs('admin.pages')])>
        {{ __('Static Pages') }}
    </a>
    <a href="{{ route('admin.faqs') }}" wire:navigate
        @class(['pb-3 border-b-2' => true, 'border-indigo-500 text-indigo-600' => request()->routeIs('admin.faqs'), 'border-transparent text-gray-600 hover:text-gray-700' => ! request()->routeIs('admin.faqs')])>
        {{ __('FAQ') }}
    </a>
    <a href="{{ route('admin.news') }}" wire:navigate
        @class(['pb-3 border-b-2' => true, 'border-indigo-500 text-indigo-600' => request()->routeIs('admin.news'), 'border-transparent text-gray-600 hover:text-gray-700' => ! request()->routeIs('admin.news')])>
        {{ __('News') }}
    </a>
</div>
