<?php

namespace Tests\Feature;

use App\Livewire\Admin\Users;
use App\Models\AuditLog;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class AdminUserPasswordTest extends TestCase
{
    use RefreshDatabase;

    private const NEW_PASSWORD = 'Str0ng-Passw0rd!x';

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    private function actor(string $role = 'super_administrator'): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function student(): User
    {
        $user = User::factory()->create(['password' => Hash::make('old-password-123')]);
        $user->assignRole('student');

        return $user;
    }

    public function test_admin_can_set_a_password_when_editing_a_user(): void
    {
        $student = $this->student();

        Livewire::actingAs($this->actor())
            ->test(Users::class)
            ->call('startEdit', $student->id)
            ->set('editingPassword', self::NEW_PASSWORD)
            ->set('editingPassword_confirmation', self::NEW_PASSWORD)
            ->call('confirmEdit')
            ->assertHasNoErrors();

        $this->assertTrue(Hash::check(self::NEW_PASSWORD, $student->fresh()->password));
        $this->assertTrue(AuditLog::where('action', 'user.password_changed')->exists());
    }

    public function test_leaving_the_password_blank_keeps_the_current_one(): void
    {
        $student = $this->student();

        Livewire::actingAs($this->actor())
            ->test(Users::class)
            ->call('startEdit', $student->id)
            ->set('editingName', 'Renamed Student')
            ->call('confirmEdit')
            ->assertHasNoErrors();

        $fresh = $student->fresh();
        $this->assertSame('Renamed Student', $fresh->name);
        $this->assertTrue(Hash::check('old-password-123', $fresh->password));
    }

    public function test_mismatched_or_weak_passwords_are_rejected(): void
    {
        $student = $this->student();

        Livewire::actingAs($this->actor())
            ->test(Users::class)
            ->call('startEdit', $student->id)
            ->set('editingPassword', self::NEW_PASSWORD)
            ->set('editingPassword_confirmation', 'something-else')
            ->call('confirmEdit')
            ->assertHasErrors(['editingPassword']);

        Livewire::actingAs($this->actor())
            ->test(Users::class)
            ->call('startEdit', $student->id)
            ->set('editingPassword', 'short')
            ->set('editingPassword_confirmation', 'short')
            ->call('confirmEdit')
            ->assertHasErrors(['editingPassword']);

        $this->assertTrue(Hash::check('old-password-123', $student->fresh()->password));
    }

    public function test_changing_a_password_signs_the_user_out_everywhere(): void
    {
        config(['session.driver' => 'database']);
        $student = $this->student();

        DB::table('sessions')->insert([
            'id' => 'abc123', 'user_id' => $student->id, 'ip_address' => '127.0.0.1',
            'user_agent' => 'test', 'payload' => '', 'last_activity' => time(),
        ]);

        Livewire::actingAs($this->actor())
            ->test(Users::class)
            ->call('startEdit', $student->id)
            ->set('editingPassword', self::NEW_PASSWORD)
            ->set('editingPassword_confirmation', self::NEW_PASSWORD)
            ->call('confirmEdit');

        $this->assertSame(0, DB::table('sessions')->where('user_id', $student->id)->count());
    }

    public function test_creating_a_user_with_a_password_skips_the_reset_email(): void
    {
        Notification::fake();

        Livewire::actingAs($this->actor())
            ->test(Users::class)
            ->set('name', 'New Person')
            ->set('email', 'new.person@example.com')
            ->set('role', 'student')
            ->set('newPassword', self::NEW_PASSWORD)
            ->set('newPassword_confirmation', self::NEW_PASSWORD)
            ->call('create')
            ->assertHasNoErrors();

        $created = User::where('email', 'new.person@example.com')->firstOrFail();
        $this->assertTrue(Hash::check(self::NEW_PASSWORD, $created->password));
        Notification::assertNothingSent();
    }

    public function test_creating_a_user_without_a_password_still_emails_a_reset_link(): void
    {
        Notification::fake();

        Livewire::actingAs($this->actor())
            ->test(Users::class)
            ->set('name', 'Emailed Person')
            ->set('email', 'emailed.person@example.com')
            ->set('role', 'student')
            ->call('create')
            ->assertHasNoErrors();

        Notification::assertSentTo(User::where('email', 'emailed.person@example.com')->first(), ResetPassword::class);
    }

    public function test_a_regular_admin_cannot_change_a_super_admins_password(): void
    {
        $superAdmin = $this->actor('super_administrator');

        Livewire::actingAs($this->actor('administrator'))
            ->test(Users::class)
            ->call('startEdit', $superAdmin->id)
            ->assertForbidden();
    }
}
