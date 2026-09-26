<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use App\Models\Page;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Pages extends Component
{
    public ?int $editingId = null;

    public string $title_en = '';

    public string $title_ar = '';

    public string $body_en = '';

    public string $body_ar = '';

    public bool $saved = false;

    public function edit(int $id): void
    {
        $page = Page::findOrFail($id);

        $this->editingId = $page->id;
        $this->title_en = $page->title_en;
        $this->title_ar = $page->title_ar;
        $this->body_en = $page->body_en;
        $this->body_ar = $page->body_ar;
        $this->saved = false;
    }

    public function cancelEdit(): void
    {
        $this->reset(['editingId', 'title_en', 'title_ar', 'body_en', 'body_ar']);
    }

    public function save(): void
    {
        $validated = $this->validate([
            'title_en' => ['required', 'string', 'max:255'],
            'title_ar' => ['required', 'string', 'max:255'],
            'body_en' => ['required', 'string'],
            'body_ar' => ['required', 'string'],
        ]);

        $page = Page::findOrFail($this->editingId);
        $old = $page->only(['title_en', 'title_ar', 'body_en', 'body_ar']);

        $page->update([...$validated, 'updated_by' => Auth::id()]);

        AuditLog::record('page.updated', subject: $page, old: $old, new: $validated);

        $this->saved = true;
    }

    public function render()
    {
        return view('livewire.admin.pages', [
            'pages' => Page::with('updatedBy')->orderBy('slug')->get(),
        ]);
    }
}
