<?php

namespace App\Livewire\Admin\SupportTickets;

use App\Models\SupportTicket;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Queue extends Component
{
    use WithPagination;

    #[Url]
    public string $status = '';

    #[Url]
    public string $category = '';

    #[Url]
    public string $priority = '';

    #[Url]
    public string $assignee = '';

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingCategory(): void
    {
        $this->resetPage();
    }

    public function updatingPriority(): void
    {
        $this->resetPage();
    }

    public function updatingAssignee(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $tickets = SupportTicket::with(['user', 'assignedTo'])
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when($this->category, fn ($q) => $q->where('category', $this->category))
            ->when($this->priority, fn ($q) => $q->where('priority', $this->priority))
            ->when($this->assignee === 'me', fn ($q) => $q->where('assigned_to', Auth::id()))
            ->when($this->assignee === 'unassigned', fn ($q) => $q->whereNull('assigned_to'))
            ->latest('created_at')
            ->paginate(15);

        return view('livewire.admin.support-tickets.queue', [
            'tickets' => $tickets,
            'openCount' => SupportTicket::whereIn('status', SupportTicket::OPEN_STATUSES)->count(),
        ]);
    }
}
