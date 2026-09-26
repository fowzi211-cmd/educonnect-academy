<?php

namespace App\Livewire\Admin\Finance;

use App\Models\AuditLog;
use App\Models\Coupon;
use App\Models\Course;
use App\Models\User;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Coupons extends Component
{
    public string $code = '';

    public string $type = Coupon::TYPE_PERCENTAGE;

    public string $value = '';

    public ?int $courseId = null;

    public string $studentEmail = '';

    public string $maxRedemptions = '';

    public string $expiresAt = '';

    public ?int $editingId = null;

    public string $editingCode = '';

    public string $editingType = Coupon::TYPE_PERCENTAGE;

    public string $editingValue = '';

    public ?int $editingCourseId = null;

    public string $editingStudentEmail = '';

    public string $editingMaxRedemptions = '';

    public string $editingExpiresAt = '';

    protected function rules(bool $editing): array
    {
        $field = fn (string $base) => $editing ? 'editing'.ucfirst($base) : $base;
        $ignoreId = $editing ? $this->editingId : null;

        return [
            $field('code') => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('coupons', 'code')->ignore($ignoreId)],
            $field('type') => ['required', 'in:'.Coupon::TYPE_PERCENTAGE.','.Coupon::TYPE_FIXED],
            $field('value') => ['required', 'numeric', 'gt:0'],
            $field('courseId') => ['nullable', 'exists:courses,id'],
            $field('studentEmail') => ['nullable', 'email', 'exists:users,email'],
            $field('maxRedemptions') => ['nullable', 'integer', 'min:1'],
            $field('expiresAt') => ['nullable', 'date'],
        ];
    }

    public function create(): void
    {
        $this->code = strtoupper(trim($this->code));

        $validated = $this->validate($this->rules(false));

        $coupon = Coupon::create([
            'code' => strtoupper($validated['code']),
            'type' => $validated['type'],
            'value' => $validated['value'],
            'course_id' => $validated['courseId'] ?: null,
            'user_id' => $this->studentEmail ? User::where('email', $this->studentEmail)->value('id') : null,
            'max_redemptions' => $validated['maxRedemptions'] ?: null,
            'expires_at' => $validated['expiresAt'] ?: null,
            'is_active' => true,
        ]);

        AuditLog::record('coupon.created', subject: $coupon, new: $coupon->only(['code', 'type', 'value', 'course_id', 'user_id']));

        $this->reset(['code', 'type', 'value', 'courseId', 'studentEmail', 'maxRedemptions', 'expiresAt']);
        $this->type = Coupon::TYPE_PERCENTAGE;
    }

    public function edit(int $id): void
    {
        $coupon = Coupon::findOrFail($id);

        $this->editingId = $coupon->id;
        $this->editingCode = $coupon->code;
        $this->editingType = $coupon->type;
        $this->editingValue = (string) $coupon->value;
        $this->editingCourseId = $coupon->course_id;
        $this->editingStudentEmail = $coupon->user?->email ?? '';
        $this->editingMaxRedemptions = (string) ($coupon->max_redemptions ?? '');
        $this->editingExpiresAt = $coupon->expires_at?->format('Y-m-d') ?? '';
    }

    public function cancelEdit(): void
    {
        $this->reset([
            'editingId', 'editingCode', 'editingType', 'editingValue', 'editingCourseId',
            'editingStudentEmail', 'editingMaxRedemptions', 'editingExpiresAt',
        ]);
        $this->editingType = Coupon::TYPE_PERCENTAGE;
    }

    public function update(): void
    {
        $this->editingCode = strtoupper(trim($this->editingCode));

        $validated = $this->validate($this->rules(true));

        $coupon = Coupon::findOrFail($this->editingId);
        $old = $coupon->only(['code', 'type', 'value', 'course_id', 'user_id', 'max_redemptions', 'expires_at']);

        $coupon->update([
            'code' => strtoupper($validated['editingCode']),
            'type' => $validated['editingType'],
            'value' => $validated['editingValue'],
            'course_id' => $validated['editingCourseId'] ?: null,
            'user_id' => $this->editingStudentEmail ? User::where('email', $this->editingStudentEmail)->value('id') : null,
            'max_redemptions' => $validated['editingMaxRedemptions'] ?: null,
            'expires_at' => $validated['editingExpiresAt'] ?: null,
        ]);

        AuditLog::record('coupon.updated', subject: $coupon, old: $old, new: $coupon->only(['code', 'type', 'value', 'course_id', 'user_id', 'max_redemptions', 'expires_at']));

        $this->cancelEdit();
    }

    public function toggleActive(int $id): void
    {
        $coupon = Coupon::findOrFail($id);
        $old = ['is_active' => $coupon->is_active];
        $coupon->update(['is_active' => ! $coupon->is_active]);

        AuditLog::record('coupon.toggled', subject: $coupon, old: $old, new: ['is_active' => $coupon->is_active]);
    }

    public function delete(int $id): void
    {
        $coupon = Coupon::withCount('redemptions')->findOrFail($id);

        if ($coupon->redemptions_count > 0) {
            $this->addError('delete', __('This coupon has already been redeemed and cannot be deleted. Deactivate it instead.'));

            return;
        }

        AuditLog::record('coupon.deleted', subject: $coupon, old: $coupon->only(['code']));

        $coupon->delete();
    }

    public function render()
    {
        return view('livewire.admin.finance.coupons', [
            'coupons' => Coupon::with(['course', 'user'])->withCount('redemptions')->latest()->get(),
            'courses' => Course::orderBy('title')->get(),
        ]);
    }
}
