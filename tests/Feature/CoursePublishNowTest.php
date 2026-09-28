<?php

namespace Tests\Feature;

use App\Livewire\Lecturer\CourseForm;
use App\Models\Category;
use App\Models\Course;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CoursePublishNowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    private function user(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function draftCourse(User $owner): Course
    {
        $category = Category::create(['name' => 'Medicine']);

        return Course::create([
            'title' => 'Anatomy', 'slug' => 'anatomy', 'short_description' => 's', 'full_description' => 'f',
            'category_id' => $category->id, 'level' => 'beginner', 'teaching_language' => 'en',
            'delivery_format' => 'recorded', 'certificate_available' => false, 'status' => Course::STATUS_DRAFT, 'created_by' => $owner->id,
        ]);
    }

    public function test_a_super_admin_can_publish_a_draft_directly_and_it_appears_in_the_catalogue(): void
    {
        $admin = $this->user('super_administrator');
        $course = $this->draftCourse($admin);

        $this->get('/courses')->assertDontSee('Anatomy');

        Livewire::actingAs($admin)
            ->test(CourseForm::class, ['course' => $course])
            ->call('publishNow')
            ->assertSet('published', true);

        $fresh = $course->fresh();
        $this->assertSame(Course::STATUS_PUBLISHED, $fresh->status);
        $this->assertNotNull($fresh->published_at);

        $this->get('/courses')->assertSee('Anatomy');
    }

    public function test_a_lecturer_cannot_publish_their_own_course_directly(): void
    {
        $lecturer = $this->user('lecturer');
        $course = $this->draftCourse($lecturer);

        Livewire::actingAs($lecturer)
            ->test(CourseForm::class, ['course' => $course])
            ->call('publishNow')
            ->assertForbidden();

        $this->assertSame(Course::STATUS_DRAFT, $course->fresh()->status);
    }

    public function test_the_publish_now_button_is_only_shown_to_publishers(): void
    {
        $admin = $this->user('super_administrator');
        $course = $this->draftCourse($admin);

        Livewire::actingAs($admin)
            ->test(CourseForm::class, ['course' => $course])
            ->assertSee('Publish Now');

        $lecturer = $this->user('lecturer');
        $ownCourse = Course::create([
            'title' => 'Lecturer Course', 'slug' => 'lecturer-course', 'short_description' => 's', 'full_description' => 'f',
            'category_id' => $course->category_id, 'level' => 'beginner', 'teaching_language' => 'en',
            'delivery_format' => 'recorded', 'certificate_available' => false, 'status' => Course::STATUS_DRAFT, 'created_by' => $lecturer->id,
        ]);

        Livewire::actingAs($lecturer)
            ->test(CourseForm::class, ['course' => $ownCourse])
            ->assertDontSee('Publish Now')
            ->assertSee('Submit for Review');
    }
}
