<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            SettingSeeder::class,
            StaticContentSeeder::class,
        ]);

        if (app()->environment('local', 'testing')) {
            $this->call([
                DemoUserSeeder::class,
                CourseDemoSeeder::class,
                CurriculumDemoSeeder::class,
                Phase5DemoSeeder::class,
                Phase6DemoSeeder::class,
                NewsDemoSeeder::class,
            ]);
        }
    }
}
