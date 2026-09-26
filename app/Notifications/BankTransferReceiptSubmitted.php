<?php

namespace App\Notifications;

use App\Models\Transaction;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent to everyone holding "manage finances" so a bank-transfer receipt
 * doesn't sit unreviewed — the student has no other automated payment
 * confirmation to fall back on.
 */
class BankTransferReceiptSubmitted extends Notification
{
    public function __construct(protected Transaction $transaction) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'transaction.receipt_submitted',
            'message' => $this->message(),
            'url' => route('admin.finance.transactions'),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('Bank Transfer Receipt Awaiting Review'))
            ->line($this->message())
            ->action(__('Review Receipt'), route('admin.finance.transactions'));
    }

    protected function message(): string
    {
        return __(':name submitted a bank transfer receipt for :course (:reference) — awaiting review.', [
            'name' => $this->transaction->user->name,
            'course' => $this->transaction->course->title,
            'reference' => $this->transaction->receipt_reference_number,
        ]);
    }
}
