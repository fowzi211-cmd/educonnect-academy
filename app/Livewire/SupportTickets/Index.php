<?php

namespace App\Livewire\SupportTickets;

use App\Models\SupportTicket;
use App\Services\Support\SupportTicketService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithFileUploads;

    public bool $showForm = false;

    public string $category = '';

    public string $priority = 'medium';

    public string $subject = '';

    public string $description = '';

    public $attachment;

    public function newForm(): void
    {
        $this->reset(['category', 'subject', 'description', 'attachment']);
        $this->priority = 'medium';
        $this->showForm = true;
    }

    public function cancelForm(): void
    {
        $this->showForm = false;
    }

    public function create(SupportTicketService $tickets): void
    {
        $validated = $this->validate([
            'category' => ['required', 'string', 'in:'.implode(',', SupportTicket::CATEGORIES)],
            'priority' => ['required', 'string', 'in:'.implode(',', SupportTicket::PRIORITIES)],
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'attachment' => ['nullable', 'file', 'max:5120'],
        ]);

        $ticket = $tickets->create(Auth::user(), $validated, $this->attachment);

        $this->showForm = false;

        $this->redirect(route('support-tickets.show', $ticket), navigate: true);
    }

    public function render()
    {
        return view('livewire.support-tickets.index', [
            'tickets' => Auth::user()->supportTickets()->latest()->get(),
        ]);
    }
}
