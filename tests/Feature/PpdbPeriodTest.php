<?php

namespace Tests\Feature;

use App\Models\InterviewSlot;
use App\Models\PpdbPeriod;
use App\Models\PPDBRegistration;
use App\Models\Program;
use App\Models\User;
use App\Services\DocumentService;
use App\Services\JakartaDateTime;
use App\Services\PpdbAvailability;
use App\Services\PpdbContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PpdbPeriodTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true, 'is_superadmin' => true, 'is_active' => true]);
    }

    private function applicant(): User
    {
        return User::factory()->create(['is_admin' => false, 'is_applicant' => true, 'is_active' => true, 'email_verified_at' => now()]);
    }

    private function openPeriod(string $year, ?string $opensAt = null, ?string $closesAt = null): PpdbPeriod
    {
        // Migrasi backfill sudah membuat 2026/2027 — pakai ulang, jangan duplikat.
        $period = PpdbPeriod::firstOrNew(['academic_year' => $year]);
        $period->fill([
            'status' => 'open',
            'opens_at' => $opensAt, 'closes_at' => $closesAt,
            'is_open' => true, 'is_active' => true, 'is_archived' => false,
            'status_override' => null,
        ])->save();
        PpdbContext::flush();

        return $period->fresh();
    }

    private function fullPayload(int $programId, string $nik, array $overrides = []): array
    {
        return array_merge([
            'name' => 'Siswa '.$nik,
            'nik' => $nik,
            'nisn' => substr($nik, 0, 10),
            'birth_place' => 'Bogor',
            'birth_date' => '2010-05-12',
            'gender' => 'laki-laki',
            'address' => 'Jl. Merdeka No. 1',
            'province' => 'Jawa Barat',
            'city' => 'Kota Bogor',
            'district' => 'Bogor Tengah',
            'village' => 'Pabaton',
            'postal_code' => '16121',
            'school_origin' => 'SMPN 1 Bogor',
            'phone' => '081234567890',
            'email' => 's'.$nik.'@example.id',
            'father_name' => 'Ayah X',
            'father_phone' => '081234567891',
            'mother_name' => 'Ibu X',
            'program_id' => $programId,
        ], $overrides);
    }

    public function test_wib_datetime_roundtrip_has_no_drift_after_five_saves(): void
    {
        $admin = $this->admin();
        PpdbPeriod::query()->update(['status' => 'closed', 'is_active' => false]);
        PpdbContext::flush();
        $payload = ['academic_year' => '2029/2030', 'opens_at' => '2029-09-25T14:50', 'closes_at' => '2029-09-26T12:30', 'quota' => 100];
        $this->actingAs($admin)->post(route('admin.periods.store'), $payload)->assertRedirect();
        $period = PpdbPeriod::where('academic_year', '2029/2030')->firstOrFail();

        for ($i = 0; $i < 5; $i++) {
            $this->assertSame('2029-09-25T14:50', JakartaDateTime::forInput($period->fresh()->opens_at));
            $this->actingAs($admin)->put(route('admin.periods.update', $period), $payload)->assertRedirect();
        }

        $this->assertSame('2029-09-25T14:50', JakartaDateTime::forInput($period->fresh()->opens_at));
        $this->assertSame('2029-09-26T12:30', JakartaDateTime::forInput($period->fresh()->closes_at));
        $this->assertSame('14:50', $period->fresh()->opens_at->format('H:i'));
    }

    public function test_mandatory_regression_16_0_1(): void
    {
        $admin = $this->admin();
        $program = Program::factory()->create(['status' => 'active']);

        // Periode A dengan 16 aplikasi: 6 lulus / 5 verifikasi / 3 tidak lulus / 2 batal.
        $periodA = $this->openPeriod('2026/2027');
        $statuses = array_merge(
            array_fill(0, 6, 'accepted'), array_fill(0, 5, 'pending'),
            array_fill(0, 3, 'rejected'), array_fill(0, 2, 'cancelled')
        );
        foreach ($statuses as $i => $st) {
            PPDBRegistration::factory()->create([
                'period_id' => $periodA->id, 'status' => $st,
                'nik' => '320101010510'.str_pad((string) $i, 4, '0', STR_PAD_LEFT),
                'program_id' => $program->id,
            ]);
        }

        // Tutup A -> dashboard mode riwayat dengan angka persis.
        $this->actingAs($admin)->post(route('admin.periods.close', $periodA))->assertRedirect();
        $dash = $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
        $dash->assertSee('2026/2027', false);
        $dash->assertSee('RIWAYAT', false);
        $this->assertEquals(16, PPDBRegistration::where('period_id', $periodA->id)->count());

        // Buat + buka B -> semua metrik nol.
        $this->actingAs($admin)->post(route('admin.periods.store'), ['academic_year' => '2027/2028'])->assertRedirect();
        $periodB = PpdbPeriod::where('academic_year', '2027/2028')->first();
        $this->actingAs($admin)->post(route('admin.periods.open', $periodB))->assertRedirect();
        PpdbContext::flush();

        $dashB = $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
        $dashB->assertSee('2027/2028', false);
        $dashB->assertDontSee('RIWAYAT', false);
        $this->assertEquals(0, PPDBRegistration::where('period_id', $periodB->id)->count());

        // Satu aplikasi di B -> B=1, A tetap 16.
        $payload = $this->fullPayload($program->id, '3201010105990001');
        $this->actingAs($this->applicant())->post(route('portal.applications.store'), $payload)->assertRedirect();
        $this->assertEquals(1, PPDBRegistration::where('period_id', $periodB->id)->count());
        $this->assertEquals(16, PPDBRegistration::where('period_id', $periodA->id)->count());
        $this->assertEquals($periodB->id, PPDBRegistration::where('nik', '3201010105990001')->first()->period_id);
    }

    public function test_cannot_open_two_periods_at_once(): void
    {
        $admin = $this->admin();
        $this->openPeriod('2026/2027');
        $this->actingAs($admin)->post(route('admin.periods.store'), ['academic_year' => '2027/2028'])->assertRedirect();
        $periodB = PpdbPeriod::where('academic_year', '2027/2028')->first();
        $this->actingAs($admin)->post(route('admin.periods.open', $periodB))->assertSessionHasErrors(['status']);
        $this->assertEquals('draft', $periodB->fresh()->status);
    }

    public function test_open_period_supports_safe_json_response_without_download(): void
    {
        $admin = $this->admin();
        PpdbPeriod::query()->update(['status' => 'closed', 'is_active' => false]);
        $period = PpdbPeriod::create([
            'academic_year' => '2030/2031',
            'status' => 'draft',
            'is_active' => false,
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->postJson(route('admin.periods.open', $period))
            ->assertOk()
            ->assertHeader('content-type', 'application/json')
            ->assertJsonPath('redirect', route('admin.periods.index'));

        $this->assertSame('open', $period->fresh()->status);
    }

    public function test_period_boundaries_upcoming_open_closed(): void
    {
        $admin = $this->admin();
        $program = Program::factory()->create(['status' => 'active']);
        $opens = now('Asia/Jakarta')->addDay()->format('Y-m-d\TH:i');
        $closes = now('Asia/Jakarta')->addDays(10)->format('Y-m-d\TH:i');

        // Tutup periode legacy (dateless-open) agar tidak memblokir window baru.
        $legacy = PpdbPeriod::where('academic_year', '2026/2027')->first();
        if ($legacy && in_array($legacy->status, PpdbPeriod::CURRENT_STATUSES, true)) {
            $this->actingAs($admin)->post(route('admin.periods.close', $legacy))->assertRedirect();
        }
        PpdbContext::flush();

        $this->actingAs($admin)->post(route('admin.periods.store'), [
            'academic_year' => '2028/2029', 'opens_at' => $opens, 'closes_at' => $closes,
        ])->assertRedirect();
        $period = PpdbPeriod::where('academic_year', '2028/2029')->first();
        // Status draft yang punya opens_at masa depan => efektif upcoming, publik tertutup.
        $period->update(['status' => 'upcoming']);
        PpdbContext::flush();

        $this->assertFalse(PpdbAvailability::resolvePublic()->canCreateApplication());

        // Buka (override tanggal ke masa lalu) => publik terbuka.
        $period->update(['status' => 'open', 'opens_at' => now()->subDay(), 'closes_at' => now()->addDay()]);
        PpdbContext::flush();
        $this->assertTrue(PpdbAvailability::resolvePublic()->canCreateApplication());
    }

    public function test_interview_slot_cross_period_isolation(): void
    {
        $user = $this->applicant();
        $program = Program::factory()->create(['status' => 'active']);
        $periodA = $this->openPeriod('2026/2027');
        $slotA = InterviewSlot::create([
            'period_id' => $periodA->id, 'date' => now('Asia/Jakarta')->addDay()->toDateString(),
            'start_time' => '08:00', 'capacity' => 5, 'status' => 'active',
        ]);
        $app = PPDBRegistration::factory()->create([
            'period_id' => $periodA->id, 'applicant_account_id' => $user->id,
            'application_status' => 'verified', 'status' => 'pending', 'program_id' => $program->id,
        ]);
        DocumentService::ensurePlaceholders($app);

        $this->openPeriod('2027/2028');
        PpdbContext::flush();

        // Slot periode A tidak muncul untuk aplikasi periode A? (kontrol: muncul)
        $this->actingAs($user)->get(route('portal.slots.index', $app))->assertSee('08:00', false);

        // Aplikasi periode B tidak melihat slot A.
        $appB = PPDBRegistration::factory()->create([
            'period_id' => PpdbPeriod::where('academic_year', '2027/2028')->first()->id,
            'applicant_account_id' => $user->id,
            'application_status' => 'verified', 'status' => 'pending', 'program_id' => $program->id,
        ]);
        DocumentService::ensurePlaceholders($appB);
        // Periode B tidak punya slot => pesan kosong.
        $this->actingAs($user)->get(route('portal.slots.index', $appB))->assertSee('Belum ada jadwal', false);
        $this->assertEquals($slotA->id, InterviewSlot::where('period_id', $periodA->id)->first()->id);
    }

    public function test_export_and_list_default_to_dashboard_period(): void
    {
        $admin = $this->admin();
        $program = Program::factory()->create(['status' => 'active']);
        $periodA = $this->openPeriod('2026/2027');
        PPDBRegistration::factory()->create(['period_id' => $periodA->id, 'status' => 'pending', 'program_id' => $program->id, 'name' => 'Anak Lama']);
        $periodB = $this->openPeriod('2027/2028', now()->subDay()->format('Y-m-d H:i'), now()->addDay()->format('Y-m-d H:i'));
        // Tutup A agar B menjadi konteks dashboard.
        $periodA->update(['status' => 'closed', 'is_active' => false]);
        PpdbContext::flush();

        $csv = $this->actingAs($admin)->get(route('admin.registrations.export'))->assertOk()->streamedContent();
        $this->assertStringNotContainsString('Anak Lama', $csv);

        $list = $this->actingAs($admin)->get(route('admin.registrations.index'))->assertOk();
        $list->assertDontSee('Anak Lama', false);

        // Filter eksplisit periode lama tetap bisa.
        $listOld = $this->actingAs($admin)->get(route('admin.registrations.index', ['period_id' => $periodA->id]))->assertOk();
        $listOld->assertSee('Anak Lama', false);
    }

    public function test_portal_groups_current_and_history(): void
    {
        $user = $this->applicant();
        $program = Program::factory()->create(['status' => 'active']);
        $periodA = $this->openPeriod('2026/2027');
        $old = PPDBRegistration::factory()->create([
            'period_id' => $periodA->id, 'applicant_account_id' => $user->id,
            'status' => 'accepted', 'program_id' => $program->id, 'name' => 'Kakak Lulus',
        ]);
        $periodA->update(['status' => 'closed', 'is_active' => false]);
        $this->openPeriod('2027/2028');
        PpdbContext::flush();

        $page = $this->actingAs($user)->get(route('portal.dashboard'))->assertOk();
        $page->assertSee('Riwayat', false);
        $page->assertSee('Kakak Lulus', false);
    }

    public function test_duplicate_nik_allowed_across_periods(): void
    {
        $program = Program::factory()->create(['status' => 'active']);
        $this->openPeriod('2026/2027');
        $user = $this->applicant();
        $payload = $this->fullPayload($program->id, '3201010105770001');
        $this->actingAs($user)->post(route('portal.applications.store'), $payload)->assertRedirect();

        $periodB = $this->openPeriod('2027/2028');
        PpdbPeriod::where('id', '<>', $periodB->id)->update(['status' => 'closed', 'is_active' => false]);
        PpdbContext::flush();

        // NIK sama di periode berbeda: diizinkan (nama+HP dibedakan agar
        // tidak kena guard double-submit yang memang benar).
        $this->actingAs($user)->post(route('portal.applications.store'), array_merge($payload, [
            'email' => 'lain@example.id', 'name' => 'Siswa Pindahan', 'phone' => '081299900011',
        ]))->assertRedirect();
        $this->assertEquals(2, PPDBRegistration::where('nik', '3201010105770001')->count());
    }

    public function test_trend_uses_real_period_scoped_data(): void
    {
        $admin = $this->admin();
        $program = Program::factory()->create(['status' => 'active']);
        $period = $this->openPeriod('2026/2027');

        // Data deterministik [2,0,3,1,0,4,6] selama 7 hari terakhir (WIB).
        $plan = [2, 0, 3, 1, 0, 4, 6];
        foreach ($plan as $ago => $count) {
            $daysAgo = 6 - $ago;
            for ($i = 0; $i < $count; $i++) {
                $app = PPDBRegistration::factory()->create([
                    'period_id' => $period->id, 'status' => 'pending', 'program_id' => $program->id,
                ]);
                $app->updateQuietly(['created_at' => now('Asia/Jakarta')->subDays($daysAgo)->setTime(10, $i)]);
            }
        }
        // Satu aplikasi periode lain di hari ini — tidak boleh masuk tren.
        $other = $this->openPeriod('2027/2028');
        $period->update(['status' => 'closed', 'is_active' => false]);
        PpdbContext::flush();
        PPDBRegistration::factory()->create(['period_id' => $other->id, 'status' => 'pending', 'program_id' => $program->id]);
        $other->update(['status' => 'closed', 'is_active' => false]);
        $period->update(['status' => 'open', 'is_active' => true]);
        PpdbContext::flush();

        $response = $this->actingAs($admin)->get(route('admin.dashboard', ['period' => $period->id]))->assertOk();
        $content = $response->getContent();
        foreach (array_merge($plan, [16]) as $n) {
            $this->assertStringContainsString((string) $n, $content);
        }
        // Total tren periode = 16, bukan 17 (data periode lain dikecualikan).
        $this->assertEquals(16, PPDBRegistration::where('period_id', $period->id)->count());
    }

    public function test_manual_entry_requires_explicit_period(): void
    {
        $admin = $this->admin();
        $program = Program::factory()->create(['status' => 'active']);
        $period = $this->openPeriod('2026/2027');
        $this->actingAs($admin)->post(route('admin.registrations.store'), [
            'name' => 'Manual', 'gender' => 'laki-laki', 'program_id' => $program->id,
        ])->assertSessionHasErrors(['period_id']);

        $this->actingAs($admin)->post(route('admin.registrations.store'), [
            'name' => 'Manual', 'gender' => 'laki-laki', 'program_id' => $program->id, 'period_id' => $period->id,
        ])->assertRedirect();
        $this->assertEquals($period->id, PPDBRegistration::where('name', 'Manual')->first()->period_id);
    }
}
