<?php

namespace Tests\Feature;

use Database\Seeders\RoleSeeder;
use Database\Seeders\SettingSeeder;
use Database\Seeders\StaticContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $this->seed([RoleSeeder::class, SettingSeeder::class, StaticContentSeeder::class]);

        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
