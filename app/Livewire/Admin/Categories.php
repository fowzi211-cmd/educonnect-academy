<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use App\Models\Category;
use Illuminate\Support\Facades\Validator;
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

    public bool $showBulkAdd = false;

    public string $bulkAddText = '';

    public int $bulkAddCreated = 0;

    /** @var array<int, array{line: string, error: string}> */
    public array $bulkAddFailures = [];

    protected const BULK_ADD_MAX_ROWS = 200;

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

    public function toggleBulkAdd(): void
    {
        $this->showBulkAdd = ! $this->showBulkAdd;
        $this->reset(['bulkAddText', 'bulkAddFailures', 'bulkAddCreated']);
    }

    /**
     * One category name per line — an optional " | description" suffix is
     * supported. Each row is validated independently so a single duplicate
     * or over-length name doesn't abort the rest of the paste.
     */
    public function runBulkAdd(): void
    {
        $this->bulkAddFailures = [];
        $this->bulkAddCreated = 0;

        $lines = array_values(array_filter(array_map('trim', explode("\n", $this->bulkAddText)), fn ($line) => $line !== ''));

        if (empty($lines)) {
            $this->addError('bulkAddText', __('Paste at least one row first.'));

            return;
        }

        if (count($lines) > self::BULK_ADD_MAX_ROWS) {
            $this->addError('bulkAddText', __('Import up to :max rows at a time — split larger lists into batches.', ['max' => self::BULK_ADD_MAX_ROWS]));

            return;
        }

        $seenNames = [];

        foreach ($lines as $line) {
            [$name, $description] = array_pad(array_map('trim', explode('|', $line, 2)), 2, null);
            $nameKey = mb_strtolower($name);

            $rowValidator = Validator::make(
                ['name' => $name, 'description' => $description],
                [
                    'name' => ['required', 'string', 'max:255', 'unique:categories,name'],
                    'description' => ['nullable', 'string', 'max:1000'],
                ],
            );

            if (in_array($nameKey, $seenNames, true)) {
                $this->bulkAddFailures[] = ['line' => $line, 'error' => __('Duplicate name within this import.')];

                continue;
            }

            if ($rowValidator->fails()) {
                $this->bulkAddFailures[] = ['line' => $line, 'error' => $rowValidator->errors()->first()];

                continue;
            }

            $category = Category::create(['name' => $name, 'description' => $description ?: null]);
            AuditLog::record('category.created', subject: $category, new: ['name' => $name, 'description' => $description]);

            $seenNames[] = $nameKey;
            $this->bulkAddCreated++;
        }

        $this->bulkAddText = '';
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
