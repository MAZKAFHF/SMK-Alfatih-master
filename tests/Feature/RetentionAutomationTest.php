<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\AuditLog;
use App\Models\LoginLog;
use App\Models\PpdbPeriod;
use App\Models\PPDBRegistration;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RetentionAutomationTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();
        parent::tearDown();
    }

    public function test_database_log_retention_supports_dry_run_and_pruning(): void
    {
        CarbonImmutable::setTestNow('2026-09-28 12:00:00 UTC');
        config()->set('retention.logs.enabled', true);
        $user = User::factory()->create();
        $oldAudit = AuditLog::create(['action' => 'old', 'actor_type' => 'system', 'created_at' => now()->subDays(4)]);
        $recentAudit = AuditLog::create(['action' => 'recent', 'actor_type' => 'system', 'created_at' => now()->subHours(2)]);
        $oldLogin = LoginLog::create(['user_id' => $user->id, 'event' => 'login', 'created_at' => now()->subDays(4)]);

        $this->artisan('app:retention:logs', ['--dry-run' => true])->assertExitCode(0);
        $this->assertDatabaseHas('audit_logs', ['id' => $oldAudit->id]);

        $this->artisan('app:retention:logs')->assertExitCode(0);
        $this->assertDatabaseMissing('audit_logs', ['id' => $oldAudit->id]);
        $this->assertDatabaseMissing('login_logs', ['id' => $oldLogin->id]);
        $this->assertDatabaseHas('audit_logs', ['id' => $recentAudit->id]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'database_logs_pruned', 'actor_type' => 'system']);
    }

    public function test_trash_purge_only_removes_safe_expired_items(): void
    {
        CarbonImmutable::setTestNow('2026-09-28 12:00:00 UTC');
        config()->set('retention.trash.enabled', true);
        $expired = Announcement::factory()->create();
        $recent = Announcement::factory()->create();
        $expired->delete();
        $recent->delete();
        DB::table('announcements')->where('id', $expired->id)->update(['deleted_at' => now()->subDays(8)]);
        DB::table('announcements')->where('id', $recent->id)->update(['deleted_at' => now()->subDays(2)]);

        $this->artisan('app:trash:purge', ['--dry-run' => true])->assertExitCode(0);
        $this->assertNotNull(Announcement::withTrashed()->find($expired->id));

        $this->artisan('app:trash:purge')->assertExitCode(0);
        $this->assertNull(Announcement::withTrashed()->find($expired->id));
        $this->assertNotNull(Announcement::withTrashed()->find($recent->id));
        $this->assertDatabaseHas('audit_logs', ['action' => 'trash_auto_purged', 'actor_type' => 'system']);
    }

    public function test_applicant_retirement_preserves_application_and_revokes_access(): void
    {
        CarbonImmutable::setTestNow('2026-09-28 12:00:00 UTC');
        config()->set('retention.applicants.enabled', true);
        $user = User::factory()->create(['is_applicant' => true, 'is_admin' => false, 'is_superadmin' => false]);
        $period = PpdbPeriod::create([
            'academic_year' => '2030/2031',
            'status' => 'archived',
            'is_archived' => true,
            'is_active' => false,
            'results_released_at' => now()->subDays(30),
            'operational_completed_at' => now()->subDays(20),
            'account_retention_until' => now()->subDay(),
        ]);
        $application = PPDBRegistration::factory()->create([
            'applicant_account_id' => $user->id,
            'period_id' => $period->id,
            'application_status' => 'passed',
        ]);
        DB::table('sessions')->insert([
            'id' => 'retention-test-session', 'user_id' => $user->id, 'ip_address' => null,
            'user_agent' => null, 'payload' => 'test', 'last_activity' => now()->timestamp,
        ]);
        DB::table('password_reset_tokens')->insert([
            'email' => $user->email, 'token' => 'test-token', 'created_at' => now(),
        ]);

        $this->artisan('app:applicants:retire', ['--dry-run' => true])->assertExitCode(0);
        $this->assertDatabaseHas('users', ['id' => $user->id]);

        $this->artisan('app:applicants:retire')->assertExitCode(0);
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertDatabaseHas('ppdb_registrations', ['id' => $application->id, 'applicant_account_id' => null]);
        $this->assertDatabaseMissing('sessions', ['id' => 'retention-test-session']);
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $user->email]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'applicant_account_retired', 'actor_type' => 'system']);
    }

    public function test_applicant_with_unfinished_period_is_blocked(): void
    {
        CarbonImmutable::setTestNow('2026-09-28 12:00:00 UTC');
        config()->set('retention.applicants.enabled', true);
        $user = User::factory()->create(['is_applicant' => true]);
        $period = PpdbPeriod::create(['academic_year' => '2031/2032', 'status' => 'open', 'is_active' => true]);
        PPDBRegistration::factory()->create([
            'applicant_account_id' => $user->id,
            'period_id' => $period->id,
            'application_status' => 'submitted',
        ]);

        $this->artisan('app:applicants:retire')->assertExitCode(0);
        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }
}
