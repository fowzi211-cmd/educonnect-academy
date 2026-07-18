<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My Subscriptions') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @forelse ($subscriptions as $subscription)
                <div class="bg-white shadow-sm sm:rounded-lg p-6" wire:key="sub-{{ $subscription->id }}">
                    <div class="flex items-start justify-between flex-wrap gap-3">
                        <div>
                            <h3 class="font-semibold text-gray-900">{{ $subscription->course->title }}</h3>
                            <p class="text-sm text-gray-500">
                                {{ number_format($subscription->monthly_price, 2) }} {{ $subscription->currency }} / {{ __('month') }}
                            </p>
                        </div>
                        <x-subscription-status-badge :status="$subscription->status" />
                    </div>

                    <dl class="mt-4 grid grid-cols-2 gap-4 text-sm">
                        @if ($subscription->current_period_ends_at)
                            <div>
                                <dt class="text-gray-500">{{ $subscription->cancel_at_period_end ? __('Access ends') : __('Next payment') }}</dt>
                                <dd class="text-gray-900">{{ $subscription->current_period_ends_at->format('Y-m-d') }}</dd>
                            </div>
                        @endif
                        @if ($subscription->cancel_at_period_end)
                            <div>
                                <dt class="text-gray-500">{{ __('Renewal') }}</dt>
                                <dd class="text-gray-900">{{ __('Cancelled — will not renew') }}</dd>
                            </div>
                        @endif
                    </dl>

                    @if ($invoice = $subscription->transactions->first(fn ($t) => $t->invoice)?->invoice)
                        <a href="{{ route('invoices.show', $invoice) }}" wire:navigate class="mt-3 inline-block text-sm text-indigo-600 hover:text-indigo-800 underline">
                            {{ __('View Latest Invoice') }}
                        </a>
                    @endif

                    @if (in_array($subscription->status, ['trial', 'active', 'past_due', 'grace_period']) && ! $subscription->cancel_at_period_end)
                        <div class="mt-4 border-t border-gray-100 pt-4">
                            @if ($cancellingId === $subscription->id)
                                <x-input-label for="cancelReason" :value="__('Why are you cancelling?')" />
                                <textarea wire:model="cancelReason" id="cancelReason" rows="2"
                                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full text-sm"></textarea>
                                <x-input-error :messages="$errors->get('cancelReason')" class="mt-2" />
                                <div class="mt-2 flex gap-2">
                                    <x-danger-button wire:click="confirmCancel">{{ __('Confirm Cancellation') }}</x-danger-button>
                                    <x-secondary-button wire:click="$set('cancellingId', null)">{{ __('Never mind') }}</x-secondary-button>
                                </div>
                            @else
                                <button wire:click="startCancel({{ $subscription->id }})" class="text-sm text-red-600 hover:text-red-800 underline">
                                    {{ __('Cancel Subscription') }}
                                </button>
                            @endif
                        </div>
                    @endif
                </div>
            @empty
                <p class="text-center text-gray-500 py-12">
                    {{ __("You don't have any subscriptions yet.") }}
                    <a href="{{ route('courses.index') }}" wire:navigate class="underline">{{ __('Browse the catalogue') }}</a>.
                </p>
            @endforelse
        </div>
    </div>
</div>
