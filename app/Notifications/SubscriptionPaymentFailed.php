<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Fired from PaymentService::confirmPayment()'s failure branch — covers a
 * declined renewal (grace period started, access continues) and a first
 * payment that never activated (access denied outright) with different
 * wording, since the student's actual situation differs.
 */
class SubscriptionPaymentFailed extends Notification
{
    public function __construct(protected Subscription $subscription, protected bool $gracePeriod, protected ?string $reason) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'subscription.payment_failed',
            'message' => $this->message(),
            'url' => route('my-subscriptions.index'),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('Payment Issue'))
            ->line($this->message())
            ->action(__('View Subscriptions'), route('my-subscriptions.index'));
    }

    protected function message(): string
    {
        $course = $this->subscription->course->title;

        $message = $this->gracePeriod
            ? __('Your renewal payment for :course failed. Access continues until :date — please retry payment.', [
                'course' => $course,
                'date' => $this->subscription->grace_period_ends_at?->format('Y-m-d'),
            ])
            : __('Your payment for :course could not be completed.', ['course' => $course]);

        if ($this->reason) {
            $message .= ' '.__('Reason: :reason', ['reason' => $this->reason]);
        }

        return $message;
    }
}
