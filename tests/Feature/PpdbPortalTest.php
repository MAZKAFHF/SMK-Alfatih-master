<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Models\InterviewSlot;
use App\Models\PPDBRegistration;
use App\Models\PpdbPeriod;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class PpdbPortalTest extends TestCase
{
    use RefreshDatabase;

    private function period(): PpdbPeriod
    {
        return PpdbPeriod::first() ?? PpdbPeriod::create(['academic_year' => '2026/2027', 'is_open' => true]);
    }

    private function program(): Program
    {
        return Program::factory()->create(['status' => 'active']);
    }

    private function applicant(): User
    {
        return User::factory()->create(['is_admin' => false, 'is_applicant' => true, 'is_active' => true, 'email_verified_at' => now()]);
    }

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true, 'is_superadmin' => false, 'is_active' => true]);
    }

    public function test_admin_logged_in_can_still_open_portal_register_and_login(): void
    {
        // Regresi: admin yang klik "Buat Akun" / "Masuk Portal" tidak boleh
        // dibuang ke /admin.dashboard oleh middleware `guest` global.
        $this->actingAs($this->admin());
        $this->get('/portal/daftar')->assertOk()->assertSee('Buat Akun PPDB', false);
        $this->get('/portal/masuk')->assertOk()->assertSee('Masuk Portal PPDB', false);
    }

    public function test_applicant_logged_in_is_redirected_to_portal_dashboard(): void
    {
        $this->actingAs($this->applicant());
        $this->get('/portal/daftar')->assertRedirect(route('portal.dashboard'));
        $this->get('/portal/masuk')->assertRedirect(route('portal.dashboard'));
    }

    public function test_guest_opening_portal_dashboard_goes_to_portal_login(): void
    {
        // Regresi: tamu yang buka /portal harus ke portal.login, bukan admin.login.
        $this->get('/portal')->assertRedirect(route('portal.login'));
    }

    public function test_email_verification_continues_automatically_after_login_on_another_device(): void
    {
        $user = User::factory()->create([
            'email' => 'cross-device@example.com', 'password' => Hash::make('Pass1234'),
            'is_admin' => false, 'is_applicant' => true, 'is_active' => true, 'email_verified_at' => null,
        ]);
        $url = URL::temporarySignedRoute('portal.verification.verify', now()->addHour(), ['id' => $user->id, 'hash' => sha1($user->email)]);

        $this->get($url)->assertRedirect(route('portal.login'));
        $response = $this->post(route('portal.login.store'), ['email' => $user->email, 'password' => 'Pass1234']);
        $response->assertRedirect($url);
        $this->get($url)->assertRedirect(route('portal.dashboard'));
        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_register_while_admin_logged_in_switches_to_new_applicant(): void
    {
        $this->actingAs($this->admin());
        $this->post('/portal/daftar', [
            'name' => 'Ortu Baru', 'email' => 'baru@example.com',
            'password' => 'Pass1234', 'password_confirmation' => 'Pass1234',
        ])->assertRedirect(route('portal.dashboard'));
        $this->assertAuthenticatedAs(User::where('email', 'baru@example.com')->first());
    }

    public function test_parent_can_register_and_create_multiple_students(): void
    {
        $this->post('/portal/daftar', [
            'name' => 'Orang Tua', 'email' => 'ortu@example.com', 'phone' => '0811',
            'password' => 'Pass1234', 'password_confirmation' => 'Pass1234',
        ])->assertRedirect(route('portal.dashboard'));

        $this->assertDatabaseHas('users', ['email' => 'ortu@example.com', 'is_applicant' => true]);
        $user = User::where('email', 'ortu@example.com')->first();

        $this->actingAs($user);
        $program = $this->program();
        $this->post('/portal/aplikasi', ['name' => 'Anak A', 'gender' => 'laki-laki', 'program_id' => $program->id])->assertRedirect();
        $this->post('/portal/aplikasi', ['name' => 'Anak B', 'gender' => 'perempuan', 'program_id' => $program->id])->assertRedirect();
        $this->assertEquals(2, $user->applications()->count());
    }

    public function test_submit_requires_verification_and_documents(): void
    {
        $user = User::factory()->create(['is_admin' => false, 'is_applicant' => true, 'is_active' => true, 'email_verified_at' => null]);
        $this->actingAs($user);
        $program = $this->program();
        $app = PPDBRegistration::create(['name' => 'Siswa', 'gender' => 'laki-laki', 'program_id' => $program->id, 'applicant_account_id' => $user->id, 'period_id' => $this->period()->id, 'source' => 'applicant']);

        // Belum verifikasi → ditolak
        $this->post(route('portal.applications.submit', $app), ['confirm' => 1])->assertSessionHas('error');

        $user->markEmailAsVerified();
        // Dokumen belum lengkap → ditolak
        $this->post(route('portal.applications.submit', $app), ['confirm' => 1])->assertSessionHas('error');
    }

    public function test_idor_blocked_between_accounts(): void
    {
        $a = $this->applicant();
        $b = $this->applicant();
        $program = $this->program();
        $appB = PPDBRegistration::create(['name' => 'Milik B', 'gender' => 'laki-laki', 'program_id' => $program->id, 'applicant_account_id' => $b->id, 'period_id' => $this->period()->id, 'source' => 'applicant']);

        $this->actingAs($a);
        $this->get(route('portal.applications.show', $appB))->assertForbidden();
        $this->put(route('portal.applications.update', $appB), ['name' => 'Hacked', 'gender' => 'laki-laki', 'program_id' => $program->id])->assertForbidden();
    }

    public function test_slot_booking_is_transaction_safe_and_no_overbook(): void
    {
        Storage::fake('ppdb_private');
        $period = $this->period();
        $program = $this->program();
        $slot = InterviewSlot::create(['period_id' => $period->id, 'date' => now('Asia/Jakarta')->addDay()->toDateString(), 'start_time' => '08:00', 'location' => 'R1', 'capacity' => 1, 'status' => 'active']);

        $u1 = $this->applicant();
        $u2 = $this->applicant();
        $a1 = PPDBRegistration::create(['name' => 'S1', 'gender' => 'laki-laki', 'program_id' => $program->id, 'applicant_account_id' => $u1->id, 'period_id' => $period->id, 'application_status' => ApplicationStatus::Verified, 'status' => 'pending', 'source' => 'applicant']);
        $a2 = PPDBRegistration::create(['name' => 'S2', 'gender' => 'perempuan', 'program_id' => $program->id, 'applicant_account_id' => $u2->id, 'period_id' => $period->id, 'application_status' => ApplicationStatus::Verified, 'status' => 'pending', 'source' => 'applicant']);

        $this->actingAs($u1)->post(route('portal.slots.book', $a1), ['slot_id' => $slot->id])->assertRedirect();
        $this->actingAs($u2)->post(route('portal.slots.book', $a2), ['slot_id' => $slot->id])->assertSessionHasErrors('slot');
        $this->assertEquals(1, InterviewSlot::find($slot->id)->booked_count);
    }

    public function test_slot_picker_has_clear_selected_state_and_confirmation_summary(): void
    {
        $period = $this->period();
        $user = $this->applicant();
        $app = PPDBRegistration::create([
            'name' => 'Pemilih Jadwal', 'gender' => 'laki-laki', 'program_id' => $this->program()->id,
            'applicant_account_id' => $user->id, 'period_id' => $period->id,
            'application_status' => ApplicationStatus::Verified, 'status' => 'pending', 'source' => 'applicant',
        ]);
        InterviewSlot::create([
            'period_id' => $period->id, 'date' => now('Asia/Jakarta')->addDay()->toDateString(),
            'start_time' => '08:00', 'location' => 'Ruang Podcast', 'capacity' => 10, 'status' => 'active',
        ]);

        $this->actingAs($user)->get(route('portal.slots.index', $app))
            ->assertOk()
            ->assertSee('data-slot-form', false)
            ->assertSee('data-slot-option', false)
            ->assertSee('data-slot-summary-panel', false)
            ->assertSee('data-slot-submit', false)
            ->assertSee('Konfirmasi Jadwal Pilihan')
            ->assertSee('Pilih satu jadwal.');
    }

    public function test_scheduled_interview_stage_renders_status_badge_without_error(): void
    {
        $period = $this->period();
        $program = $this->program();
        $user = $this->applicant();
        $application = PPDBRegistration::create([
            'name' => 'Siswa Terjadwal', 'gender' => 'laki-laki', 'program_id' => $program->id,
            'applicant_account_id' => $user->id, 'period_id' => $period->id,
            'application_status' => ApplicationStatus::Scheduled, 'status' => 'pending', 'source' => 'applicant',
        ]);
        $slot = InterviewSlot::create([
            'period_id' => $period->id, 'date' => now('Asia/Jakarta')->addDay()->toDateString(),
            'start_time' => '08:00', 'location' => 'Ruang Wawancara', 'capacity' => 5, 'booked_count' => 1, 'status' => 'active',
        ]);
        \App\Models\InterviewAppointment::create([
            'application_id' => $application->id, 'slot_id' => $slot->id, 'status' => 'scheduled',
        ]);

        $this->actingAs($user)
            ->get(route('portal.applications.show', [$application, 'tahap' => 'wawancara']))
            ->assertOk()
            ->assertSee('Terjadwal', false)
            ->assertSee('Ruang Wawancara', false);
    }

    public function test_verified_data_locked_for_applicant(): void
    {
        $user = $this->applicant();
        $program = $this->program();
        $app = PPDBRegistration::create(['name' => 'Locked', 'gender' => 'laki-laki', 'program_id' => $program->id, 'applicant_account_id' => $user->id, 'period_id' => $this->period()->id, 'application_status' => ApplicationStatus::Verified, 'status' => 'pending', 'source' => 'applicant']);
        $this->actingAs($user);
        $this->put(route('portal.applications.update', $app), ['name' => 'Changed', 'gender' => 'laki-laki', 'program_id' => $program->id])->assertForbidden();
        $this->post(route('portal.documents.upload', [$app, 'kk']), ['file' => UploadedFile::fake()->create('kk.pdf', 100, 'application/pdf')])->assertForbidden();
    }

    public function test_only_document_requested_for_revision_is_unlocked_after_submission(): void
    {
        Storage::fake('ppdb_private');
        $user = $this->applicant();
        $app = PPDBRegistration::create([
            'name' => 'Koreksi Dokumen',
            'gender' => 'laki-laki',
            'program_id' => $this->program()->id,
            'applicant_account_id' => $user->id,
            'period_id' => $this->period()->id,
            'application_status' => ApplicationStatus::Submitted,
            'status' => 'pending',
            'source' => 'applicant',
        ]);
        \App\Services\DocumentService::ensurePlaceholders($app);
        $kk = $app->documents()->where('type', 'kk')->first();
        $akta = $app->documents()->where('type', 'akta')->first();
        $kk->update(['status' => 'needs_revision', 'path' => 'old/kk.pdf', 'admin_note' => 'Foto kurang jelas.']);
        $akta->update(['status' => 'valid', 'path' => 'old/akta.pdf']);

        $page = $this->actingAs($user)->get(route('portal.applications.show', [$app, 'tahap' => 'dokumen']));
        $page->assertOk()
            ->assertSee('Ganti Kartu Keluarga (KK)', false)
            ->assertDontSee('Ganti Akta Kelahiran', false);

        $this->actingAs($user)->post(
            route('portal.documents.upload', [$app, 'kk']),
            ['file' => UploadedFile::fake()->create('kk-baru.pdf', 200, 'application/pdf')]
        )->assertRedirect();
        $this->assertSame('replaced', $kk->fresh()->status->value);

        $this->actingAs($user)->post(
            route('portal.documents.upload', [$app, 'akta']),
            ['file' => UploadedFile::fake()->create('akta-baru.pdf', 200, 'application/pdf')]
        )->assertForbidden();
        $this->assertSame('valid', $akta->fresh()->status->value);
    }

    public function test_portal_uses_stage_specific_next_action_and_has_no_locked_change_request(): void
    {
        $user = $this->applicant();
        $app = PPDBRegistration::create(['name' => 'Tahap Jelas', 'gender' => 'laki-laki', 'program_id' => $this->program()->id, 'applicant_account_id' => $user->id, 'period_id' => $this->period()->id, 'source' => 'applicant']);
        \App\Services\DocumentService::ensurePlaceholders($app);

        $page = $this->actingAs($user)->get(route('portal.applications.show', $app))->assertOk();
        $page->assertSee('Lanjutkan Data Pendaftaran', false);
        $page = $this->actingAs($user)->get(route('portal.applications.show', [$app, 'tahap' => 'dokumen']))->assertOk();
        $page->assertSee('Pilih file untuk langsung mengunggah', false);
        $page->assertDontSee('Minta Perubahan Data Terkunci', false)->assertDontSee('Simpan Pengganti', false)->assertDontSee('Unggah Dokumen</button>', false);
        $this->assertFalse(\Illuminate\Support\Facades\Route::has('portal.applications.change'));
    }

    public function test_admin_document_review_json_autosave_is_authoritative(): void
    {
        $user = $this->applicant();
        $app = PPDBRegistration::create(['name' => 'Review Dok', 'gender' => 'laki-laki', 'program_id' => $this->program()->id, 'applicant_account_id' => $user->id, 'period_id' => $this->period()->id, 'source' => 'applicant']);
        \App\Services\DocumentService::ensurePlaceholders($app);
        $doc = $app->documents()->where('type', 'kk')->first();
        $admin = $this->admin();

        $this->actingAs($admin)->postJson(route('admin.documents.review', $doc), ['status' => 'needs_revision', 'admin_note' => 'Foto KK terpotong.'])->assertOk()->assertJson(['status' => 'needs_revision']);
        $this->assertDatabaseHas('application_documents', ['id' => $doc->id, 'status' => 'needs_revision', 'admin_note' => 'Foto KK terpotong.']);
        $this->actingAs($admin)->postJson(route('admin.documents.review', $doc), ['status' => 'needs_revision', 'admin_note' => ''])->assertUnprocessable();
    }

    public function test_document_upload_and_private_preview(): void
    {
        Storage::fake('ppdb_private');
        $user = $this->applicant();
        $other = $this->applicant();
        $program = $this->program();
        $app = PPDBRegistration::create(['name' => 'Doc', 'gender' => 'laki-laki', 'program_id' => $program->id, 'applicant_account_id' => $user->id, 'period_id' => $this->period()->id, 'source' => 'applicant']);

        $this->actingAs($user)->post(route('portal.documents.upload', [$app, 'kk']), ['file' => UploadedFile::fake()->create('kk.pdf', 200, 'application/pdf')])->assertRedirect();
        $doc = $app->documents()->where('type', 'kk')->first();
        $this->assertNotNull($doc->path);

        $this->actingAs($other)->get(route('portal.documents.preview', $doc))->assertForbidden();
    }

    public function test_email_failure_does_not_rollback_decision(): void
    {
        // Mailer log tidak pernah throw; simulasi: keputusan tetap tersimpan + log email pending/failed/sent
        $admin = User::factory()->create(['is_admin' => true, 'is_superadmin' => true, 'is_active' => true]);
        $program = $this->program();
        $period = $this->period();
        $user = $this->applicant();
        $app = PPDBRegistration::create(['name' => 'Decide', 'gender' => 'laki-laki', 'program_id' => $program->id, 'applicant_account_id' => $user->id, 'period_id' => $period->id, 'application_status' => ApplicationStatus::WaitingDecision, 'status' => 'pending', 'source' => 'applicant']);

        $this->actingAs($admin)->post(route('admin.registrations.decide', $app), ['result' => 'passed', 'confirm' => 1])->assertRedirect();
        $this->assertEquals('passed', $app->fresh()->decision->result->value);
        $this->actingAs($admin)->post(route('admin.registrations.release', $app))->assertRedirect();
        $this->assertNotNull($app->fresh()->decision->released_at);
    }
}
