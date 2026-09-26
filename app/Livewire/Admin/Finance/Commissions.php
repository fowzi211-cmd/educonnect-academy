<?php

namespace App\Livewire\Admin\Finance;

use App\Models\AuditLog;
use App\Models\Course;
use App\Models\LecturerCommissionRate;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Commissions extends Component
{
    use WithPagination;

    public ?int $lecturerId = null;

    public ?int $courseId = null;

    public string $type = 'percentage';

    public ?float $value = null;

    public ?int $editingId = null;

    public ?int $deletingId = null;

    public function edit(int $id): void
    {
        $rate = LecturerCommissionRate::findOrFail($id);

        $this->editingId = $id;
        $this->lecturerId = $rate->user_id;
        $this->courseId = $rate->course_id;
        $this->type = $rate->type;
        $this->value = (float) $rate->value;
    }

    public function cancelEdit(): void
    {
        $this->reset(['editingId', 'lecturerId', 'courseId', 'type', 'value']);
        $this->type = 'percentage';
    }

    public function save(): void
    {
        $validated = $this->validate([
            'lecturerId' => ['required', 'exists:users,id'],
            'courseId' => ['nullable', 'exists:courses,id'],
            'type' => ['required', 'string', 'in:percentage,fixed'],
            'value' => ['required', 'numeric', 'min:0'],
        ]);

        if ($validated['type'] === 'percentage' && $validated['value'] > 100) {
            $this->addError('value', __('A percentage rate cannot exceed 100.'));

            return;
        }

        $existing = LecturerCommissionRate::where('user_id', $validated['lecturerId'])
            ->where('course_id', $validated['courseId'])
            ->when($this->editingId, fn ($q) => $q->where('id', '!=', $this->editingId))
            ->first();

        if ($existing) {
            $this->addError('courseId', __('A rate already exists for this lecturer and course combination. Edit the existing one instead.'));

            return;
        }

        $attributes = [
            'user_id' => $validated['lecturerId'],
            'course_id' => $validated['courseId'],
            'type' => $validated['type'],
            'value' => $validated['value'],
        ];

        if ($this->editingId) {
            $rate = LecturerCommissionRate::findOrFail($this->editingId);
            $old = $rate->only(['user_id', 'course_id', 'type', 'value']);
            $rate->update($attributes);

            AuditLog::record('commission_rate.updated', subject: $rate, old: $old, new: $attributes);
        } else {
            $rate = LecturerCommissionRate::create($attributes);

            AuditLog::record('commission_rate.created', subject: $rate, new: $attributes);
        }

        $this->cancelEdit();
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingId = $id;
    }

    public function delete(): void
    {
        $rate = LecturerCommissionRate::findOrFail($this->deletingId);
        $rate->delete();

        AuditLog::record('commission_rate.deleted', subject: $rate, old: $rate->only(['user_id', 'course_id', 'type', 'value']));

        $this->deletingId = null;
    }

    public function render()
    {
        return view('livewire.admin.finance.commissions', [
            'rates' => LecturerCommissionRate::with(['user', 'course'])->latest()->paginate(15),
            'lecturers' => User::role('lecturer')->orderBy('name')->get(),
            'courses' => Course::orderBy('title')->get(),
        ]);
    }
}
