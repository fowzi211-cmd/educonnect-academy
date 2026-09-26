<div class="flex gap-4 border-b border-gray-200 mb-6 text-sm font-medium">
    <a href="{{ route('admin.finance.transactions') }}" wire:navigate
        @class(['pb-3 border-b-2' => true, 'border-indigo-500 text-indigo-600' => request()->routeIs('admin.finance.transactions'), 'border-transparent text-gray-600 hover:text-gray-700' => ! request()->routeIs('admin.finance.transactions')])>
        {{ __('Transactions') }}
    </a>
    <a href="{{ route('admin.finance.invoices') }}" wire:navigate
        @class(['pb-3 border-b-2' => true, 'border-indigo-500 text-indigo-600' => request()->routeIs('admin.finance.invoices'), 'border-transparent text-gray-600 hover:text-gray-700' => ! request()->routeIs('admin.finance.invoices')])>
        {{ __('Invoices') }}
    </a>
    <a href="{{ route('admin.finance.revenue') }}" wire:navigate
        @class(['pb-3 border-b-2' => true, 'border-indigo-500 text-indigo-600' => request()->routeIs('admin.finance.revenue'), 'border-transparent text-gray-600 hover:text-gray-700' => ! request()->routeIs('admin.finance.revenue')])>
        {{ __('Revenue Report') }}
    </a>
    <a href="{{ route('admin.finance.coupons') }}" wire:navigate
        @class(['pb-3 border-b-2' => true, 'border-indigo-500 text-indigo-600' => request()->routeIs('admin.finance.coupons'), 'border-transparent text-gray-600 hover:text-gray-700' => ! request()->routeIs('admin.finance.coupons')])>
        {{ __('Coupons') }}
    </a>
    <a href="{{ route('admin.finance.bank-accounts') }}" wire:navigate
        @class(['pb-3 border-b-2' => true, 'border-indigo-500 text-indigo-600' => request()->routeIs('admin.finance.bank-accounts'), 'border-transparent text-gray-600 hover:text-gray-700' => ! request()->routeIs('admin.finance.bank-accounts')])>
        {{ __('Bank Accounts') }}
    </a>
    @can('manage lecturer commissions')
        <a href="{{ route('admin.finance.commissions') }}" wire:navigate
            @class(['pb-3 border-b-2' => true, 'border-indigo-500 text-indigo-600' => request()->routeIs('admin.finance.commissions'), 'border-transparent text-gray-600 hover:text-gray-700' => ! request()->routeIs('admin.finance.commissions')])>
            {{ __('Commissions') }}
        </a>
        <a href="{{ route('admin.finance.payouts') }}" wire:navigate
            @class(['pb-3 border-b-2' => true, 'border-indigo-500 text-indigo-600' => request()->routeIs('admin.finance.payouts'), 'border-transparent text-gray-600 hover:text-gray-700' => ! request()->routeIs('admin.finance.payouts')])>
            {{ __('Payouts') }}
        </a>
    @endcan
</div>
