<?php

namespace App\Livewire\Admin\SupportTickets;

use App\Models\SupportTicket;
use App\Models\User;
use App\Services\Support\SupportTicketService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Show extends Component
{
    public SupportTicket $ticket;

    public string $replyBody = '';

    public bool $isInternalNote = false;

    public function mount(SupportTicket $supportTicket): void
    {
        $this->ticket = $supportTicket;
    }

    public function reply(SupportTicketService $tickets): void
    {
        $this->validate(['replyBody' => ['required', 'string', 'max:5000']]);

        $tickets->reply($this->ticket, Auth::user(), $this->replyBody, $this->isInternalNote);

        $this->replyBody = '';
        $this->isInternalNote = false;
        $this->ticket->refresh();
    }

    public function assignToMe(SupportTicketService $tickets): void
    {
        $tickets->assign($this->ticket, Auth::user(), Auth::user());
        $this->ticket->refresh();
    }

    public function assignTo(int $userId, SupportTicketService $tickets): void
    {
        $staff = User::findOrFail($userId);
        $tickets->assign($this->ticket, $staff, Auth::user());
        $this->ticket->refresh();
    }

    public function updateStatus(string $status, SupportTicketService $tickets): void
    {
        $tickets->updateStatus($this->ticket, $status, Auth::user());
        $this->ticket->refresh();
    }

    public function render()
    {
        return view('livewire.admin.support-tickets.show', [
            'replies' => $this->ticket->replies()->with('user')->orderBy('created_at')->get(),
            'staff' => User::permission('manage support tickets')->orderBy('name')->get(),
        ]);
    }
}
