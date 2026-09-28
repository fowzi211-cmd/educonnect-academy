<?php

namespace App\Livewire\Lecturer;

use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Course;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class CourseForm extends Component
{
    use WithFileUploads;

    public ?Course $course = null;

    public string $title = '';

    public string $short_description = '';

    public string $full_description = '';

    public ?int $category_id = null;

    public string $level = 'beginner';

    public string $teaching_language = 'en';

    public string $delivery_format = 'recorded';

    public ?string $monthly_price = null;

    public ?string $one_time_price = null;

    public ?int $trial_period_days = null;

    public ?int $max_students = null;

    public bool $certificate_available = false;

    public $image;

    public bool $saved = false;

    public bool $published = false;

    public function mount(?Course $course = null): void
    {
        if ($course && $course->exists) {
            abort_unless($course->isEditableBy(Auth::user()), 403);

            $this->course = $course;
            $this->title = $course->title;
            $this->short_description = (string) $course->short_description;
            $this->full_description = (string) $course->full_description;
            $this->category_id = $course->category_id;
            $this->level = (string) $course->level;
            $this->teaching_language = $course->teaching_language;
            $this->delivery_format = $course->delivery_format;
            $this->monthly_price = $course->monthly_price !== null ? (string) $course->monthly_price : null;
            $this->one_time_price = $course->one_time_price !== null ? (string) $course->one_time_price : null;
            $this->trial_period_days = $course->trial_period_days;
            $this->max_students = $course->max_students;
            $this->certificate_available = $course->certificate_available;
        }
    }

    protected function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'short_description' => ['required', 'string', 'max:255'],
            'full_description' => ['required', 'string', 'max:10000'],
            'category_id' => ['required', 'exists:categories,id'],
            'level' => ['required', 'in:'.implode(',', Course::LEVELS)],
            'teaching_language' => ['required', 'string', 'in:en,ar,both'],
            'delivery_format' => ['required', 'in:'.implode(',', Course::DELIVERY_FORMATS)],
            'monthly_price' => ['nullable', 'numeric', 'min:0', 'max:99999'],
            'one_time_price' => ['nullable', 'numeric', 'min:0', 'max:99999'],
            'trial_period_days' => ['nullable', 'integer', 'min:0', 'max:365'],
            'max_students' => ['nullable', 'integer', 'min:1'],
            'certificate_available' => ['boolean'],
            'image' => ['nullable', 'image', 'max:2048'],
        ];
    }

    public function save(): void
    {
        $validated = $this->validate();
        unset($validated['image']);

        if ($this->course) {
            $old = $this->course->only(array_keys($validated));
            $this->course->update($validated);
            AuditLog::record('course.updated', subject: $this->course, old: $old, new: $validated);
        } else {
            $validated['created_by'] = Auth::id();
            $validated['status'] = Course::STATUS_DRAFT;
            $validated['currency'] = Setting::get('branding.default_currency', config('platform.default_currency'));

            $this->course = Course::create($validated);
            $this->course->lecturers()->attach(Auth::id(), ['is_primary' => true]);

            AuditLog::record('course.created', subject: $this->course, new: $validated);
        }

        if ($this->image) {
            $this->course->update(['image_path' => $this->image->store('course-images', 'public')]);
        }

        $this->saved = true;
    }

    public function submitForReview(): void
    {
        abort_unless($this->course && $this->course->created_by === Auth::id(), 403);
        abort_unless(in_array($this->course->status, [Course::STATUS_DRAFT, Course::STATUS_REVISION_REQUESTED], true), 403);

        $old = ['status' => $this->course->status];

        $this->course->update([
            'status' => Course::STATUS_UNDER_REVIEW,
            'submitted_at' => now(),
        ]);

        AuditLog::record('course.submitted_for_review', subject: $this->course, old: $old, new: ['status' => Course::STATUS_UNDER_REVIEW]);
    }

    /**
     * Publishers (admins with the "publish courses" permission) can skip the
     * submit -> approve -> publish chain for a course they are already allowed
     * to edit; the decision is still recorded in the audit log.
     */
    public function publishNow(): void
    {
        abort_unless($this->course && Auth::user()->can('publish courses'), 403);
        abort_unless($this->course->isEditableBy(Auth::user()), 403);
        abort_unless(in_array($this->course->status, [
            Course::STATUS_DRAFT,
            Course::STATUS_UNDER_REVIEW,
            Course::STATUS_REVISION_REQUESTED,
            Course::STATUS_APPROVED,
            Course::STATUS_UNPUBLISHED,
        ], true), 403);

        $old = ['status' => $this->course->status];

        $this->course->update([
            'status' => Course::STATUS_PUBLISHED,
            'published_at' => now(),
            'revision_notes' => null,
        ]);

        AuditLog::record('course.published', subject: $this->course, old: $old, new: ['status' => Course::STATUS_PUBLISHED], reason: 'Published directly from the course form');

        $this->published = true;
    }

    public function render()
    {
        return view('livewire.lecturer.course-form', [
            'categories' => Category::where('is_active', true)->orderBy('name')->get(),
        ]);
    }
}
