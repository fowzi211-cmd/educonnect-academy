<?php

namespace App\Livewire\Lecturer;

use App\Models\AuditLog;
use App\Models\Course;
use App\Models\LiveClass;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class LiveClasses extends Component
{
    public Course $course;

    public bool $showForm = false;
    public ?int $editingId = null;

    public string $title = '';
    public string $description = '';
    public string $starts_at = '';
    public string $ends_at = '';
    public string $provider = 'zoom';
    public string $meeting_link = '';
    public string $host_link = '';

    public ?int $cancellingId = null;
    public string $cancellation_reason = '';

    public function mount(Course $course): void
    {
        abort_unless($course->isTaughtBy(Auth::user()), 403);
        $this->course = $course;
    }

    public function newForm(): void
    {
        $this->reset(['editingId', 'title', 'description', 'starts_at', 'ends_at', 'provider', 'meeting_link', 'host_link']);
        $this->provider = 'zoom';
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $liveClass = $this->course->liveClasses()->findOrFail($id);

        $this->editingId = $liveClass->id;
        $this->title = $liveClass->title;
        $this->description = (string) $liveClass->description;
        $this->starts_at = $liveClass->starts_at->format('Y-m-d\TH:i');
        $this->ends_at = $liveClass->ends_at->format('Y-m-d\TH:i');
        $this->provider = $liveClass->provider;
        $this->meeting_link = $liveClass->meeting_link;
        $this->host_link = (string) $liveClass->host_link;
        $this->showForm = true;
    }

    public function cancelForm(): void
    {
        $this->showForm = false;
    }

    protected function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'provider' => ['required', 'in:'.implode(',', LiveClass::PROVIDERS)],
            'meeting_link' => ['required', 'url', 'max:255'],
            'host_link' => ['nullable', 'url', 'max:255'],
        ];
    }

    public function save(): void
    {
        $validated = $this->validate();

        if ($this->editingId) {
            $liveClass = $this->course->liveClasses()->findOrFail($this->editingId);
            $old = $liveClass->only(array_keys($validated));
            $liveClass->update($validated);
            AuditLog::record('live_class.updated', subject: $liveClass, old: $old, new: $validated);
        } else {
            $liveClass = $this->course->liveClasses()->create(array_merge($validated, [
                'host_id' => Auth::id(),
                'status' => LiveClass::STATUS_SCHEDULED,
            ]));
            AuditLog::record('live_class.scheduled', subject: $liveClass, new: $validated);
        }

        $this->showForm = false;
    }

    public function startCancel(int $id): void
    {
        $this->cancellingId = $id;
        $this->cancellation_reason = '';
    }

    public function confirmCancel(): void
    {
        $this->validate(['cancellation_reason' => ['required', 'string', 'max:1000']]);

        $liveClass = $this->course->liveClasses()->findOrFail($this->cancellingId);
        $liveClass->update([
            'status' => LiveClass::STATUS_CANCELLED,
            'cancellation_reason' => $this->cancellation_reason,
        ]);

        AuditLog::record('live_class.cancelled', subject: $liveClass, reason: $this->cancellation_reason);

        $this->cancellingId = null;
    }

    public function render()
    {
        return view('livewire.lecturer.live-classes', [
            'liveClasses' => $this->course->liveClasses()->orderBy('starts_at')->get(),
        ]);
    }
}
