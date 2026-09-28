<?php

namespace Tests\Feature;

use App\Models\PpdbPeriod;
use App\Models\PPDBRegistration;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ApplicantAccountCleanupTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();
        parent::tearDown();
    }

    private function applicant(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'is_admin' => false, 'is_superadmin' => false, 'is_applicant' => true,
            'is_active' => true, 'password' => Hash::make('password123'),
        ], $overrides));
    }

    private function completedPeriod(array $overrides = []): PpdbPeriod
    {
        return PpdbPeriod::create(array_merge([
            'academic_year' => '2030/'.(2031 + random_int(0, 50)),
            'status' => 'archived', 'is_archived' => true, 'is_active' => false,
            'results_released_at' => now()->subDays(30),
            'operational_completed_at' => now()->subDays(20),
            'account_retention_until' => now()->subDay(),
        ], $overrides));
    }

    public function test_unverified_orphan_older_than_3_days_is_deleted_and_cannot_login(): void
    {
        CarbonImmutable::setTestNow('2026-09-28 12:00:00 UTC');
        config()->set('retention.applicants.enabled', true);
        $user = $this->applicant(['email_verified_at' => null, 'created_at' => now()->subDays(5), 'updated_at' => now()->subDays(5)]);

        $this->artisan('app:applicants:retire', ['--dry-run' => true])->assertExitCode(0);
        $this->assertDatabaseHas('users', ['id' => $user->id]);

        $this->artisan('app:applicants:retire')->assertExitCode(0);
        $this->assertDatabaseMissing('users', ['id' => $user->id]);

        // Login impossible, professional message, no stack.
        $response = $this->post(route('portal.login.store'), ['email' => $user->email, 'password' => 'password123']);
        $response->assertSessionHasErrors(['email']);
        $this->assertStringContainsString('Email atau password salah', session('errors')->first('email'));
        $this->assertGuest();
    }

    public function test_unverified_orphan_with_application_never_uses_orphan_path(): void
    {
        CarbonImmutable::setTestNow('2026-09-28 12:00:00 UTC');
        $user = $this->applicant(['email_verified_at' => null, 'created_at' => now()->subDays(10), 'updated_at' => now()->subDays(10)]);
        $period = PpdbPeriod::create(['academic_year' => '2035/2036', 'status' => 'open', 'is_active' => true]);
        PPDBRegistration::factory()->create([
            'applicant_account_id' => $user->id, 'period_id' => $period->id, 'application_status' => 'submitted',
        ]);

        $evaluation = app(\App\Services\ApplicantAccountLifecycleService::class)->evaluate($user, CarbonImmutable::now('UTC'));
        $this->assertEquals('real_applicant', $evaluation['type']);
        $this->assertFalse($evaluation['eligible']);
    }

    public function test_verified_unused_with_upcoming_period_is_not_deleted(): void
    {
        CarbonImmutable::setTestNow('2026-09-28 12:00:00 UTC');
        config()->set('retention.applicants.enabled', true);
        $user = $this->applicant(['email_verified_at' => now()->subDays(200), 'updated_at' => now()->subDays(200), 'last_activity_at' => now()->subDays(200)]);
        PpdbPeriod::create(['academic_year' => '2032/2033', 'status' => 'upcoming', 'is_active' => true, 'opens_at' => now()->addMonth()]);

        $this->artisan('app:applicants:retire')->assertExitCode(0);
        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }

    public function test_closed_but_not_finished_account_remains_login_capable(): void
    {
        CarbonImmutable::setTestNow('2026-09-28 12:00:00 UTC');
        config()->set('retention.applicants.enabled', true);
        $user = $this->applicant(['email_verified_at' => now()]);
        $period = PpdbPeriod::create(['academic_year' => '2090/2091', 'status' => 'closed', 'is_active' => false, 'closes_at' => now()->subDay()]);
        PPDBRegistration::factory()->create([
            'applicant_account_id' => $user->id, 'period_id' => $period->id, 'application_status' => 'scheduled',
        ]);

        $this->artisan('app:applicants:retire')->assertExitCode(0);
        $this->assertDatabaseHas('users', ['id' => $user->id]);
        $this->post(route('portal.login.store'), ['email' => $user->email, 'password' => 'password123'])->assertRedirect();
        $this->assertAuthenticatedAs($user);
    }

    public function test_finished_retention_expired_account_cannot_login_after_cleanup(): void
    {
        CarbonImmutable::setTestNow('2026-09-28 12:00:00 UTC');
        config()->set('retention.applicants.enabled', true);
        $user = $this->applicant(['email_verified_at' => now()->subDays(100)]);
        $period = $this->completedPeriod();
        $app = PPDBRegistration::factory()->create([
            'applicant_account_id' => $user->id, 'period_id' => $period->id, 'application_status' => 'passed',
        ]);
        \DB::table('application_decisions')->insert([
            'application_id' => $app->id, 'result' => 'passed',
            'released_at' => now()->subDays(10), 'created_at' => now(), 'updated_at' => now(),
        ]);
        $countBefore = PpdbPeriod::find($period->id)->applications()->count();

        $this->artisan('app:applicants:retire')->assertExitCode(0);
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        // History preserved: application row stays, detached.
        $this->assertDatabaseHas('ppdb_registrations', ['id' => $app->id, 'applicant_account_id' => null]);
        $this->assertEquals($countBefore, PpdbPeriod::find($period->id)->applications()->count());

        $response = $this->post(route('portal.login.store'), ['email' => $user->email, 'password' => 'password123']);
        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    public function test_unreleased_result_blocks_cleanup(): void
    {
        CarbonImmutable::setTestNow('2026-09-28 12:00:00 UTC');
        config()->set('retention.applicants.enabled', true);
        $user = $this->applicant(['email_verified_at' => now()->subDays(100)]);
        $period = $this->completedPeriod();
        $app = PPDBRegistration::factory()->create([
            'applicant_account_id' => $user->id, 'period_id' => $period->id, 'application_status' => 'passed',
        ]);
        \DB::table('application_decisions')->insert([
            'application_id' => $app->id, 'result' => 'passed',
            'released_at' => null, 'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->artisan('app:applicants:retire')->assertExitCode(0);
        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }

    public function test_multi_student_parent_with_one_active_is_safe(): void
    {
        CarbonImmutable::setTestNow('2026-09-28 12:00:00 UTC');
        config()->set('retention.applicants.enabled', true);
        $parent = $this->applicant(['email_verified_at' => now()->subDays(100)]);
        $oldPeriod = $this->completedPeriod(['academic_year' => '2092/2093']);
        $oldApp = PPDBRegistration::factory()->create([
            'applicant_account_id' => $parent->id, 'period_id' => $oldPeriod->id, 'application_status' => 'passed',
        ]);
        \DB::table('application_decisions')->insert([
            'application_id' => $oldApp->id, 'result' => 'passed',
            'released_at' => now()->subDays(10), 'created_at' => now(), 'updated_at' => now(),
        ]);
        $activePeriod = PpdbPeriod::create(['academic_year' => '2093/2094', 'status' => 'open', 'is_active' => true]);
        PPDBRegistration::factory()->create([
            'applicant_account_id' => $parent->id, 'period_id' => $activePeriod->id, 'application_status' => 'submitted',
        ]);

        $this->artisan('app:applicants:retire')->assertExitCode(0);
        $this->assertDatabaseHas('users', ['id' => $parent->id]);
    }

    public function test_admin_applicant_never_deleted(): void
    {
        CarbonImmutable::setTestNow('2026-09-28 12:00:00 UTC');
        config()->set('retention.applicants.enabled', true);
        $staff = User::factory()->create([
            'is_admin' => true, 'is_superadmin' => false, 'is_applicant' => true,
            'is_active' => true, 'email_verified_at' => null,
            'created_at' => now()->subDays(30), 'updated_at' => now()->subDays(30),
        ]);

        $this->artisan('app:applicants:retire')->assertExitCode(0);
        $this->assertDatabaseHas('users', ['id' => $staff->id]);
    }

    public function test_dry_run_changes_nothing_and_rerun_is_idempotent(): void
    {
        CarbonImmutable::setTestNow('2026-09-28 12:00:00 UTC');
        config()->set('retention.applicants.enabled', true);
        $user = $this->applicant(['email_verified_at' => null, 'created_at' => now()->subDays(5), 'updated_at' => now()->subDays(5)]);

        $this->artisan('app:applicants:retire', ['--dry-run' => true])->assertExitCode(0);
        $this->assertDatabaseHas('users', ['id' => $user->id]);

        $this->artisan('app:applicants:retire')->assertExitCode(0);
        $this->assertDatabaseMissing('users', ['id' => $user->id]);

        $second = $this->artisan('app:applicants:retire')->assertExitCode(0);
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_documents_are_never_deleted_by_account_cleanup(): void
    {
        CarbonImmutable::setTestNow('2026-09-28 12:00:00 UTC');
        config()->set('retention.applicants.enabled', true);
        \Storage::fake('ppdb_private');
        $user = $this->applicant(['email_verified_at' => now()->subDays(100)]);
        $period = $this->completedPeriod();
        $program = \App\Models\Program::factory()->create(['status' => 'active']);
        $app = PPDBRegistration::create(array_merge(
            \Tests\Feature\PpdbFinalSubmissionTest::completeData($program->id),
            ['applicant_account_id' => $user->id, 'period_id' => $period->id, 'source' => 'applicant', 'application_status' => 'passed', 'status' => 'accepted']
        ));
        \App\Services\DocumentService::ensurePlaceholders($app);
        $docCount = $app->documents()->count();
        $this->assertGreaterThan(0, $docCount);

        \DB::table('application_decisions')->insert([
            'application_id' => $app->id, 'result' => 'passed',
            'released_at' => now()->subDays(10), 'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->artisan('app:applicants:retire')->assertExitCode(0);
        $this->assertEquals($docCount, \App\Models\ApplicationDocument::where('application_id', $app->id)->count());
    }
}
