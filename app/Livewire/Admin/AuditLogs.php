<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class AuditLogs extends Component
{
    use WithPagination;

    #[Url(as: 'user')]
    public string $userFilter = '';

    #[Url(as: 'action')]
    public string $actionFilter = '';

    #[Url(as: 'type')]
    public string $auditableTypeFilter = '';

    #[Url(as: 'from')]
    public string $dateFrom = '';

    #[Url(as: 'to')]
    public string $dateTo = '';

    #[Url]
    public string $search = '';

    public ?int $expandedId = null;

    public function updatingUserFilter(): void
    {
        $this->resetPage();
    }

    public function updatingActionFilter(): void
    {
        $this->resetPage();
    }

    public function updatingAuditableTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updatingDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatingDateTo(): void
    {
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function toggleExpand(int $id): void
    {
        $this->expandedId = $this->expandedId === $id ? null : $id;
    }

    public function resetFilters(): void
    {
        $this->reset(['userFilter', 'actionFilter', 'auditableTypeFilter', 'dateFrom', 'dateTo', 'search']);
        $this->resetPage();
    }

    public function render()
    {
        $logs = AuditLog::query()
            ->with('user')
            ->when($this->userFilter, fn ($q) => $q->where('user_id', $this->userFilter))
            ->when($this->actionFilter, fn ($q) => $q->where('action', $this->actionFilter))
            ->when($this->auditableTypeFilter, fn ($q) => $q->where('auditable_type', $this->auditableTypeFilter))
            ->when($this->dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->when($this->search, fn ($q) => $q->where(function ($sub) {
                $sub->where('reason', 'like', '%'.$this->search.'%')
                    ->orWhere('action', 'like', '%'.$this->search.'%')
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('email', 'like', '%'.$this->search.'%'));
            }))
            ->latest('created_at')
            ->paginate(20);

        return view('livewire.admin.audit-logs', [
            'logs' => $logs,
            'actions' => AuditLog::query()->select('action')->distinct()->orderBy('action')->pluck('action'),
            'auditableTypes' => AuditLog::query()->whereNotNull('auditable_type')->select('auditable_type')->distinct()->orderBy('auditable_type')->pluck('auditable_type'),
            'users' => User::query()->whereIn('id', AuditLog::query()->select('user_id')->distinct()->whereNotNull('user_id'))->orderBy('name')->get(['id', 'name', 'email']),
        ]);
    }
}
