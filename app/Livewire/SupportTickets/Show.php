<?php

namespace App\Livewire\SupportTickets;

use App\Models\SupportTicket;
use App\Services\Support\SupportTicketService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Show extends Component
{
    public SupportTicket $ticket;

    public string $replyBody = '';

    public function mount(SupportTicket $supportTicket): void
    {
        abort_unless($supportTicket->user_id === Auth::id(), 403);

        $this->ticket = $supportTicket;
    }

    public function reply(SupportTicketService $tickets): void
    {
        abort_if(in_array($this->ticket->status, [SupportTicket::STATUS_RESOLVED, SupportTicket::STATUS_CLOSED], true), 403);

        $this->validate(['replyBody' => ['required', 'string', 'max:5000']]);

        $tickets->reply($this->ticket, Auth::user(), $this->replyBody);

        $this->replyBody = '';
        $this->ticket->refresh();
    }

    public function reopen(SupportTicketService $tickets): void
    {
        $tickets->reopen($this->ticket, Auth::user());
        $this->ticket->refresh();
    }

    public function render()
    {
        return view('livewire.support-tickets.show', [
            'replies' => $this->ticket->replies()->with('user')->where('is_internal_note', false)->orderBy('created_at')->get(),
        ]);
    }
}
