<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use App\Models\LectureRoom;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class LectureRooms extends Component
{
    public string $name = '';

    public string $capacity = '';

    public string $location = '';

    public string $notes = '';

    public ?int $editingId = null;

    public string $editingName = '';

    public string $editingCapacity = '';

    public string $editingLocation = '';

    public string $editingNotes = '';

    public bool $showBulkAdd = false;

    public string $bulkAddText = '';

    public int $bulkAddCreated = 0;

    /** @var array<int, array{line: string, error: string}> */
    public array $bulkAddFailures = [];

    protected const BULK_ADD_MAX_ROWS = 200;

    protected function rules(bool $editing): array
    {
        $field = fn (string $base) => $editing ? 'editing'.ucfirst($base) : $base;

        return [
            $field('name') => ['required', 'string', 'max:255', Rule::unique('lecture_rooms', 'name')->ignore($editing ? $this->editingId : null)],
            $field('capacity') => ['nullable', 'integer', 'min:1', 'max:100000'],
            $field('location') => ['nullable', 'string', 'max:255'],
            $field('notes') => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function create(): void
    {
        $validated = $this->validate($this->rules(false));

        $room = LectureRoom::create([
            'name' => $validated['name'],
            'capacity' => $validated['capacity'] ?: null,
            'location' => $validated['location'] ?: null,
            'notes' => $validated['notes'] ?: null,
            'is_active' => true,
        ]);

        AuditLog::record('lecture_room.created', subject: $room, new: $room->only(['name', 'capacity', 'location']));

        $this->reset(['name', 'capacity', 'location', 'notes']);
    }

    public function edit(int $id): void
    {
        $room = LectureRoom::findOrFail($id);

        $this->editingId = $room->id;
        $this->editingName = $room->name;
        $this->editingCapacity = (string) ($room->capacity ?? '');
        $this->editingLocation = (string) $room->location;
        $this->editingNotes = (string) $room->notes;
    }

    public function cancelEdit(): void
    {
        $this->reset(['editingId', 'editingName', 'editingCapacity', 'editingLocation', 'editingNotes']);
    }

    public function update(): void
    {
        $validated = $this->validate($this->rules(true));

        $room = LectureRoom::findOrFail($this->editingId);
        $old = $room->only(['name', 'capacity', 'location', 'notes']);

        $room->update([
            'name' => $validated['editingName'],
            'capacity' => $validated['editingCapacity'] ?: null,
            'location' => $validated['editingLocation'] ?: null,
            'notes' => $validated['editingNotes'] ?: null,
        ]);

        AuditLog::record('lecture_room.updated', subject: $room, old: $old, new: $room->only(['name', 'capacity', 'location', 'notes']));

        $this->cancelEdit();
    }

    public function toggleActive(int $id): void
    {
        $room = LectureRoom::findOrFail($id);
        $old = ['is_active' => $room->is_active];
        $room->update(['is_active' => ! $room->is_active]);

        AuditLog::record('lecture_room.toggled', subject: $room, old: $old, new: ['is_active' => $room->is_active]);
    }

    public function delete(int $id): void
    {
        $room = LectureRoom::findOrFail($id);

        AuditLog::record('lecture_room.deleted', subject: $room, old: $room->only(['name']));

        $room->delete();
    }

    public function toggleBulkAdd(): void
    {
        $this->showBulkAdd = ! $this->showBulkAdd;
        $this->reset(['bulkAddText', 'bulkAddFailures', 'bulkAddCreated']);
    }

    /**
     * "Name | Capacity | Location" per line, capacity/location optional.
     * Each row validated independently, same as Categories' bulk add.
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
            $fields = array_pad(array_map('trim', explode('|', $line, 3)), 3, null);
            [$name, $capacity, $location] = $fields;
            $nameKey = mb_strtolower($name);

            $rowValidator = Validator::make(
                ['name' => $name, 'capacity' => $capacity ?: null, 'location' => $location],
                [
                    'name' => ['required', 'string', 'max:255', 'unique:lecture_rooms,name'],
                    'capacity' => ['nullable', 'integer', 'min:1', 'max:100000'],
                    'location' => ['nullable', 'string', 'max:255'],
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

            $room = LectureRoom::create([
                'name' => $name,
                'capacity' => $capacity ?: null,
                'location' => $location ?: null,
                'is_active' => true,
            ]);

            AuditLog::record('lecture_room.created', subject: $room, new: $room->only(['name', 'capacity', 'location']));

            $seenNames[] = $nameKey;
            $this->bulkAddCreated++;
        }

        $this->bulkAddText = '';
    }

    public function render()
    {
        return view('livewire.admin.lecture-rooms', [
            'rooms' => LectureRoom::orderBy('name')->get(),
        ]);
    }
}
