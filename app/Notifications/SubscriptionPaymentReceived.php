<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Fired from PaymentService::activateSubscription() — the single choke
 * point for every gateway (test, stripe, paypal, bank_transfer) — so a
 * student is notified consistently regardless of how they paid.
 */
class SubscriptionPaymentReceived extends Notification
{
    public function __construct(protected Subscription $subscription) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'subscription.activated',
            'message' => $this->message(),
            'url' => route('my-courses.classroom', $this->subscription->course),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('Payment Confirmed'))
            ->line($this->message())
            ->action(__('Go to Classroom'), route('my-courses.classroom', $this->subscription->course));
    }

    protected function message(): string
    {
        return __('Your payment was confirmed — you now have access to :course.', [
            'course' => $this->subscription->course->title,
        ]);
    }
}
