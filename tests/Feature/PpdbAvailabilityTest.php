<?php

namespace Tests\Feature;

use App\Models\PPDBRegistration;
use App\Models\PpdbPeriod;
use App\Models\Program;
use App\Models\User;
use App\Services\PpdbAvailability;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PpdbAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    private function period(array $overrides = []): PpdbPeriod
    {
        // Backfill migrasi sudah membuat 2026/2027 — pakai ulang bila sama.
        $year = $overrides['academic_year'] ?? '2027/2028';
        $period = PpdbPeriod::firstOrNew(['academic_year' => $year]);
        $period->fill(array_merge([
            'status' => 'open',
            'opens_at' => CarbonImmutable::now('Asia/Jakarta')->subDay(),
            'closes_at' => CarbonImmutable::now('Asia/Jakarta')->addDays(30),
            'is_open' => true, 'is_active' => true, 'is_archived' => false,
            'status_override' => null,
        ], $overrides));
        $period->save();
        \App\Services\PpdbContext::flush();

        return $period->fresh();
    }

    private function applicant(): User
    {
        return User::factory()->create(['is_admin' => false, 'is_applicant' => true, 'is_active' => true, 'email_verified_at' => now()]);
    }

    private function draftApp(User $user, int $programId, int $periodId): PPDBRegistration
    {
        $app = PPDBRegistration::create([
            'name' => 'Siswa '.uniqid(), 'gender' => 'laki-laki', 'program_id' => $programId,
            'applicant_account_id' => $user->id, 'period_id' => $periodId, 'source' => 'applicant',
        ]);
        \App\Services\DocumentService::ensurePlaceholders($app);

        return $app->fresh();
    }

    public function test_time_boundaries(): void
    {
        $opens = CarbonImmutable::now('Asia/Jakarta')->setTime(8, 0, 0);
        $closes = CarbonImmutable::now('Asia/Jakarta')->setTime(23, 59, 0);
        $period = $this->period(['opens_at' => $opens, 'closes_at' => $closes, 'status' => 'open']);

        $this->assertEquals(PpdbAvailability::UPCOMING, PpdbAvailability::forPeriod($period, $opens->subSecond())->status);
        $this->assertEquals(PpdbAvailability::OPEN, PpdbAvailability::forPeriod($period, $opens)->status);
        $this->assertEquals(PpdbAvailability::OPEN, PpdbAvailability::forPeriod($period, $closes->subSecond())->status);
        $this->assertEquals(PpdbAvailability::CLOSED, PpdbAvailability::forPeriod($period, $closes)->status);
        $this->assertEquals(PpdbAvailability::CLOSED, PpdbAvailability::forPeriod($period, $closes->addSecond())->status);
    }

    public function test_no_period_state(): void
    {
        PpdbPeriod::query()->delete();
        $state = PpdbAvailability::resolvePublic();
        $this->assertEquals(PpdbAvailability::NO_PERIOD, $state->status);
        $this->assertFalse($state->canRegister());
    }

    public function test_quota_progression_3(): void
    {
        $program = Program::factory()->create(['status' => 'active']);
        $period = $this->period(['quota' => 3]);

        $this->assertEquals(PpdbAvailability::OPEN, PpdbAvailability::forPeriod($period)->status);
        $this->assertEquals(3, PpdbAvailability::forPeriod($period)->remainingQuota());

        foreach ([1, 2] as $i) {
            $user = $this->applicant();
            $app = $this->draftApp($user, $program->id, $period->id);
            PpdbAvailability::submitApplication($app, $user->id);
        }
        $state = PpdbAvailability::forPeriod($period->fresh());
        $this->assertEquals(PpdbAvailability::OPEN, $state->status);
        $this->assertEquals(1, $state->remainingQuota());

        $user3 = $this->applicant();
        $app3 = $this->draftApp($user3, $program->id, $period->id);
        PpdbAvailability::submitApplication($app3, $user3->id);

        $full = PpdbAvailability::forPeriod($period->fresh());
        $this->assertEquals(PpdbAvailability::FULL, $full->status);
        $this->assertEquals(0, $full->remainingQuota());
        $this->assertFalse($full->canRegister());

        // Pendaftar ke-4 ditolak, draf tetap ada.
        $user4 = $this->applicant();
        $app4 = $this->draftApp($user4, $program->id, $period->id);
        try {
            PpdbAvailability::submitApplication($app4, $user4->id);
            $this->fail('Seharusnya ditolak karena kuota penuh.');
        } catch (\App\Services\QuotaFullException $e) {
            $this->assertStringContainsString('Kuota', $e->getMessage());
        }
        $this->assertEquals('draft', $app4->fresh()->application_status->value);
        $this->assertEquals(3, PpdbAvailability::usedQuota($period->id));
    }

    public function test_drafts_do_not_consume_quota(): void
    {
        $program = Program::factory()->create(['status' => 'active']);
        $period = $this->period(['quota' => 10]);
        $user = $this->applicant();
        for ($i = 0; $i < 20; $i++) {
            $this->draftApp($user, $program->id, $period->id);
        }
        $state = PpdbAvailability::forPeriod($period->fresh());
        $this->assertEquals(PpdbAvailability::OPEN, $state->status);
        $this->assertEquals(10, $state->remainingQuota());
    }

    public function test_quota_last_slot_race_sequential(): void
    {
        // Kuota 1: pendaftar pertama lolos, kedua ditolak tanpa overbooking.
        $program = Program::factory()->create(['status' => 'active']);
        $period = $this->period(['quota' => 1]);

        $userA = $this->applicant();
        $appA = $this->draftApp($userA, $program->id, $period->id);
        PpdbAvailability::submitApplication($appA, $userA->id);

        $userB = $this->applicant();
        $appB = $this->draftApp($userB, $program->id, $period->id);
        try {
            PpdbAvailability::submitApplication($appB, $userB->id);
            $this->fail('Seharusnya hanya satu yang lolos.');
        } catch (\App\Services\QuotaFullException) {
        }
        $this->assertEquals(1, PpdbAvailability::usedQuota($period->id));
        $this->assertEquals('draft', $appB->fresh()->application_status->value);
    }

    public function test_quota_is_per_period(): void
    {
        $program = Program::factory()->create(['status' => 'active']);
        $periodA = $this->period(['academic_year' => '2026/2027', 'quota' => 1, 'status' => 'closed', 'is_active' => false]);
        $user = $this->applicant();
        $old = $this->draftApp($user, $program->id, $periodA->id);
        $old->update(['application_status' => 'submitted']);

        $periodB = $this->period(['academic_year' => '2027/2028', 'quota' => 100]);
        $stateB = PpdbAvailability::forPeriod($periodB);
        $this->assertEquals(PpdbAvailability::OPEN, $stateB->status);
        $this->assertEquals(100, $stateB->remainingQuota());
    }

    public function test_home_ppdb_consistency_across_states(): void
    {
        $period = $this->period(['quota' => 1]);
        $home = $this->get('/')->assertOk();
        $ppdb = $this->get('/ppdb')->assertOk();
        foreach (['Sedang Dibuka', '2027/2028'] as $text) {
            $home->assertSee($text, false);
            $ppdb->assertSee($text, false);
        }
        $home->assertDontSee('Telah Dibuka', false);
        $home->assertDontSee('Telah Ditutup', false);

        // Penuhi kuota -> KEDUA halaman serempak FULL.
        $program = Program::factory()->create(['status' => 'active']);
        $user = $this->applicant();
        PpdbAvailability::submitApplication($this->draftApp($user, $program->id, $period->id), $user->id);

        // CTA fungsional hilang; tautan navigasi footer (/ppdb) tetap wajar ada.
        $this->get('/')->assertSee('Kuota Telah Terpenuhi', false)->assertSee('Lihat Informasi PPDB', false);
        $this->get('/ppdb')->assertSee('Kuota Telah Terpenuhi', false)->assertDontSee('Buat Akun & Daftar', false);
    }

    public function test_portal_create_blocked_when_full_or_closed(): void
    {
        $program = Program::factory()->create(['status' => 'active']);
        $period = $this->period(['quota' => 1]);
        $user = $this->applicant();

        $filler = $this->applicant();
        PpdbAvailability::submitApplication($this->draftApp($filler, $program->id, $period->id), $filler->id);

        $this->actingAs($user)->get(route('portal.applications.create'))->assertRedirect(route('portal.dashboard'));
        $this->actingAs($user)->post('/portal/aplikasi', ['name' => 'X', 'gender' => 'laki-laki', 'program_id' => $program->id])
            ->assertSessionHas('error');
        $this->assertEquals(0, $user->applications()->count());
    }

    public function test_manual_entry_override_audited(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'is_superadmin' => true, 'is_active' => true]);
        $program = Program::factory()->create(['status' => 'active']);
        $period = $this->period();
        $period->update(['status' => 'closed', 'is_active' => false]);

        // Tanpa alasan -> ditolak.
        $this->actingAs($admin)->post(route('admin.registrations.store'), [
            'name' => 'Manual', 'gender' => 'laki-laki', 'program_id' => $program->id, 'period_id' => $period->id,
        ])->assertSessionHasErrors(['override_reason']);

        // Dengan alasan -> lolos + audit.
        $this->actingAs($admin)->post(route('admin.registrations.store'), [
            'name' => 'Manual', 'gender' => 'laki-laki', 'program_id' => $program->id, 'period_id' => $period->id,
            'override_reason' => 'Datang langsung ke sekolah.',
        ])->assertRedirect();
        $app = PPDBRegistration::where('name', 'Manual')->first();
        $this->assertEquals('admin_manual', $app->source);
        $this->assertDatabaseHas('audit_logs', ['action' => 'ppdb_manual_create']);
    }

    public function test_submit_rejected_after_deadline_preserves_draft(): void
    {
        $program = Program::factory()->create(['status' => 'active']);
        $period = $this->period();
        $user = $this->applicant();
        $app = $this->draftApp($user, $program->id, $period->id);

        // Tenggat lewat di antara buka halaman dan klik submit.
        $period->update(['closes_at' => now('Asia/Jakarta')->subMinute()]);
        \App\Services\PpdbContext::flush();

        $this->actingAs($user)->post(route('portal.applications.submit', $app), ['confirm' => 1])
            ->assertSessionHas('error');
        $this->assertEquals('draft', $app->fresh()->application_status->value);
    }
}
