<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use App\Models\NewsItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class News extends Component
{
    use WithFileUploads;

    public ?int $editingId = null;

    public string $title_en = '';

    public string $title_ar = '';

    public string $excerpt_en = '';

    public string $excerpt_ar = '';

    public string $link_url = '';

    public $image;

    public ?string $currentImagePath = null;

    public function newForm(): void
    {
        $this->reset(['editingId', 'title_en', 'title_ar', 'excerpt_en', 'excerpt_ar', 'link_url', 'image', 'currentImagePath']);
    }

    public function edit(int $id): void
    {
        $item = NewsItem::findOrFail($id);

        $this->editingId = $item->id;
        $this->title_en = $item->title_en;
        $this->title_ar = $item->title_ar;
        $this->excerpt_en = $item->excerpt_en;
        $this->excerpt_ar = $item->excerpt_ar;
        $this->link_url = (string) $item->link_url;
        $this->currentImagePath = $item->image_path;
        $this->image = null;
    }

    public function cancelEdit(): void
    {
        $this->newForm();
    }

    public function save(): void
    {
        $validated = $this->validate([
            'title_en' => ['required', 'string', 'max:255'],
            'title_ar' => ['required', 'string', 'max:255'],
            'excerpt_en' => ['required', 'string', 'max:1000'],
            'excerpt_ar' => ['required', 'string', 'max:1000'],
            'link_url' => ['nullable', 'url', 'max:2048'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);

        unset($validated['image']);
        $validated['link_url'] = $validated['link_url'] ?: null;

        if ($this->editingId) {
            $item = NewsItem::findOrFail($this->editingId);
            $old = $item->only(['title_en', 'title_ar', 'excerpt_en', 'excerpt_ar', 'link_url']);
            $item->update($validated);

            AuditLog::record('news_item.updated', subject: $item, old: $old, new: $validated);
        } else {
            $nextPosition = (int) (NewsItem::max('position') ?? 0) + 1;

            $item = NewsItem::create([
                ...$validated,
                'position' => $nextPosition,
                'is_published' => true,
                'created_by' => Auth::id(),
            ]);

            AuditLog::record('news_item.created', subject: $item, new: $validated);
        }

        if ($this->image) {
            $item->update(['image_path' => $this->image->store('news-images', 'public')]);
        }

        $this->newForm();
    }

    public function togglePublished(int $id): void
    {
        $item = NewsItem::findOrFail($id);
        $old = ['is_published' => $item->is_published];
        $item->update(['is_published' => ! $item->is_published]);

        AuditLog::record('news_item.toggled', subject: $item, old: $old, new: ['is_published' => $item->is_published]);
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
        $item = NewsItem::findOrFail($id);

        $neighbour = NewsItem::where('position', $direction < 0 ? '<' : '>', $item->position)
            ->orderBy('position', $direction < 0 ? 'desc' : 'asc')
            ->first();

        if (! $neighbour) {
            return;
        }

        [$itemPosition, $neighbourPosition] = [$item->position, $neighbour->position];

        $item->update(['position' => $neighbourPosition]);
        $neighbour->update(['position' => $itemPosition]);
    }

    public function delete(int $id): void
    {
        $item = NewsItem::findOrFail($id);

        if ($item->image_path) {
            Storage::disk('public')->delete($item->image_path);
        }

        AuditLog::record('news_item.deleted', subject: $item, old: $item->only(['title_en']));

        $item->delete();
    }

    public function render()
    {
        return view('livewire.admin.news', [
            'newsItems' => NewsItem::orderBy('position')->get(),
        ]);
    }
}
