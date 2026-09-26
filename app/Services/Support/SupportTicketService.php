<?php

namespace App\Services\Support;

use App\Models\AuditLog;
use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use App\Models\User;
use App\Notifications\SupportTicketReplied;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use RuntimeException;

class SupportTicketService
{
    public function create(User $user, array $data, ?UploadedFile $attachment = null): SupportTicket
    {
        $ticket = SupportTicket::create([
            'ticket_number' => SupportTicket::nextTicketNumber(),
            'user_id' => $user->id,
            'category' => $data['category'],
            'priority' => $data['priority'],
            'subject' => $data['subject'],
            'description' => $data['description'],
            'attachment_path' => $attachment?->store('support-tickets', 'local'),
            'attachment_name' => $attachment?->getClientOriginalName(),
            'status' => SupportTicket::STATUS_OPEN,
        ]);

        AuditLog::record('support_ticket.created', subject: $ticket, new: [
            'category' => $ticket->category,
            'priority' => $ticket->priority,
        ]);

        $this->notifyStaff($ticket, __('New support ticket :number: :subject', [
            'number' => $ticket->ticket_number,
            'subject' => $ticket->subject,
        ]));

        return $ticket;
    }

    public function reply(SupportTicket $ticket, User $author, string $body, bool $isInternalNote = false): SupportTicketReply
    {
        $isStaffAuthor = $author->can('manage support tickets');

        $reply = SupportTicketReply::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => $author->id,
            'body' => $body,
            'is_internal_note' => $isInternalNote,
        ]);

        $ticket->update(['last_response_at' => now()]);

        if (! $isInternalNote) {
            if ($isStaffAuthor) {
                $ticket->update(['status' => SupportTicket::STATUS_WAITING_FOR_USER]);

                $ticket->user->notify(new SupportTicketReplied($ticket, __('Support replied to your ticket :number.', ['number' => $ticket->ticket_number])));
            } else {
                $ticket->update(['status' => $ticket->assigned_to ? SupportTicket::STATUS_ASSIGNED : SupportTicket::STATUS_OPEN]);

                $this->notifyStaff($ticket, __('New reply on ticket :number: :subject', [
                    'number' => $ticket->ticket_number,
                    'subject' => $ticket->subject,
                ]));
            }
        }

        AuditLog::record('support_ticket.replied', subject: $ticket, new: ['is_internal_note' => $isInternalNote]);

        return $reply;
    }

    public function assign(SupportTicket $ticket, User $staff, User $actor): void
    {
        $old = ['assigned_to' => $ticket->assigned_to, 'status' => $ticket->status];

        $ticket->update([
            'assigned_to' => $staff->id,
            'status' => in_array($ticket->status, [SupportTicket::STATUS_OPEN, SupportTicket::STATUS_REOPENED], true)
                ? SupportTicket::STATUS_ASSIGNED
                : $ticket->status,
        ]);

        AuditLog::record('support_ticket.assigned', subject: $ticket, old: $old, new: ['assigned_to' => $staff->id]);
    }

    public function updateStatus(SupportTicket $ticket, string $status, User $actor): void
    {
        if (! in_array($status, SupportTicket::STATUSES, true)) {
            throw new RuntimeException(__('Invalid ticket status.'));
        }

        $old = $ticket->status;

        $ticket->update([
            'status' => $status,
            'resolved_at' => in_array($status, [SupportTicket::STATUS_RESOLVED, SupportTicket::STATUS_CLOSED], true)
                ? ($ticket->resolved_at ?? now())
                : $ticket->resolved_at,
        ]);

        AuditLog::record('support_ticket.status_changed', subject: $ticket, old: ['status' => $old], new: ['status' => $status]);

        if (in_array($status, [SupportTicket::STATUS_RESOLVED, SupportTicket::STATUS_CLOSED], true)) {
            $ticket->user->notify(new SupportTicketReplied($ticket, __('Your ticket :number has been :status.', [
                'number' => $ticket->ticket_number,
                'status' => $status === SupportTicket::STATUS_RESOLVED ? __('resolved') : __('closed'),
            ])));
        }
    }

    public function reopen(SupportTicket $ticket, User $actor): void
    {
        if (! in_array($ticket->status, [SupportTicket::STATUS_RESOLVED, SupportTicket::STATUS_CLOSED], true)) {
            throw new RuntimeException(__('Only a resolved or closed ticket can be reopened.'));
        }

        $ticket->update(['status' => SupportTicket::STATUS_REOPENED]);

        AuditLog::record('support_ticket.reopened', subject: $ticket);

        $this->notifyStaff($ticket, __('Ticket :number was reopened: :subject', [
            'number' => $ticket->ticket_number,
            'subject' => $ticket->subject,
        ]));
    }

    protected function notifyStaff(SupportTicket $ticket, string $message): void
    {
        $recipients = $ticket->assigned_to
            ? User::where('id', $ticket->assigned_to)->get()
            : User::permission('manage support tickets')->get();

        NotificationFacade::send($recipients, new SupportTicketReplied($ticket, $message));
    }
}
