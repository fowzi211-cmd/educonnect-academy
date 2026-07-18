<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoUserSeeder extends Seeder
{
    /**
     * Development-only demo accounts (spec section 33). All share this password —
     * it must be rotated/removed before any production deployment.
     */
    public const DEMO_PASSWORD = 'Password123!';

    public function run(): void
    {
        $this->createUser('Super Admin', 'super.admin@educonnect.test', 'super_administrator');
        $this->createUser('Platform Admin', 'admin@educonnect.test', 'administrator');
        $this->createUser('Finance Officer', 'finance@educonnect.test', 'finance_officer');
        $this->createUser('Support Officer', 'support@educonnect.test', 'support_officer');
        $this->createUser('Academic Reviewer', 'reviewer@educonnect.test', 'course_reviewer');

        foreach (range(1, 3) as $i) {
            $this->createUser("Demo Lecturer {$i}", "lecturer{$i}@educonnect.test", 'lecturer');
        }

        foreach (range(1, 10) as $i) {
            $this->createUser("Demo Student {$i}", "student{$i}@educonnect.test", 'student');
        }
    }

    protected function createUser(string $name, string $email, string $role): void
    {
        $user = User::query()->firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make(self::DEMO_PASSWORD),
                'email_verified_at' => now(),
            ]
        );

        if (! $user->hasRole($role)) {
            $user->assignRole($role);
        }

        if (! $user->profile) {
            $user->profile()->create([
                'country' => 'Saudi Arabia',
                'preferred_language' => 'en',
            ]);
        }
    }
}
