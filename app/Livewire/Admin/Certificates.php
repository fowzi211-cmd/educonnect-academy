<?php

namespace App\Livewire\Admin;

use App\Models\Certificate;
use App\Services\Assessments\CertificateService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Certificates extends Component
{
    use WithPagination;

    public string $search = '';

    public ?int $revokingId = null;

    public string $revokeReason = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function startRevoke(int $id): void
    {
        $this->revokingId = $id;
        $this->revokeReason = '';
    }

    public function cancelRevoke(): void
    {
        $this->revokingId = null;
    }

    public function confirmRevoke(CertificateService $certificates): void
    {
        $this->validate(['revokeReason' => ['required', 'string', 'max:1000']]);

        $certificate = Certificate::findOrFail($this->revokingId);
        $certificates->revoke($certificate, $this->revokeReason, auth()->user());

        $this->revokingId = null;
    }

    public function reissue(int $id, CertificateService $certificates): void
    {
        $certificate = Certificate::findOrFail($id);
        $certificates->reissue($certificate);
    }

    public function render()
    {
        $certificates = Certificate::query()
            ->with(['user', 'course'])
            ->when($this->search, fn ($q) => $q->where('certificate_number', 'like', '%'.$this->search.'%')
                ->orWhereHas('user', fn ($u) => $u->where('email', 'like', '%'.$this->search.'%')))
            ->latest('issued_at')
            ->paginate(15);

        return view('livewire.admin.certificates', [
            'certificates' => $certificates,
        ]);
    }
}
