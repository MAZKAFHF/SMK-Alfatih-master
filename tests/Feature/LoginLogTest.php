<?php

namespace Tests\Feature;

use App\Models\LoginLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginLogTest extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(): User
    {
        return User::factory()->create([
            'is_admin' => true,
            'is_superadmin' => true,
        ]);
    }

    private function admin(): User
    {
        return User::factory()->create([
            'is_admin' => true,
            'is_superadmin' => false,
        ]);
    }

    public function test_login_records_a_login_log(): void
    {
        $user = $this->superAdmin();

        $this->post(route('admin.login.attempt'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertDatabaseHas('login_logs', [
            'user_id' => $user->id,
            'event' => LoginLog::EVENT_LOGIN,
            'ip_address' => '127.0.0.1',
        ]);
    }

    public function test_logout_records_a_logout_log(): void
    {
        $user = $this->superAdmin();

        $this->actingAs($user)
            ->post(route('admin.logout'));

        $this->assertDatabaseHas('login_logs', [
            'user_id' => $user->id,
            'event' => LoginLog::EVENT_LOGOUT,
        ]);
    }

    public function test_superadmin_can_view_login_logs(): void
    {
        $user = $this->superAdmin();
        LoginLog::create([
            'user_id' => $user->id,
            'event' => LoginLog::EVENT_LOGIN,
            'ip_address' => '127.0.0.1',
            'created_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('admin.login-logs.index'))
            ->assertOk()
            ->assertSee($user->name)
            ->assertSee('127.0.0.1');
    }

    public function test_regular_admin_cannot_view_login_logs(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->get(route('admin.login-logs.index'))
            ->assertForbidden();
    }

    public function test_guest_is_redirected_when_accessing_login_logs(): void
    {
        $this->get(route('admin.login-logs.index'))
            ->assertRedirect(route('admin.login'));
    }
}
