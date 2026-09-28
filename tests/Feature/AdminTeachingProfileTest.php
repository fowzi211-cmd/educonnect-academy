<?php

namespace Tests\Feature;

use App\Livewire\Admin\LecturerApplicationReview;
use App\Livewire\LecturerApplicationForm;
use App\Models\LecturerProfile;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminTeachingProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    private function admin(): User
    {
        $user = User::factory()->create(['name' => 'Dr Admin']);
        $user->assignRole('super_administrator');
        $user->profile()->create(['preferred_language' => 'en']);

        return $user;
    }

    private function submitProfile(User $user): void
    {
        Livewire::actingAs($user)
            ->test(LecturerApplicationForm::class)
            ->set('headline', 'Professor')
            ->set('biography', 'Bio')
            ->set('qualifications', 'Quals')
            ->set('areas_of_expertise', 'Anatomy')
            ->call('apply')
            ->assertHasNoErrors();
    }

    public function test_an_admin_without_an_approved_profile_sees_the_teaching_profile_link(): void
    {
        $this->actingAs($this->admin())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee(route('lecturer-application'), false)
            ->assertSee('Teaching Profile');
    }

    public function test_submitting_a_profile_does_not_approve_it_automatically(): void
    {
        $admin = $this->admin();

        $this->submitProfile($admin);

        $this->assertSame(LecturerProfile::STATUS_PENDING, $admin->fresh()->lecturerProfile->status);
        $this->get('/lecturers/'.$admin->id)->assertNotFound();
    }

    public function test_the_admin_can_approve_the_profile_from_lecturer_applications_and_it_goes_public(): void
    {
        $admin = $this->admin();
        $this->submitProfile($admin);
        $profile = $admin->fresh()->lecturerProfile;

        Livewire::actingAs($admin)
            ->test(LecturerApplicationReview::class, ['lecturerProfile' => $profile])
            ->call('approve');

        $this->assertSame(LecturerProfile::STATUS_APPROVED, $profile->fresh()->status);
        $this->get('/lecturers/'.$admin->id)->assertOk()->assertSee('Dr Admin');
        $this->get('/lecturers')->assertOk()->assertSee('Dr Admin');

        $this->actingAs($admin->fresh())
            ->get(route('dashboard'))
            ->assertDontSee('Teaching Profile');
    }
}
