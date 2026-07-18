<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Course;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.public')]
class CourseCatalogue extends Component
{
    use WithPagination;

    #[Url]
    public string $keyword = '';

    #[Url]
    public ?int $category = null;

    #[Url]
    public string $level = '';

    #[Url]
    public string $language = '';

    public function updating(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $courses = Course::query()
            ->published()
            ->with('category', 'lecturers')
            ->when($this->keyword, fn ($query) => $query->where(function ($q) {
                $q->where('title', 'like', "%{$this->keyword}%")
                    ->orWhere('short_description', 'like', "%{$this->keyword}%");
            }))
            ->when($this->category, fn ($query) => $query->where('category_id', $this->category))
            ->when($this->level, fn ($query) => $query->where('level', $this->level))
            ->when($this->language, fn ($query) => $query->where('teaching_language', $this->language))
            ->latest('published_at')
            ->paginate(12);

        return view('livewire.course-catalogue', [
            'courses' => $courses,
            'categories' => Category::where('is_active', true)->orderBy('name')->get(),
        ]);
    }
}
