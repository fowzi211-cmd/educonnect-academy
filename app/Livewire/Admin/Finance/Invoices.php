<?php

namespace App\Livewire\Admin\Finance;

use App\Models\Invoice;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Invoices extends Component
{
    use WithPagination;

    public string $status = '';

    public string $studentEmail = '';

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingStudentEmail(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $invoices = Invoice::query()
            ->with(['user', 'transaction.course'])
            ->when($this->status, fn ($query) => $query->where('status', $this->status))
            ->when($this->studentEmail, fn ($query) => $query->whereHas('user', fn ($q) => $q->where('email', 'like', '%'.$this->studentEmail.'%')))
            ->latest('issued_at')
            ->paginate(15);

        return view('livewire.admin.finance.invoices', [
            'invoices' => $invoices,
        ]);
    }
}
