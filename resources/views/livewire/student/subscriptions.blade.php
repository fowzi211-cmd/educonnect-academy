<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My Subscriptions') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <div class="flex justify-end">
                <a href="{{ route('courses.index') }}" wire:navigate class="inline-flex items-center px-4 py-2.5 rounded-lg font-semibold text-lg text-white btn-brand shadow-sm hover:shadow-md">
                    {{ __('New Subscription') }}
                </a>
            </div>

            @forelse ($subscriptions as $subscription)
                <div class="bg-white shadow-sm sm:rounded-lg p-6" wire:key="sub-{{ $subscription->id }}">
                    <div class="flex items-start justify-between flex-wrap gap-3">
                        <div>
                            <h3 class="font-semibold text-gray-900">{{ $subscription->course->title }}</h3>
                            <p class="text-lg text-gray-800">
                                {{ number_format($subscription->monthly_price, 2) }} {{ $subscription->currency }} / {{ __('month') }}
                            </p>
                        </div>
                        <x-subscription-status-badge :status="$subscription->status" />
                    </div>

                    <dl class="mt-4 grid grid-cols-2 gap-4 text-lg">
                        @if ($subscription->current_period_ends_at)
                            <div>
                                <dt class="text-gray-800">{{ $subscription->cancel_at_period_end ? __('Access ends') : __('Next payment') }}</dt>
                                <dd class="text-gray-900">{{ $subscription->current_period_ends_at->format('Y-m-d') }}</dd>
                            </div>
                        @endif
                        @if ($subscription->cancel_at_period_end)
                            <div>
                                <dt class="text-gray-800">{{ __('Renewal') }}</dt>
                                <dd class="text-gray-900">{{ __('Cancelled — will not renew') }}</dd>
                            </div>
                        @endif
                    </dl>

                    <div class="mt-3 flex items-center gap-4 flex-wrap">
                        @if ($invoice = $subscription->transactions->first(fn ($t) => $t->invoice)?->invoice)
                            <a href="{{ route('invoices.show', $invoice) }}" wire:navigate class="text-lg text-indigo-600 hover:text-indigo-800 underline">
                                {{ __('View Latest Invoice') }}
                            </a>
                        @endif
                        <button wire:click="toggleEdit({{ $subscription->id }})" class="text-lg text-indigo-600 hover:text-indigo-800 underline">
                            {{ $editingId === $subscription->id ? __('Close') : __('Edit Subscription') }}
                        </button>
                    </div>

                    @if ($editingId === $subscription->id)
                        <div class="mt-4 border-t border-gray-100 pt-4 bg-indigo-50/40 -mx-6 -mb-6 px-6 pb-6 rounded-b-lg">
                            <dl class="grid grid-cols-2 gap-4 text-lg">
                                <div>
                                    <dt class="text-gray-800">{{ __('Course') }}</dt>
                                    <dd class="text-gray-900">{{ $subscription->course->title }}</dd>
                                </div>
                                <div>
                                    <dt class="text-gray-800">{{ __('Price') }}</dt>
                                    <dd class="text-gray-900">{{ number_format($subscription->monthly_price, 2) }} {{ $subscription->currency }} / {{ __('month') }}</dd>
                                </div>
                                <div>
                                    <dt class="text-gray-800">{{ __('Status') }}</dt>
                                    <dd class="text-gray-900"><x-subscription-status-badge :status="$subscription->status" /></dd>
                                </div>
                                @if ($subscription->current_period_ends_at)
                                    <div>
                                        <dt class="text-gray-800">{{ __('Next payment') }}</dt>
                                        <dd class="text-gray-900">{{ $subscription->current_period_ends_at->format('Y-m-d') }}</dd>
                                    </div>
                                @endif
                                @if ($subscription->status === 'grace_period' && $subscription->grace_period_ends_at)
                                    <div>
                                        <dt class="text-gray-800">{{ __('Grace period ends') }}</dt>
                                        <dd class="text-gray-900">{{ $subscription->grace_period_ends_at->format('Y-m-d') }}</dd>
                                    </div>
                                @endif
                            </dl>

                            <x-input-error :messages="$errors->get('resume')" class="mt-3" />

                            <div class="mt-4">
                                @if ($subscription->cancel_at_period_end)
                                    <p class="text-lg text-gray-800 mb-2">
                                        {{ __('This subscription is set to cancel on :date. Resume it to keep auto-renewing.', ['date' => $subscription->current_period_ends_at?->format('Y-m-d')]) }}
                                    </p>
                                    <x-primary-button wire:click="resumeSubscription({{ $subscription->id }})">
                                        {{ __('Resume Subscription') }}
                                    </x-primary-button>
                                @elseif ($subscription->status === 'payment_failed')
                                    <p class="text-lg text-gray-800 mb-2">{{ __('Your last payment failed. Retry to restore access.') }}</p>
                                    <button wire:click="retryPayment({{ $subscription->id }})" class="inline-flex items-center px-4 py-2.5 rounded-lg font-semibold text-lg text-white btn-brand shadow-sm hover:shadow-md">
                                        {{ __('Retry Payment') }}
                                    </button>
                                @elseif ($subscription->status === 'grace_period')
                                    <p class="text-lg text-gray-800 mb-2">
                                        {{ __('Your last payment failed. Access continues until :date — retry now instead of waiting.', ['date' => $subscription->grace_period_ends_at?->format('Y-m-d')]) }}
                                    </p>
                                    <button wire:click="retryPayment({{ $subscription->id }})" class="inline-flex items-center px-4 py-2.5 rounded-lg font-semibold text-lg text-white btn-brand shadow-sm hover:shadow-md">
                                        {{ __('Retry Payment') }}
                                    </button>
                                @else
                                    <p class="text-lg text-gray-800">{{ __('There is nothing to change on this subscription right now.') }}</p>
                                @endif
                            </div>
                        </div>
                    @endif

                    @if (in_array($subscription->status, ['trial', 'active', 'past_due', 'grace_period']) && ! $subscription->cancel_at_period_end)
                        <div class="mt-4 border-t border-gray-100 pt-4">
                            @if ($cancellingId === $subscription->id)
                                <x-input-label for="cancelReason" :value="__('Why are you cancelling?')" />
                                <textarea wire:model="cancelReason" id="cancelReason" rows="2"
                                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full text-lg"></textarea>
                                <x-input-error :messages="$errors->get('cancelReason')" class="mt-2" />
                                <div class="mt-2 flex gap-2">
                                    <x-danger-button wire:click="confirmCancel">{{ __('Confirm Cancellation') }}</x-danger-button>
                                    <x-secondary-button wire:click="$set('cancellingId', null)">{{ __('Never mind') }}</x-secondary-button>
                                </div>
                            @else
                                <button wire:click="startCancel({{ $subscription->id }})" class="text-lg text-red-600 hover:text-red-800 underline">
                                    {{ __('Cancel Subscription') }}
                                </button>
                            @endif
                        </div>
                    @endif
                </div>
            @empty
                <p class="text-center text-gray-800 py-12">
                    {{ __("You don't have any subscriptions yet.") }}
                    <a href="{{ route('courses.index') }}" wire:navigate class="underline">{{ __('Browse the catalogue') }}</a>.
                </p>
            @endforelse
        </div>
    </div>
</div>
