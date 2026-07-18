<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * The platform's authenticated roles (section 4 of the development plan).
     * "Guest" is intentionally excluded: it is the unauthenticated state, not a stored role.
     */
    public const ROLES = [
        'student',
        'lecturer',
        'course_reviewer',
        'support_officer',
        'finance_officer',
        'administrator',
        'super_administrator',
    ];

    /**
     * Permissions needed by the features built so far (auth, users, settings).
     * More are added as later phases introduce courses, payments, etc.
     */
    public const PERMISSIONS = [
        'access admin panel',
        'manage users',
        'manage roles',
        'manage settings',
    ];

    public function run(): void
    {
        foreach (self::PERMISSIONS as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        foreach (self::ROLES as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        Role::findByName('administrator')->givePermissionTo([
            'access admin panel',
            'manage users',
            'manage settings',
        ]);

        Role::findByName('super_administrator')->givePermissionTo(self::PERMISSIONS);
    }
}
