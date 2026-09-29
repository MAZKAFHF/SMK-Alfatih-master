<?php

namespace Tests\Feature;

use App\Models\PpdbPeriod;
use App\Models\PPDBRegistration;
use App\Models\User;
use App\Services\PpdbPeriodService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Alur final: CLOSED (login jalan, kata "Ditutup") vs COMPLETED
 * (kata "Telah Selesai", CTA portal hilang, akun langsung dibersihkan,
 * periode berikutnya mulai segar, email lama bisa daftar lagi).
 */
class CompletedPeriodFlowTest extends TestCase
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

    private function applicant(string $email, array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'email' => $email, 'is_admin' => false, 'is_superadmin' => false,
            'is_applicant' => true, 'is_active' => true, 'email_verified_at' => now(),
            'password' => Hash::make('password123'),
        ], $overrides));
    }

    private function closeSeed(): void
    {
        PpdbPeriod::where('academic_year', '2026/2027')->update(['status' => 'closed', 'is_active' => false, 'is_open' => false]);
    }

    private function closedPeriod(string $year): PpdbPeriod
    {
        return PpdbPeriod::create([
            'academic_year' => $year, 'status' => 'closed', 'is_active' => false,
            'closes_at' => now()->subDay(), 'closed_at' => now()->subDay(),
        ]);
    }

    private function releasedApp(User $user, PpdbPeriod $period, string $status = 'passed'): PPDBRegistration
    {
        $app = PPDBRegistration::factory()->create([
            'applicant_account_id' => $user->id, 'period_id' => $period->id, 'application_status' => $status,
        ]);
        \DB::table('application_decisions')->insert([
            'application_id' => $app->id, 'result' => $status === 'passed' ? 'passed' : 'not_passed',
            'released_at' => now()->subDays(5), 'created_at' => now(), 'updated_at' => now(),
        ]);

        return $app;
    }

    public function test_1_closed_shows_ditutup_and_portal_entry_visible(): void
    {
        $this->closeSeed();

        $page = $this->get(route('ppdb.index'))->assertOk();
        $page->assertSee('Telah Ditutup', false);
        $page->assertSee('Masuk Portal', false);
        $page->assertSee('Buat Akun Portal', false);
        $page->assertDontSee('Telah Selesai', false);
    }

    public function test_2_completed_shows_telah_selesai_and_hides_all_ctas(): void
    {
        CarbonImmutable::setTestNow('2026-09-28 12:00:00 UTC');
        $this->closeSeed();
        $admin = $this->superadmin();
        $period = $this->closedPeriod('2110/2111');
        $this->releasedApp($this->applicant('selesai@example.com'), $period);

        $this->actingAs($admin)->post(route('admin.periods.complete', $period), ['confirm' => 1])
            ->assertSessionHasNoErrors();

        $page = $this->get(route('ppdb.index'))->assertOk();
        $page->assertSee('Telah Selesai', false);
        $page->assertSee('Seluruh rangkaian PPDB periode ini telah selesai.', false);
        $page->assertDontSee('Telah Ditutup', false);
        $page->assertDontSee('Buat Akun Portal', false);
        $page->assertDontSee('Buat Akun &amp; Daftar', false);
        $page->assertDontSee('Buat Akun & Daftar', false);
        $page->assertDontSee('Masuk Portal', false);
        $page->assertDontSee('Daftar Sekarang', false);

        $home = $this->get(route('home'))->assertOk();
        $home->assertSee('Telah Selesai', false);
        $home->assertDontSee('Masuk Portal', false);

        // Entry portal langsung juga ditolak saat SELESAI.
        $this->get(route('portal.register'))->assertRedirect(route('ppdb.index'));
        $this->get(route('portal.login'))->assertRedirect(route('ppdb.index'));
        $this->post(route('portal.register.store'), [
            'name' => 'Baru', 'email' => 'baru@example.com',
            'password' => 'password123', 'password_confirmation' => 'password123',
        ])->assertSessionHasErrors(['email']);
    }

    public function test_3_new_upcoming_period_restores_portal_entry(): void
    {
        CarbonImmutable::setTestNow('2026-09-28 12:00:00 UTC');
        $this->closeSeed();
        $admin = $this->superadmin();
        $period = $this->closedPeriod('2111/2112');
        $this->releasedApp($this->applicant('s2@example.com'), $period);
        $this->actingAs($admin)->post(route('admin.periods.complete', $period), ['confirm' => 1])
            ->assertSessionHasNoErrors();
        $this->get(route('ppdb.index'))->assertDontSee('Masuk Portal', false);

        PpdbPeriod::create([
            'academic_year' => '2112/2113', 'status' => 'upcoming', 'is_active' => true,
            'opens_at' => now()->addMonth(),
        ]);

        $page = $this->get(route('ppdb.index'))->assertOk();
        $page->assertSee('Masuk Portal', false);
        $page->assertSee('Buat Akun Portal', false);
        $this->get(route('portal.register'))->assertOk();
        $this->get(route('portal.login'))->assertOk();
    }

    public function test_4_complete_deletes_immediately_without_waiting(): void
    {
        CarbonImmutable::setTestNow('2026-09-28 12:00:00 UTC');
        config()->set('retention.applicants.enabled', true);
        $admin = $this->superadmin();
        $user = $this->applicant('langsung@example.com');
        $period = $this->closedPeriod('2113/2114');
        // Tanpa account_retention_until manual: tetap langsung eligible.
        $this->assertNull($period->fresh()->account_retention_until);
        $this->releasedApp($user, $period);

        $this->actingAs($admin)->post(route('admin.periods.complete', $period), ['confirm' => 1])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        auth()->logout();
        $this->post(route('portal.login.store'), ['email' => 'langsung@example.com', 'password' => 'password123'])
            ->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    public function test_5_stale_session_cannot_access_portal_after_complete(): void
    {
        CarbonImmutable::setTestNow('2026-09-28 12:00:00 UTC');
        config()->set('retention.applicants.enabled', true);
        $user = $this->applicant('stale@example.com');
        $period = $this->closedPeriod('2114/2115');
        $this->releasedApp($user, $period);

        // Login sungguhan (sesi nyata), lalu SELESAI sebagai sistem.
        $this->post(route('portal.login.store'), ['email' => 'stale@example.com', 'password' => 'password123'])
            ->assertRedirect();
        $this->assertAuthenticatedAs($user);

        PpdbPeriodService::complete($period, null);
        app(\App\Services\ApplicantAccountLifecycleService::class)
            ->cleanupPeriod($period->id, CarbonImmutable::now('UTC'));
        $this->assertDatabaseMissing('users', ['id' => $user->id]);

        // Tab lama di-refresh: request baru me-resolve ulang sesi ke database.
        // forgetGuards mensimulasikan proses PHP baru (tanpa memoization guard).
        auth()->forgetGuards();
        $this->get(route('portal.dashboard'))->assertRedirect(route('portal.login'));
        $this->assertGuest();
    }

    public function test_6_sixteen_apps_history_survives_account_delete(): void
    {
        CarbonImmutable::setTestNow('2026-09-28 12:00:00 UTC');
        config()->set('retention.applicants.enabled', true);
        $admin = $this->superadmin();
        $period = $this->closedPeriod('2115/2116');
        for ($i = 1; $i <= 16; $i++) {
            $this->releasedApp($this->applicant("riwayat{$i}@example.com"), $period, $i % 2 ? 'passed' : 'not_passed');
        }
        $this->assertEquals(16, $period->applications()->count());

        $this->actingAs($admin)->post(route('admin.periods.complete', $period), ['confirm' => 1])
            ->assertSessionHasNoErrors();

        $this->assertEquals(0, User::where('is_applicant', true)->where('is_admin', false)->count());
        $this->assertEquals(16, $period->fresh()->applications()->count());
        $this->assertEquals(16, \DB::table('application_decisions')
            ->join('ppdb_registrations', 'ppdb_registrations.id', '=', 'application_decisions.application_id')
            ->where('ppdb_registrations.period_id', $period->id)->count());
    }

    public function test_7_next_period_fresh_and_same_email_registers_again(): void
    {
        CarbonImmutable::setTestNow('2026-09-28 12:00:00 UTC');
        config()->set('retention.applicants.enabled', true);
        $admin = $this->superadmin();
        $old = $this->closedPeriod('2116/2117');
        $this->releasedApp($this->applicant('parent@example.com'), $old);
        $this->actingAs($admin)->post(route('admin.periods.complete', $old), ['confirm' => 1])
            ->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('users', ['email' => 'parent@example.com']);

        $new = PpdbPeriod::create([
            'academic_year' => '2117/2118', 'status' => 'open', 'is_active' => true,
            'opens_at' => now()->subDay(), 'closes_at' => now()->addMonth(),
        ]);
        $this->assertEquals($new->id, \App\Services\PpdbContext::current()->id);
        $this->assertEquals(0, $new->applications()->count());
        $this->assertEquals(0, User::where('email', 'parent@example.com')->count());

        // Email lama daftar lagi sebagai pendaftar baru.
        $this->post(route('portal.register.store'), [
            'name' => 'Orang Tua', 'email' => 'parent@example.com',
            'password' => 'password123', 'password_confirmation' => 'password123',
        ])->assertRedirect(route('portal.dashboard'));
        $this->assertDatabaseHas('users', ['email' => 'parent@example.com']);
    }

    public function test_8_staff_with_applicant_link_survives(): void
    {
        CarbonImmutable::setTestNow('2026-09-28 12:00:00 UTC');
        config()->set('retention.applicants.enabled', true);
        $admin = $this->superadmin();
        $staff = User::factory()->create([
            'email' => 'staff@example.com', 'is_admin' => true, 'is_superadmin' => false,
            'is_applicant' => true, 'is_active' => true, 'email_verified_at' => now(),
        ]);
        $period = $this->closedPeriod('2118/2119');
        $this->releasedApp($staff, $period);

        $this->actingAs($admin)->post(route('admin.periods.complete', $period), ['confirm' => 1])
            ->assertSessionHasNoErrors();
        $this->assertDatabaseHas('users', ['id' => $staff->id]);
    }

    public function test_9_admin_row_for_completed_is_historical_only(): void
    {
        $admin = $this->superadmin();
        PpdbPeriod::where('academic_year', '2026/2027')->update([
            'status' => 'completed', 'is_active' => false,
            'results_released_at' => now()->subDays(30),
            'operational_completed_at' => now()->subDays(20),
            'account_retention_until' => now()->subDay(),
        ]);
        $period = $this->closedPeriod('2119/2120');
        $this->releasedApp($this->applicant('row@example.com'), $period);
        $this->actingAs($admin)->post(route('admin.periods.complete', $period), ['confirm' => 1])
            ->assertSessionHasNoErrors();

        $page = $this->actingAs($admin)->get(route('admin.periods.index'))->assertOk();
        $page->assertSee('Selesai', false);
        $page->assertDontSee('Selesaikan', false);
        $page->assertDontSee('Buka Lagi', false);
        $page->assertDontSee('>Ubah<', false);
    }
}
