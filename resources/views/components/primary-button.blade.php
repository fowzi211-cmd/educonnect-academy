<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2.5 rounded-lg font-semibold text-sm text-white btn-brand shadow-sm hover:shadow-md focus:outline-none focus:ring-2 focus:ring-brand-primary focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:shadow-sm transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
