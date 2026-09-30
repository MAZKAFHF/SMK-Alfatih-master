<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\LoginLog;
use App\Models\PPDBRegistration;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_admin_must_create_four_digit_code_before_dashboard(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
            'admin_code' => null,
            'admin_code_set_at' => null,
        ]);

        $this->post(route('admin.login.attempt'), [
            'email' => $admin->email,
            'password' => 'password',
        ])->assertRedirect(route('admin.code.create'));

        $this->get(route('admin.dashboard'))->assertRedirect(route('admin.code.create'));

        $this->post(route('admin.code.store'), [
            'admin_code' => '4826',
            'admin_code_confirmation' => '4826',
        ])->assertRedirect(route('admin.dashboard'));

        $admin->refresh();
        $this->assertTrue(Hash::check('4826', $admin->admin_code));
        $this->assertNotSame('4826', $admin->admin_code);
        $this->assertNotNull($admin->admin_code_set_at);
    }

    public function test_registration_delete_requires_matching_admin_code(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $program = Program::factory()->create();
        $registration = PPDBRegistration::factory()->create(['program_id' => $program->id]);

        $this->actingAs($admin)
            ->delete(route('admin.registrations.destroy', $registration), ['admin_code' => '0000'])
            ->assertSessionHasErrors('admin_code');
        $this->assertNotSoftDeleted($registration);

        $this->actingAs($admin)
            ->delete(route('admin.registrations.destroy', $registration), ['admin_code' => '1234'])
            ->assertRedirect(route('admin.registrations.index'));
        $this->assertSoftDeleted($registration);
    }

    public function test_superadmin_can_delete_another_account_with_admin_code(): void
    {
        $superadmin = User::factory()->create(['is_admin' => true, 'is_superadmin' => true]);
        $target = User::factory()->create(['is_admin' => true]);

        $this->actingAs($superadmin)
            ->delete(route('admin.users.destroy', $target), ['admin_code' => '1234'])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('users', ['id' => $target->id]);
    }

    public function test_malicious_failed_login_is_recorded_without_storing_password(): void
    {
        $payload = "' OR 1=1 --";
        $this->withHeader('User-Agent', 'Security-Test-Agent/1.0')
            ->post(route('admin.login.attempt'), [
                'email' => $payload,
                'password' => 'do-not-store-this-password',
            ])->assertSessionHasErrors('email');

        $log = LoginLog::latest('id')->firstOrFail();
        $this->assertSame(LoginLog::EVENT_LOGIN_FAILED, $log->event);
        $this->assertSame($payload, $log->attempted_email);
        $this->assertSame('127.0.0.1', $log->ip_address);
        $this->assertSame('Security-Test-Agent/1.0', $log->user_agent);
        $this->assertNull($log->user_id);
        $this->assertStringNotContainsString('do-not-store-this-password', json_encode($log->toArray()));
    }

    public function test_superadmin_can_clear_login_and_audit_logs_with_code(): void
    {
        $superadmin = User::factory()->create(['is_admin' => true, 'is_superadmin' => true]);
        LoginLog::create(['user_id' => $superadmin->id, 'event' => 'login', 'created_at' => now()]);
        AuditLog::record($superadmin, 'old_action');

        $this->actingAs($superadmin)
            ->delete(route('admin.login-logs.clear'), ['admin_code' => '1234'])
            ->assertRedirect(route('admin.login-logs.index'));
        $this->assertDatabaseCount('login_logs', 0);

        $this->actingAs($superadmin)
            ->delete(route('admin.audit-logs.clear'), ['admin_code' => '1234'])
            ->assertRedirect(route('admin.audit-logs.index'));
        $this->assertDatabaseCount('audit_logs', 1);
        $this->assertDatabaseHas('audit_logs', ['action' => 'audit_logs_cleared']);
    }

    public function test_failed_portal_login_is_also_visible_in_security_log(): void
    {
        $this->withHeader('User-Agent', 'Portal-Test-Agent/1.0')
            ->post(route('portal.login.store'), [
                'email' => "x' OR '1'='1",
                'password' => 'wrong-password',
            ])->assertSessionHasErrors('email');

        $this->assertDatabaseHas('login_logs', [
            'event' => LoginLog::EVENT_LOGIN_FAILED,
            'channel' => 'portal',
            'attempted_email' => "x' OR '1'='1",
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Portal-Test-Agent/1.0',
        ]);
    }
}
