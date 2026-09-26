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
     * Permissions needed by the features built so far.
     * More are added as later phases introduce payments, assessments, etc.
     */
    public const PERMISSIONS = [
        'access admin panel',
        'manage users',
        'manage roles',
        'manage settings',
        'manage lecturer applications',
        'manage categories',
        'manage own courses',
        'review courses',
        'publish courses',
        'manage enrolments',
        'manage finances',
        'manage certificates',
        'manage lecturer commissions',
        'manage support tickets',
        'view reports',
        'manage content',
        'view audit logs',
    ];

    public function run(): void
    {
        foreach (self::PERMISSIONS as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        foreach (self::ROLES as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        Role::findByName('lecturer')->givePermissionTo([
            'manage own courses',
        ]);

        Role::findByName('course_reviewer')->givePermissionTo([
            'review courses',
        ]);

        Role::findByName('finance_officer')->givePermissionTo([
            'access admin panel',
            'manage finances',
            'manage lecturer commissions',
            'view reports',
        ]);

        Role::findByName('support_officer')->givePermissionTo([
            'access admin panel',
            'manage support tickets',
        ]);

        Role::findByName('administrator')->givePermissionTo([
            'access admin panel',
            'manage users',
            'manage roles',
            'manage settings',
            'manage lecturer applications',
            'manage categories',
            'review courses',
            'publish courses',
            'manage enrolments',
            'manage finances',
            'manage certificates',
            'manage lecturer commissions',
            'manage support tickets',
            'view reports',
            'manage content',
            'view audit logs',
        ]);

        Role::findByName('super_administrator')->givePermissionTo(self::PERMISSIONS);
    }
}
