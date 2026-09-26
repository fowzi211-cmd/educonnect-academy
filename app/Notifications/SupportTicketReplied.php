<?php

namespace App\Notifications;

use App\Models\SupportTicket;
use Illuminate\Notifications\Notification;

class SupportTicketReplied extends Notification
{
    public function __construct(protected SupportTicket $ticket, protected string $message) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'support_ticket.updated',
            'message' => $this->message,
            'url' => $notifiable->can('manage support tickets')
                ? route('admin.support-tickets.show', $this->ticket)
                : route('support-tickets.show', $this->ticket),
        ];
    }
}
