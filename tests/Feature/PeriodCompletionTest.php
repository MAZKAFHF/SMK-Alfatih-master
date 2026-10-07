<?php

namespace Tests\Feature;

use App\Models\PpdbPeriod;
use App\Models\PPDBRegistration;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * COMPLETED vs CLOSED: menutup pendaftaran bukan akhir proses.
 * Hanya konfirmasi SELESAI eksplisit yang mengunci operasional dan
 * memicu pembersihan akun pemohon otomatis oleh SYSTEM.
 */
class PeriodCompletionTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();
        parent::tearDown();
    }

    private function superadmin(): User
    {
        return User::factory()->create(['is_admin' => true, 'is_superadmin' => true, 'is_active' => true, 'password' => Hash::make('password123')]);
    }

    private function applicant(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'is_admin' => false, 'is_superadmin' => false, 'is_applicant' => true,
            'is_active' => true, 'email_verified_at' => now(), 'password' => Hash::make('password123'),
        ], $overrides));
    }

    private function closedPeriod(string $year): PpdbPeriod
    {
        return PpdbPeriod::create([
            'academic_year' => $year, 'status' => 'closed', 'is_active' => false,
            'closes_at' => now()->subDay(), 'closed_at' => now()->subDay(),
        ]);
    }

    private function finishApp(User $user, PpdbPeriod $period, string $status = 'passed'): PPDBRegistration
    {
        $app = PPDBRegistration::factory()->create([
            'applicant_account_id' => $user->id, 'period_id' => $period->id, 'application_status' => $status,
        ]);
        if (in_array($status, ['passed', 'not_passed'], true)) {
            \DB::table('application_decisions')->insert([
                'application_id' => $app->id,
                'result' => $status === 'passed' ? 'passed' : 'not_passed',
                'released_at' => now()->subDays(5), 'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        return $app;
    }

    public function test_closed_account_login_still_works_after_cleanup_run(): void
    {
        CarbonImmutable::setTestNow('2026-09-28 12:00:00 UTC');
        config()->set('retention.applicants.enabled', true);
        $admin = $this->superadmin();
        $user = $this->applicant();
        $period = $this->closedPeriod('2090/2091');
        PPDBRegistration::factory()->create([
            'applicant_account_id' => $user->id, 'period_id' => $period->id, 'application_status' => 'scheduled',
        ]);

        $this->artisan('app:applicants:retire')->assertExitCode(0);
        $this->assertDatabaseHas('users', ['id' => $user->id]);
        $this->post(route('portal.login.store'), ['email' => $user->email, 'password' => 'password123'])->assertRedirect();
        $this->assertAuthenticatedAs($user);

        // Menandai selesai dengan pekerjaan tertunda DITOLAK.
        $this->actingAs($admin)->post(route('admin.periods.complete', $period), ['confirm' => 1])
            ->assertSessionHasErrors(['status']);
        $this->assertEquals('closed', $period->fresh()->status);
    }

    public function test_complete_requires_explicit_confirmation(): void
    {
        $admin = $this->superadmin();
        $period = $this->closedPeriod('2091/2092');

        $this->actingAs($admin)->post(route('admin.periods.complete', $period), [])
            ->assertSessionHasErrors(['confirm']);
        $this->actingAs($admin)->post(route('admin.periods.complete', $period), ['confirm' => 0])
            ->assertSessionHasErrors(['confirm']);
        $this->assertEquals('closed', $period->fresh()->status);
    }

    public function test_complete_blocked_by_unreleased_result(): void
    {
        $admin = $this->superadmin();
        $user = $this->applicant();
        $period = $this->closedPeriod('2092/2093');
        $app = PPDBRegistration::factory()->create([
            'applicant_account_id' => $user->id, 'period_id' => $period->id, 'application_status' => 'passed',
        ]);
        \DB::table('application_decisions')->insert([
            'application_id' => $app->id, 'result' => 'passed',
            'released_at' => null, 'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->actingAs($admin)->post(route('admin.periods.complete', $period), ['confirm' => 1])
            ->assertSessionHasErrors(['status']);
        $this->assertEquals('closed', $period->fresh()->status);
    }

    public function test_drafts_do_not_block_completion(): void
    {
        CarbonImmutable::setTestNow('2026-09-28 12:00:00 UTC');
        $admin = $this->superadmin();
        $user = $this->applicant();
        $period = $this->closedPeriod('2093/2094');
        PPDBRegistration::factory()->create([
            'applicant_account_id' => $user->id, 'period_id' => $period->id, 'application_status' => 'draft',
        ]);

        $this->actingAs($admin)->post(route('admin.periods.complete', $period), ['confirm' => 1])
            ->assertSessionHasNoErrors()->assertRedirect();
        $this->assertEquals('completed', $period->fresh()->status);
    }

    public function test_completed_triggers_cleanup_login_fails_history_intact(): void
    {
        CarbonImmutable::setTestNow('2026-09-28 12:00:00 UTC');
        config()->set('retention.applicants.enabled', true);
        $admin = $this->superadmin();
        $user = $this->applicant();
        $period = $this->closedPeriod('2094/2095');
        // Retention lampau agar auto-cleanup langsung eligible.
        $period->update(['account_retention_until' => now()->subDay()]);
        $app = $this->finishApp($user, $period, 'passed');
        $decisionId = \DB::table('application_decisions')->where('application_id', $app->id)->value('id');
        $appCount = $period->applications()->count();

        $this->actingAs($admin)->post(route('admin.periods.complete', $period), ['confirm' => 1])
            ->assertSessionHasNoErrors()->assertRedirect();

        $period = $period->fresh();
        $this->assertEquals('completed', $period->status);
        $this->assertNotNull($period->operational_completed_at);
        $this->assertNotNull($period->results_released_at);
        $this->assertTrue($period->isLockedForOperations());

        // Akun eligible otomatis dibersihkan oleh SYSTEM.
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'applicant_account_retired', 'actor_type' => 'system']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'period_completed']);

        // Login gagal profesional.
        auth()->logout();
        $response = $this->post(route('portal.login.store'), ['email' => $user->email, 'password' => 'password123']);
        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();

        // Riwayat utuh: aplikasi terlepas tapi tercatat, keputusan ada, hitungan sama.
        $this->assertDatabaseHas('ppdb_registrations', ['id' => $app->id, 'applicant_account_id' => null]);
        $this->assertDatabaseHas('application_decisions', ['id' => $decisionId]);
        $this->assertEquals($appCount, $period->applications()->count());
    }

    public function test_operational_mutation_blocked_after_completed(): void
    {
        $admin = $this->superadmin();
        $user = $this->applicant();
        // Periode completed langsung (simulasi warisan yang sudah selesai).
        $period = PpdbPeriod::create([
            'academic_year' => '2095/2096', 'status' => 'completed', 'is_active' => false,
            'results_released_at' => now()->subDays(30),
            'operational_completed_at' => now()->subDays(20),
            'account_retention_until' => now()->addDays(30),
        ]);
        $app = PPDBRegistration::factory()->create([
            'applicant_account_id' => $user->id, 'period_id' => $period->id, 'application_status' => 'submitted',
        ]);

        $this->actingAs($admin)->post(route('admin.registrations.verify', $app))
            ->assertSessionHas('error');
        $this->assertEquals('submitted', $app->fresh()->application_status->value);
    }

    public function test_multi_period_active_application_survives_completion(): void
    {
        CarbonImmutable::setTestNow('2026-09-28 12:00:00 UTC');
        config()->set('retention.applicants.enabled', true);
        $admin = $this->superadmin();
        $parent = $this->applicant();
        $oldPeriod = $this->closedPeriod('2096/2097');
        $oldPeriod->update(['account_retention_until' => now()->subDay()]);
        $this->finishApp($parent, $oldPeriod, 'passed');
        $activePeriod = PpdbPeriod::create(['academic_year' => '2097/2098', 'status' => 'open', 'is_active' => true]);
        PPDBRegistration::factory()->create([
            'applicant_account_id' => $parent->id, 'period_id' => $activePeriod->id, 'application_status' => 'submitted',
        ]);

        $this->actingAs($admin)->post(route('admin.periods.complete', $oldPeriod), ['confirm' => 1])
            ->assertSessionHasNoErrors();
        $this->assertEquals('completed', $oldPeriod->fresh()->status);
        $this->assertDatabaseHas('users', ['id' => $parent->id]);

        $this->post(route('portal.login.store'), ['email' => $parent->email, 'password' => 'password123'])->assertRedirect();
        $this->assertAuthenticatedAs($parent);
    }

    public function test_admin_account_never_deleted_by_completion_cleanup(): void
    {
        CarbonImmutable::setTestNow('2026-09-28 12:00:00 UTC');
        config()->set('retention.applicants.enabled', true);
        $admin = $this->superadmin();
        $staff = User::factory()->create([
            'is_admin' => true, 'is_superadmin' => false, 'is_applicant' => true,
            'is_active' => true, 'email_verified_at' => now(),
        ]);
        $period = $this->closedPeriod('2098/2099');
        $period->update(['account_retention_until' => now()->subDay()]);
        $this->finishApp($staff, $period, 'passed');

        $this->actingAs($admin)->post(route('admin.periods.complete', $period), ['confirm' => 1])
            ->assertSessionHasNoErrors();
        $this->assertDatabaseHas('users', ['id' => $staff->id]);
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_update_ignores_manual_lifecycle_dates(): void
    {
        $admin = $this->superadmin();
        $period = $this->closedPeriod('2101/2102');

        $this->actingAs($admin)->put(route('admin.periods.update', $period), [
            'academic_year' => '2101/2102',
            'results_released_at' => '2026-01-01T10:00',
            'operational_completed_at' => '2026-01-02T10:00',
            'account_retention_until' => '2026-06-01T10:00',
        ])->assertSessionHasNoErrors()->assertRedirect();

        $period = $period->fresh();
        $this->assertNull($period->results_released_at);
        $this->assertNull($period->operational_completed_at);
        $this->assertNull($period->account_retention_until);
    }

    public function test_release_stamps_period_results_automatically(): void
    {
        $admin = $this->superadmin();
        $user = $this->applicant();
        $period = $this->closedPeriod('2102/2103');
        $this->assertNull($period->fresh()->results_released_at);
        $app = PPDBRegistration::factory()->create([
            'applicant_account_id' => $user->id, 'period_id' => $period->id, 'application_status' => 'passed',
        ]);
        \DB::table('application_decisions')->insert([
            'application_id' => $app->id, 'result' => 'passed',
            'released_at' => null, 'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->actingAs($admin)->post(route('admin.registrations.release', $app))->assertRedirect();
        $this->assertNotNull($period->fresh()->results_released_at);
    }

    public function test_complete_persists_system_calculated_retention(): void
    {
        CarbonImmutable::setTestNow('2026-09-28 12:00:00 UTC');
        $admin = $this->superadmin();
        $period = $this->closedPeriod('2103/2104');
        $this->finishApp($this->applicant(), $period, 'passed');

        $this->actingAs($admin)->post(route('admin.periods.complete', $period), ['confirm' => 1])
            ->assertSessionHasNoErrors();

        $period = $period->fresh();
        $this->assertNotNull($period->account_retention_until);
        $expected = $period->operational_completed_at;
        $this->assertEquals($expected->timestamp, $period->account_retention_until->timestamp);
    }

    public function test_reopen_resets_retirement_baseline_and_recomputes(): void
    {
        CarbonImmutable::setTestNow('2026-09-28 12:00:00 UTC');
        $admin = $this->superadmin();
        PpdbPeriod::where('academic_year', '2026/2027')->update(['status' => 'closed', 'is_active' => false, 'is_open' => false]);
        $period = $this->closedPeriod('2104/2105');
        $period->update([
            'results_released_at' => now()->subDays(2),
            'operational_completed_at' => now()->subDay(),
            'account_retention_until' => now()->subDay(),
        ]);

        $this->actingAs($admin)->post(route('admin.periods.reopen', $period))->assertRedirect();
        $period = $period->fresh();
        $this->assertEquals('open', $period->status);
        // Baseline pensiun basi dibatalkan; fakta pengumuman hasil dipertahankan.
        $this->assertNull($period->operational_completed_at);
        $this->assertNull($period->account_retention_until);
        $this->assertNotNull($period->results_released_at);
        $this->assertFalse($period->isLockedForOperations());

    }

    public function test_completed_period_is_terminal_and_cannot_be_reopened(): void
    {
        $admin = $this->superadmin();
        // Periode seed bawaan migrasi (2026/2027, open) harus dinetralkan
        // agar tidak konflik dengan aturan satu-periode-berjalan.
        PpdbPeriod::where('academic_year', '2026/2027')->update(['status' => 'closed', 'is_active' => false, 'is_open' => false]);
        $period = $this->closedPeriod('2099/2100');
        $applicant = $this->applicant();
        $this->finishApp($applicant, $period, 'passed');
        // Rilis otomatis? Tidak — keputusan fixture sudah released via finishApp.
        $this->actingAs($admin)->post(route('admin.periods.complete', $period), ['confirm' => 1])
            ->assertSessionHasNoErrors();
        $this->assertEquals('completed', $period->fresh()->status);
        $this->assertDatabaseMissing('users', ['id' => $applicant->id]);

        $this->actingAs($admin)->post(route('admin.periods.reopen', $period))
            ->assertSessionHasErrors(['status']);
        $this->assertEquals('completed', $period->fresh()->status);
    }
}
