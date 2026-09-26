<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use App\Models\Faq;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Faqs extends Component
{
    public ?int $editingId = null;

    public string $question_en = '';

    public string $question_ar = '';

    public string $answer_en = '';

    public string $answer_ar = '';

    public ?int $deletingId = null;

    public function newForm(): void
    {
        $this->reset(['editingId', 'question_en', 'question_ar', 'answer_en', 'answer_ar']);
    }

    public function edit(int $id): void
    {
        $faq = Faq::findOrFail($id);

        $this->editingId = $faq->id;
        $this->question_en = $faq->question_en;
        $this->question_ar = $faq->question_ar;
        $this->answer_en = $faq->answer_en;
        $this->answer_ar = $faq->answer_ar;
    }

    public function cancelEdit(): void
    {
        $this->reset(['editingId', 'question_en', 'question_ar', 'answer_en', 'answer_ar']);
    }

    public function save(): void
    {
        $validated = $this->validate([
            'question_en' => ['required', 'string', 'max:500'],
            'question_ar' => ['required', 'string', 'max:500'],
            'answer_en' => ['required', 'string', 'max:5000'],
            'answer_ar' => ['required', 'string', 'max:5000'],
        ]);

        if ($this->editingId) {
            $faq = Faq::findOrFail($this->editingId);
            $old = $faq->only(['question_en', 'question_ar', 'answer_en', 'answer_ar']);
            $faq->update($validated);

            AuditLog::record('faq.updated', subject: $faq, old: $old, new: $validated);
        } else {
            $nextPosition = (int) (Faq::max('position') ?? 0) + 1;

            $faq = Faq::create([...$validated, 'position' => $nextPosition, 'is_published' => true]);

            AuditLog::record('faq.created', subject: $faq, new: $validated);
        }

        $this->cancelEdit();
    }

    public function togglePublished(int $id): void
    {
        $faq = Faq::findOrFail($id);
        $old = ['is_published' => $faq->is_published];
        $faq->update(['is_published' => ! $faq->is_published]);

        AuditLog::record('faq.toggled', subject: $faq, old: $old, new: ['is_published' => $faq->is_published]);
    }

    public function moveUp(int $id): void
    {
        $this->swapPosition($id, -1);
    }

    public function moveDown(int $id): void
    {
        $this->swapPosition($id, 1);
    }

    protected function swapPosition(int $id, int $direction): void
    {
        $faq = Faq::findOrFail($id);

        $neighbour = Faq::where('position', $direction < 0 ? '<' : '>', $faq->position)
            ->orderBy('position', $direction < 0 ? 'desc' : 'asc')
            ->first();

        if (! $neighbour) {
            return;
        }

        [$faqPosition, $neighbourPosition] = [$faq->position, $neighbour->position];

        $faq->update(['position' => $neighbourPosition]);
        $neighbour->update(['position' => $faqPosition]);
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingId = $id;
    }

    public function delete(): void
    {
        $faq = Faq::findOrFail($this->deletingId);

        AuditLog::record('faq.deleted', subject: $faq, old: $faq->only(['question_en']));

        $faq->delete();

        $this->deletingId = null;
    }

    public function render()
    {
        return view('livewire.admin.faqs', [
            'faqs' => Faq::orderBy('position')->get(),
        ]);
    }
}
