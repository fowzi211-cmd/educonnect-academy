<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use App\Models\Category;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Categories extends Component
{
    public string $name = '';
    public string $description = '';

    public ?int $editingId = null;
    public string $editingName = '';
    public string $editingDescription = '';

    public function create(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255', 'unique:categories,name'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $category = Category::create($validated);

        AuditLog::record('category.created', subject: $category, new: $validated);

        $this->reset(['name', 'description']);
    }

    public function edit(int $id): void
    {
        $category = Category::findOrFail($id);
        $this->editingId = $category->id;
        $this->editingName = $category->name;
        $this->editingDescription = (string) $category->description;
    }

    public function cancelEdit(): void
    {
        $this->reset(['editingId', 'editingName', 'editingDescription']);
    }

    public function update(): void
    {
        $validated = $this->validate([
            'editingName' => ['required', 'string', 'max:255', 'unique:categories,name,'.$this->editingId],
            'editingDescription' => ['nullable', 'string', 'max:1000'],
        ]);

        $category = Category::findOrFail($this->editingId);
        $old = $category->only(['name', 'description']);

        $category->update([
            'name' => $validated['editingName'],
            'description' => $validated['editingDescription'],
        ]);

        AuditLog::record('category.updated', subject: $category, old: $old, new: $category->only(['name', 'description']));

        $this->cancelEdit();
    }

    public function toggleActive(int $id): void
    {
        $category = Category::findOrFail($id);
        $old = ['is_active' => $category->is_active];
        $category->update(['is_active' => ! $category->is_active]);

        AuditLog::record('category.toggled', subject: $category, old: $old, new: ['is_active' => $category->is_active]);
    }

    public function delete(int $id): void
    {
        $category = Category::withCount('courses')->findOrFail($id);

        if ($category->courses_count > 0) {
            $this->addError('delete', __('This category has courses assigned and cannot be deleted. Deactivate it instead.'));

            return;
        }

        AuditLog::record('category.deleted', subject: $category, old: $category->only(['name']));

        $category->delete();
    }

    public function render()
    {
        return view('livewire.admin.categories', [
            'categories' => Category::withCount('courses')->orderBy('name')->get(),
        ]);
    }
}
