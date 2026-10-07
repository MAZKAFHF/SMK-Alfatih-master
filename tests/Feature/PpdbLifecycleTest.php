<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Models\ApplicationDecision;
use App\Models\EmailLog;
use App\Models\InterviewSlot;
use App\Models\PpdbPeriod;
use App\Models\PPDBRegistration;
use App\Models\Program;
use App\Models\RescheduleRequest;
use App\Models\User;
use App\Services\MailService;
use App\Services\PpdbContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * Lifecycle PPDB ujung-ke-ujung via HTTP (setara E2E flow 1-39):
 * akun -> verifikasi -> multi-siswa -> draf -> submit -> koreksi ->
 * verifikasi -> kunci -> change request -> slot -> booking ->
 * reschedule -> wawancara -> keputusan -> notifikasi/email -> periode ->
 * manual -> duplikat -> IDOR -> cetak -> export.
 */
class PpdbLifecycleTest extends TestCase
{
    use RefreshDatabase;

    private function period(): PpdbPeriod
    {
        return PpdbPeriod::first() ?? PpdbPeriod::create(['academic_year' => '2026/2027', 'is_open' => true]);
    }

    private function applicant(): User
    {
        return User::factory()->create(['is_admin' => false, 'is_applicant' => true, 'is_active' => true, 'email_verified_at' => null]);
    }

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true, 'is_superadmin' => true, 'is_active' => true]);
    }

    private function program(): Program
    {
        return Program::factory()->create(['status' => 'active']);
    }

    private function uploadAllDocs(PPDBRegistration $app, User $user): void
    {
        foreach (['kk', 'ktp_ortu', 'akta', 'rapor'] as $type) {
            $this->actingAs($user)->post(
                route('portal.documents.upload', [$app, $type]),
                ['file' => UploadedFile::fake()->create($type.'.pdf', 200, 'application/pdf')]
            )->assertRedirect();
        }
        $this->actingAs($user)->post(
            route('portal.documents.upload', [$app, 'foto']),
            ['file' => UploadedFile::fake()->create('foto.jpg', 200, 'image/jpeg')]
        )->assertRedirect();
    }

    private function submitReadyApp(User $user, Program|int $program, array $overrides = []): PPDBRegistration
    {
        $programId = $program instanceof Program ? $program->id : $program;
        $base = PpdbFinalSubmissionTest::completeData($programId);
        $app = PPDBRegistration::create(array_merge($base, [
            'applicant_account_id' => $user->id, 'period_id' => $this->period()->id, 'source' => 'applicant',
        ], $overrides));
        $this->uploadAllDocs($app, $user);
        $user->markEmailAsVerified();
        $this->actingAs($user)->post(route('portal.applications.submit', $app), ['confirm' => 1])->assertSessionHasNoErrors();

        return $app->fresh();
    }

    public function test_verification_link_marks_email_verified(): void
    {
        Storage::fake('ppdb_private');
        $user = $this->applicant();
        $url = URL::temporarySignedRoute('portal.verification.verify', now()->addHour(), ['id' => $user->id, 'hash' => sha1($user->email)]);
        $this->actingAs($user)->get($url)->assertRedirect(route('portal.dashboard'));
        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_multi_student_data_does_not_mix(): void
    {
        Storage::fake('ppdb_private');
        $user = $this->applicant();
        $user->markEmailAsVerified();
        $program = $this->program();
        $a = $this->submitReadyApp($user, $program, ['name' => 'Anak A', 'nik' => '3201010105100001', 'nisn' => '1111111111']);
        $b = $this->submitReadyApp($user, $program, ['name' => 'Anak B', 'nik' => '3201010105100002', 'nisn' => '2222222222']);

        $this->assertNotEquals($a->registration_number, $b->registration_number);
        $this->assertEquals(2, $user->applications()->count());

        // Edit A tidak mengubah B.
        $a->update(['school_origin' => 'SMP A']);
        $this->assertNotEquals('SMP A', $b->fresh()->school_origin);
        $this->assertEquals(5, $a->documents()->whereNotNull('path')->count());
        $this->assertEquals(5, $b->documents()->whereNotNull('path')->count());
    }

    public function test_draft_persists_across_logout_login(): void
    {
        $user = $this->applicant();
        $program = $this->program();
        $this->actingAs($user)->post('/portal/aplikasi', [
            'name' => 'Setengah Jalan', 'gender' => 'perempuan', 'program_id' => $program->id, 'city' => 'Bogor',
        ])->assertRedirect();
        auth()->logout();

        $user->markEmailAsVerified();
        $this->actingAs($user);
        $app = PPDBRegistration::first();
        $this->assertEquals('Setengah Jalan', $app->name);
        $this->assertEquals('Bogor', $app->city);
        $this->assertEquals(ApplicationStatus::Draft, $app->application_status);
    }

    public function test_correction_flow_preserves_history(): void
    {
        Storage::fake('ppdb_private');
        $admin = $this->admin();
        $user = $this->applicant();
        $app = $this->submitReadyApp($user, $this->program());

        // Admin tandai akta perlu perbaikan.
        $akta = $app->documents()->where('type', 'akta')->first();
        $this->actingAs($admin)->post(route('admin.documents.review', $akta), [
            'status' => 'needs_revision', 'admin_note' => 'Bagian bawah dokumen tidak terlihat dengan jelas.',
        ])->assertRedirect();
        $this->actingAs($admin)->post(route('admin.registrations.revise', $app), ['note' => 'Perbaiki akta.'])->assertRedirect();
        $this->assertEquals(ApplicationStatus::NeedsRevision, $app->fresh()->application_status);

        // Pemohon unggah pengganti -> versi naik + revisi tersimpan.
        $this->actingAs($user)->post(
            route('portal.documents.upload', [$app, 'akta']),
            ['file' => UploadedFile::fake()->create('akta2.pdf', 200, 'application/pdf')]
        )->assertRedirect();
        $akta->refresh();
        $this->assertEquals(2, $akta->version);
        $this->assertEquals(1, $akta->revisions()->count());

        // Kirim ulang -> resubmitted.
        $this->actingAs($user)->post(route('portal.applications.submit', $app), ['confirm' => 1])->assertSessionHasNoErrors();
        $this->assertEquals(ApplicationStatus::Resubmitted, $app->fresh()->application_status);

        // Admin validasi semua + verifikasi.
        foreach ($app->documents as $doc) {
            $this->actingAs($admin)->post(route('admin.documents.review', $doc), ['status' => 'valid'])->assertRedirect();
        }
        $this->actingAs($admin)->post(route('admin.registrations.verify', $app))->assertRedirect();
        $this->assertEquals(ApplicationStatus::Verified, $app->fresh()->application_status);

        // Kunci: edit langsung 403.
        $this->actingAs($user)->put(route('portal.applications.update', $app), [
            'name' => 'Diubah', 'gender' => 'laki-laki', 'program_id' => $app->program_id,
        ])->assertForbidden();
    }

    public function test_applicant_cannot_reopen_locked_data_and_admin_can_request_revision(): void
    {
        Storage::fake('ppdb_private');
        $admin = $this->admin();
        $user = $this->applicant();
        $app = $this->submitReadyApp($user, $this->program());
        $this->assertFalse(Route::has('portal.applications.change'));
        $this->actingAs($user)->get(route('portal.applications.show', $app))->assertDontSee('Minta Perubahan Data Terkunci', false);

        $this->actingAs($admin)->post(route('admin.registrations.revise', $app), [
            'note' => 'Nomor kontak perlu diperbaiki sesuai konfirmasi panitia.',
        ])->assertRedirect();
        $this->assertEquals(ApplicationStatus::NeedsRevision, $app->fresh()->application_status);
        $this->assertSame('Nomor kontak perlu diperbaiki sesuai konfirmasi panitia.', $app->fresh()->admin_notes);

        // Setelah admin meminta koreksi, pemohon dapat memperbaiki data drafnya.
        $this->actingAs($user)->put(route('portal.applications.update', $app), array_merge(
            PpdbFinalSubmissionTest::completeData($app->program_id),
            ['phone' => '089999']
        ))->assertRedirect();
        $this->assertEquals('089999', $app->fresh()->phone);
    }

    public function test_interview_booking_reschedule_and_complete(): void
    {
        Storage::fake('ppdb_private');
        $admin = $this->admin();
        $user = $this->applicant();
        $period = $this->period();
        $app = $this->submitReadyApp($user, $this->program());
        $app->update(['application_status' => ApplicationStatus::Verified, 'status' => 'pending']);

        $s1 = InterviewSlot::create(['period_id' => $period->id, 'date' => now('Asia/Jakarta')->addDay()->toDateString(), 'start_time' => '08:00', 'location' => 'R1', 'capacity' => 1, 'status' => 'active']);
        $s2 = InterviewSlot::create(['period_id' => $period->id, 'date' => now('Asia/Jakarta')->addDay()->toDateString(), 'start_time' => '09:00', 'location' => 'R1', 'capacity' => 1, 'status' => 'active']);

        // Booking slot 1.
        $this->actingAs($user)->post(route('portal.slots.book', $app), ['slot_id' => $s1->id])->assertRedirect();
        $this->assertEquals(ApplicationStatus::Scheduled, $app->fresh()->application_status);

        // Pemohon kedua tidak bisa rebut slot penuh.
        $user2 = $this->applicant();
        $user2->markEmailAsVerified();
        $app2 = $this->submitReadyApp($user2, $app->program_id, ['name' => 'Siswa Kedua', 'nik' => '3201010105100009', 'nisn' => '9999999999']);
        $app2->update(['application_status' => ApplicationStatus::Verified, 'status' => 'pending']);
        $this->actingAs($user2)->post(route('portal.slots.book', $app2), ['slot_id' => $s1->id])->assertSessionHasErrors(['slot']);

        // Reschedule ke slot 2 + approve -> pindah atomik.
        $this->actingAs($user)->post(route('portal.reschedule.store', $app), [
            'reason' => 'Ada acara keluarga mendadak pagi itu.', 'new_slot_id' => $s2->id,
        ])->assertRedirect();
        $rr = RescheduleRequest::first();
        $this->actingAs($admin)->post(route('admin.reschedules.decide', $rr), ['decision' => 'approved'])->assertRedirect();
        $this->assertEquals($s2->id, $app->fresh()->appointment->slot_id);
        $this->assertEquals(0, $s1->fresh()->booked_count);

        // Tandai hadir + asesmen (tanpa skor hardcode).
        $s2->update(['date' => now('Asia/Jakarta')->subDay()->toDateString()]);
        $appt = $app->fresh()->appointment;
        $this->actingAs($admin)->post(route('admin.appointments.complete', $appt), [
            'attendance' => 'attended', 'interview_notes' => 'Komunikatif.',
            'tahfizh_notes' => 'Lancar 2 halaman.', 'tahsin_notes' => 'Makhraj baik.', 'recommendation' => 'Layak.',
        ])->assertRedirect();
        $this->assertEquals(ApplicationStatus::WaitingDecision, $app->fresh()->application_status);
    }

    public function test_no_show_does_not_auto_reject(): void
    {
        Storage::fake('ppdb_private');
        $admin = $this->admin();
        $user = $this->applicant();
        $app = $this->submitReadyApp($user, $this->program());
        $slot = InterviewSlot::create(['period_id' => $this->period()->id, 'date' => now('Asia/Jakarta')->addDay()->toDateString(), 'start_time' => '10:00', 'capacity' => 1, 'status' => 'active']);
        $app->update(['application_status' => ApplicationStatus::Verified, 'status' => 'pending']);
        $this->actingAs($user)->post(route('portal.slots.book', $app), ['slot_id' => $slot->id])->assertRedirect();

        $slot->update(['date' => now('Asia/Jakarta')->subDay()->toDateString()]);
        $appt = $app->fresh()->appointment;
        $this->actingAs($admin)->post(route('admin.appointments.complete', $appt), ['attendance' => 'no_show'])->assertRedirect();
        // Tetap scheduled (tidak otomatis ditolak) — sekolah yang menentukan tindak lanjut.
        $this->assertEquals(ApplicationStatus::Scheduled, $app->fresh()->application_status);
        $this->assertEquals('no_show', $appt->fresh()->status->value);
    }

    public function test_pass_and_not_pass_results(): void
    {
        Storage::fake('ppdb_private');
        $admin = $this->admin();
        $user = $this->applicant();
        $app = $this->submitReadyApp($user, $this->program());
        $app->update(['application_status' => ApplicationStatus::WaitingDecision, 'status' => 'pending']);

        $this->actingAs($admin)->post(route('admin.registrations.decide', $app), [
            'result' => 'passed', 'confirm' => 1,
            'internal_note' => 'RAHASIA INTERNAL', 'applicant_message' => 'Selamat!',
        ])->assertRedirect();
        // Belum rilis -> portal belum tunjukkan hasil.
        $this->actingAs($user)->get(route('portal.applications.show', $app))->assertDontSee('RAHASIA INTERNAL', false);

        $this->actingAs($admin)->post(route('admin.registrations.release', $app))->assertRedirect();
        $page = $this->actingAs($user)->get(route('portal.applications.show', $app));
        $page->assertSee('Lulus', false);
        $page->assertDontSee('RAHASIA INTERNAL', false);
        $page->assertDontSee('not_passed', false);
        $this->assertNotNull(EmailLog::where('application_id', $app->id)->first());
        $this->assertTrue($user->notifications()->where('title', 'like', '%Hasil%')->exists());

        // NOT PASS di aplikasi lain.
        $user2 = $this->applicant();
        $app2 = $this->submitReadyApp($user2, $app->program_id, ['name' => 'Tidak Lulus', 'nik' => '3201010105100008', 'nisn' => '8888888888']);
        $app2->update(['application_status' => ApplicationStatus::WaitingDecision, 'status' => 'pending']);
        $this->actingAs($admin)->post(route('admin.registrations.decide', $app2), ['result' => 'not_passed', 'confirm' => 1, 'internal_note' => 'SKOR JELEK'])->assertRedirect();
        $this->actingAs($admin)->post(route('admin.registrations.release', $app2))->assertRedirect();
        $page2 = $this->actingAs($user2)->get(route('portal.applications.show', $app2));
        $page2->assertSee('Belum Lulus', false);
        $page2->assertDontSee('SKOR JELEK', false);
    }

    public function test_email_failure_keeps_decision(): void
    {
        Storage::fake('ppdb_private');
        $admin = $this->admin();
        $user = $this->applicant();
        $app = $this->submitReadyApp($user, $this->program());
        $app->update(['application_status' => ApplicationStatus::WaitingDecision, 'status' => 'pending']);
        $this->actingAs($admin)->post(route('admin.registrations.decide', $app), ['result' => 'passed', 'confirm' => 1])->assertRedirect();

        // Simulasikan SMTP mati via transport nyata yang pasti ditolak
        // (tanpa mock agar tidak bocor antar-tes).
        config(['mail.default' => 'broken-smtp']);
        config(['mail.mailers.broken-smtp' => [
            'transport' => 'smtp', 'host' => '127.0.0.1', 'port' => 1,
            'timeout' => 3,
        ]]);
        $log = MailService::send('result_pass', $user->email, 'Hasil', ['headline' => 'Hasil', 'body' => 'x'], $app);
        $this->assertEquals('failed', $log->status);
        $this->assertNotEmpty($log->error, 'Ringkasan kegagalan wajib tercatat di log.');
        $this->assertEquals('passed', $app->fresh()->decision->result->value);
        $this->assertEquals(1, EmailLog::where('application_id', $app->id)->where('status', 'failed')->count());
    }

    public function test_admin_can_resend_email_and_log_updates(): void
    {
        Storage::fake('ppdb_private');
        $admin = $this->admin();
        $user = $this->applicant();
        $app = $this->submitReadyApp($user, $this->program());
        $app->update(['application_status' => ApplicationStatus::WaitingDecision, 'status' => 'pending']);
        $this->actingAs($admin)->post(route('admin.registrations.decide', $app), ['result' => 'passed', 'confirm' => 1])->assertRedirect();
        $this->actingAs($admin)->post(route('admin.registrations.release', $app))->assertRedirect();

        $before = EmailLog::where('application_id', $app->id)->count();
        $this->assertGreaterThanOrEqual(1, $before);
        $this->actingAs($admin)->post(route('admin.registrations.resend', $app))->assertRedirect();
        $latest = EmailLog::where('application_id', $app->id)->latest('id')->first();
        $this->assertEquals('sent', $latest->status);
        $this->assertGreaterThanOrEqual(1, $latest->retries);
        $this->assertNotEmpty($latest->payload);
        // Tidak ada aksi bisnis ganda: tetap satu keputusan.
        $this->assertEquals(1, ApplicationDecision::where('application_id', $app->id)->count());
    }

    public function test_period_closed_blocks_public_but_allows_manual(): void
    {
        Storage::fake('ppdb_private');
        $admin = $this->admin();
        $period = $this->period();
        $period->update(['closes_at' => now('Asia/Jakarta')->subDay(), 'status_override' => null, 'is_open' => true]);
        PpdbPeriod::flushCache();

        // Portal: buat draf diblokir.
        $user = $this->applicant();
        $program = $this->program();
        $this->actingAs($user)->post('/portal/aplikasi', ['name' => 'X', 'gender' => 'laki-laki', 'program_id' => $program->id])
            ->assertSessionHas('error');

        // Admin manual: tanpa alasan override -> ditolak saat periode tutup.
        $this->actingAs($admin)->post(route('admin.registrations.store'), [
            'name' => 'Manual', 'gender' => 'laki-laki', 'program_id' => $program->id, 'period_id' => $period->id,
        ])->assertSessionHasErrors(['override_reason']);
        $this->assertNull(PPDBRegistration::where('name', 'Manual')->first());

        // Dengan alasan -> bisa + tercatat.
        $this->actingAs($admin)->post(route('admin.registrations.store'), [
            'name' => 'Manual', 'gender' => 'laki-laki', 'program_id' => $program->id, 'period_id' => $period->id,
            'override_reason' => 'Pengecualian langsung.',
        ])->assertRedirect();
        $manual = PPDBRegistration::where('name', 'Manual')->first();
        $this->assertEquals('admin_manual', $manual->source);
        $this->assertEquals($admin->id, $manual->created_by);
    }

    public function test_admin_routes_blocked_for_applicant_and_guest(): void
    {
        // Tamu dulu (actingAs menempel pada request berikutnya).
        $this->get('/admin')->assertRedirect(route('admin.login'));
        $user = $this->applicant();
        $this->actingAs($user)->get('/admin')->assertForbidden();
        $this->actingAs($user)->get(route('admin.registrations.index'))->assertForbidden();
    }

    public function test_print_and_export_are_human_readable(): void
    {
        Storage::fake('ppdb_private');
        $admin = $this->admin();
        $user = $this->applicant();
        $app = $this->submitReadyApp($user, $this->program());

        // Halaman admin (detail + slot + list) render tanpa error view/enum.
        $this->actingAs($admin)->get(route('admin.registrations.show', $app))->assertOk()->assertDontSee('validation.', false);
        $this->actingAs($admin)->get(route('admin.slots.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.registrations.index'))->assertOk();
        $this->actingAs($user)->get(route('portal.applications.show', $app))->assertOk()->assertDontSee('validation.', false);
        $this->actingAs($user)->get(route('portal.applications.review', $app))->assertOk();

        $print = $this->actingAs($admin)->get(route('admin.registrations.print', $app))->assertOk();
        $print->assertSee($app->registration_number, false);
        $print->assertDontSee('not_uploaded', false);

        $list = $this->actingAs($admin)->get(route('admin.registrations.print-list'))->assertOk();
        $list->assertSee($app->name, false);

        // Filter: program lain tidak ikut.
        $other = Program::factory()->create(['status' => 'active', 'name' => 'Program Lain Unik']);
        $filtered = $this->actingAs($admin)->get(route('admin.registrations.print-list', ['program_id' => $other->id]))->assertOk();
        $filtered->assertDontSee($app->name, false);

        $export = $this->actingAs($admin)->get(route('admin.registrations.export'))->assertOk();
        $this->assertStringContainsString('Nomor Registrasi', $export->streamedContent());
        $this->assertStringNotContainsString('not_passed', $export->streamedContent());
    }

    public function test_period_close_invalidates_context_immediately(): void
    {
        $admin = $this->admin();
        $period = $this->period();
        $this->assertTrue($period->isOpen());
        $this->actingAs($admin)->post(route('admin.periods.close', $period))->assertRedirect();
        $this->assertNull(PpdbContext::current());
        $latest = PpdbContext::latestClosed();
        $this->assertNotNull($latest);
        $this->assertEquals('2026/2027', $latest->academic_year);
    }
}
