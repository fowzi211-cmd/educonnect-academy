<?php

namespace App\Notifications;

use App\Models\Transaction;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Confirms to the student that their receipt was received and is queued
 * for review — the first of the "every step" notifications for a
 * bank-transfer subscription (submitted -> approved/rejected).
 */
class BankTransferReceiptReceived extends Notification
{
    public function __construct(protected Transaction $transaction) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'transaction.receipt_received',
            'message' => $this->message(),
            'url' => route('checkout.complete', $this->transaction->subscription),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('Receipt Received'))
            ->line($this->message())
            ->action(__('View Status'), route('checkout.complete', $this->transaction->subscription));
    }

    protected function message(): string
    {
        return __('We received your bank transfer receipt for :course (reference :reference). We will review it within 1 business day.', [
            'course' => $this->transaction->course->title,
            'reference' => $this->transaction->receipt_reference_number,
        ]);
    }
}
