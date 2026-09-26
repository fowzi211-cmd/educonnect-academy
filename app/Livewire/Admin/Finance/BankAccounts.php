<?php

namespace App\Livewire\Admin\Finance;

use App\Models\AuditLog;
use App\Models\BankAccount;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class BankAccounts extends Component
{
    public string $bankName = '';

    public string $accountName = '';

    public string $accountNumber = '';

    public string $iban = '';

    public string $swiftCode = '';

    public string $currency = 'SDG';

    public string $notes = '';

    public ?int $editingId = null;

    public string $editingBankName = '';

    public string $editingAccountName = '';

    public string $editingAccountNumber = '';

    public string $editingIban = '';

    public string $editingSwiftCode = '';

    public string $editingCurrency = 'SDG';

    public string $editingNotes = '';

    protected function rules(bool $editing): array
    {
        $field = fn (string $base) => $editing ? 'editing'.ucfirst($base) : $base;

        return [
            $field('bankName') => ['required', 'string', 'max:255'],
            $field('accountName') => ['required', 'string', 'max:255'],
            $field('accountNumber') => ['required', 'string', 'max:100'],
            $field('iban') => ['nullable', 'string', 'max:100'],
            $field('swiftCode') => ['nullable', 'string', 'max:20'],
            $field('currency') => ['required', 'string', 'size:3'],
            $field('notes') => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function create(): void
    {
        $validated = $this->validate($this->rules(false));

        $account = BankAccount::create([
            'bank_name' => $validated['bankName'],
            'account_name' => $validated['accountName'],
            'account_number' => $validated['accountNumber'],
            'iban' => $validated['iban'] ?: null,
            'swift_code' => $validated['swiftCode'] ?: null,
            'currency' => strtoupper($validated['currency']),
            'notes' => $validated['notes'] ?: null,
            'is_active' => true,
        ]);

        AuditLog::record('bank_account.created', subject: $account, new: $account->only(['bank_name', 'account_name', 'account_number']));

        $this->reset(['bankName', 'accountName', 'accountNumber', 'iban', 'swiftCode', 'notes']);
        $this->currency = 'SDG';
    }

    public function edit(int $id): void
    {
        $account = BankAccount::findOrFail($id);

        $this->editingId = $account->id;
        $this->editingBankName = $account->bank_name;
        $this->editingAccountName = $account->account_name;
        $this->editingAccountNumber = $account->account_number;
        $this->editingIban = $account->iban ?? '';
        $this->editingSwiftCode = $account->swift_code ?? '';
        $this->editingCurrency = $account->currency;
        $this->editingNotes = $account->notes ?? '';
    }

    public function cancelEdit(): void
    {
        $this->reset([
            'editingId', 'editingBankName', 'editingAccountName', 'editingAccountNumber',
            'editingIban', 'editingSwiftCode', 'editingNotes',
        ]);
        $this->editingCurrency = 'SDG';
    }

    public function update(): void
    {
        $validated = $this->validate($this->rules(true));

        $account = BankAccount::findOrFail($this->editingId);
        $old = $account->only(['bank_name', 'account_name', 'account_number', 'iban', 'swift_code', 'currency', 'notes']);

        $account->update([
            'bank_name' => $validated['editingBankName'],
            'account_name' => $validated['editingAccountName'],
            'account_number' => $validated['editingAccountNumber'],
            'iban' => $validated['editingIban'] ?: null,
            'swift_code' => $validated['editingSwiftCode'] ?: null,
            'currency' => strtoupper($validated['editingCurrency']),
            'notes' => $validated['editingNotes'] ?: null,
        ]);

        AuditLog::record('bank_account.updated', subject: $account, old: $old, new: $account->only(['bank_name', 'account_name', 'account_number', 'iban', 'swift_code', 'currency', 'notes']));

        $this->cancelEdit();
    }

    public function toggleActive(int $id): void
    {
        $account = BankAccount::findOrFail($id);
        $old = ['is_active' => $account->is_active];
        $account->update(['is_active' => ! $account->is_active]);

        AuditLog::record('bank_account.toggled', subject: $account, old: $old, new: ['is_active' => $account->is_active]);
    }

    public function render()
    {
        return view('livewire.admin.finance.bank-accounts', [
            'accounts' => BankAccount::latest()->get(),
        ]);
    }
}
