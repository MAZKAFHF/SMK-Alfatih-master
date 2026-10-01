<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_production_seed_does_not_require_a_dev_only_factory(): void
    {
        config(['app.initial_admin_password' => 'Initial-Admin-Password-2026!']);

        $this->seed(DatabaseSeeder::class);

        $admin = User::query()->where('email', 'admin@smkalfatih.sch.id')->firstOrFail();

        $this->assertTrue($admin->is_admin);
        $this->assertTrue($admin->is_superadmin);
        $this->assertTrue($admin->is_active);
        $this->assertFalse($admin->is_applicant);
        $this->assertNotNull($admin->email_verified_at);
        $this->assertTrue(Hash::check('Initial-Admin-Password-2026!', $admin->password));
    }

    public function test_reseeding_keeps_the_existing_admin_password(): void
    {
        config(['app.initial_admin_password' => 'First-Password-2026!']);
        $this->seed(DatabaseSeeder::class);

        config(['app.initial_admin_password' => 'Replacement-Password-2026!']);
        $this->seed(DatabaseSeeder::class);

        $admin = User::query()->where('email', 'admin@smkalfatih.sch.id')->firstOrFail();

        $this->assertTrue(Hash::check('First-Password-2026!', $admin->password));
        $this->assertFalse(Hash::check('Replacement-Password-2026!', $admin->password));
        $this->assertDatabaseCount('users', 1);
    }
}
