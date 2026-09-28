<?php

namespace Tests\Feature;

use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * REGRESI: tidak ada kunci validasi mentah (validation.*, auth.*, passwords.*)
 * atau nama field snake_case yang bocor ke UI. Semua pesan Bahasa Indonesia.
 */
class ValidationLanguageTest extends TestCase
{
    use RefreshDatabase;

    /** @return string[] */
    private function sessionMessages(): array
    {
        /** @var \Illuminate\Support\ViewErrorBag $bag */
        $bag = session('errors');

        return $bag ? $bag->all() : [];
    }

    private function assertHuman(array $messages): void
    {
        $this->assertNotEmpty($messages, 'Harus ada pesan error untuk diuji.');
        foreach ($messages as $msg) {
            $this->assertDoesNotMatchRegularExpression('/^(validation|auth|passwords)\./', $msg, "Kunci mentah bocor: {$msg}");
            $this->assertDoesNotMatchRegularExpression('/^[a-z_]+\.[a-z_]+$/', $msg, "Format kunci bocor: {$msg}");
        }
    }

    public function test_password_mismatch_regression(): void
    {
        // Bug asli: menampilkan "validation.confirmed".
        $response = $this->from('/portal/daftar')->post('/portal/daftar', [
            'name' => 'Orang Tua',
            'email' => 'ortu@example.com',
            'password' => 'abc12345',
            'password_confirmation' => 'different123',
        ]);
        $response->assertSessionHasErrors(['password']);
        $this->assertHuman($this->sessionMessages());

        // Halaman render: pesan manusiawi, tanpa kunci mentah.
        $page = $this->followRedirects($response);
        $page->assertDontSee('validation.confirmed', false);
        $page->assertDontSee('validation.', false);
        $page->assertSee('Password dan konfirmasi password harus sama.', false);
    }

    public function test_single_error_does_not_duplicate_in_summary(): void
    {
        $response = $this->from('/portal/daftar')->post('/portal/daftar', [
            'name' => 'Orang Tua',
            'email' => 'bukan-email',
            'password' => 'abc12345',
            'password_confirmation' => 'abc12345',
        ]);
        $page = $this->followRedirects($response);
        // Satu error -> hanya error field, ringkasan disembunyikan.
        $page->assertDontSee('data-validation-summary', false);
        $page->assertDontSee('validation.', false);
    }

    public function test_multiple_errors_show_counted_summary(): void
    {
        $response = $this->from('/portal/daftar')->post('/portal/daftar', [
            'name' => '',
            'email' => 'bukan-email',
            'password' => 'pendek',
            'password_confirmation' => 'beda',
        ]);
        $this->assertHuman($this->sessionMessages());
        $page = $this->followRedirects($response);
        $page->assertSee('Ada 3 bagian yang perlu diperbaiki', false);
        $page->assertDontSee('validation.', false);
    }

    public function test_required_email_unique_min_rules_are_indonesian(): void
    {
        User::factory()->create(['email' => 'ada@example.com', 'is_admin' => false]);

        $this->post('/portal/daftar', ['name' => '', 'email' => '', 'password' => '', 'password_confirmation' => '']);
        $this->assertHuman($this->sessionMessages());

        $this->post('/portal/daftar', ['name' => 'X', 'email' => 'ada@example.com', 'password' => 'abc12345', 'password_confirmation' => 'abc12345']);
        $msgs = $this->sessionMessages();
        $this->assertHuman($msgs);
        $this->assertContains('Email tersebut sudah digunakan.', $msgs);

        $this->post('/portal/daftar', ['name' => 'X', 'email' => 'baru@example.com', 'password' => 'abc12', 'password_confirmation' => 'abc12']);
        $this->assertHuman($this->sessionMessages());
    }

    public function test_login_and_contact_errors_are_indonesian(): void
    {
        $this->post('/portal/masuk', ['email' => 'salah@example.com', 'password' => 'salah1234']);
        $this->assertHuman($this->sessionMessages());

        $this->post('/portal/masuk', ['email' => 'bukan-email', 'password' => 'x']);
        $this->assertHuman($this->sessionMessages());

        $this->post('/kontak', ['name' => '', 'email' => 'buruk', 'subject' => '', 'message' => '']);
        $this->assertHuman($this->sessionMessages());
    }

    public function test_portal_application_errors_are_indonesian(): void
    {
        $period = \App\Models\PpdbPeriod::query()->firstOrFail();
        $period->update(['status' => 'open', 'is_open' => true, 'status_override' => 'open']);
        \App\Services\PpdbContext::flush();
        $user = User::factory()->create(['is_applicant' => true, 'is_admin' => false]);

        $this->actingAs($user)->post(route('portal.applications.store'), []);
        $msgs = $this->sessionMessages();
        $this->assertHuman($msgs);
        $this->assertContains('Silakan pilih satu program keahlian.', $msgs);
    }

    public function test_admin_reset_password_mismatch_is_indonesian(): void
    {
        $response = $this->post('/admin/reset-password', [
            'token' => 'tok', 'email' => 'admin@x.id',
            'password' => 'Password123', 'password_confirmation' => 'Berbeda123',
        ]);
        $response->assertSessionHasErrors(['password']);
        $msgs = $this->sessionMessages();
        $this->assertHuman($msgs);
        $this->assertContains('Password dan konfirmasi password harus sama.', $msgs);
    }

    public function test_slot_and_interview_errors_are_indonesian(): void
    {
        $user = User::factory()->create(['is_admin' => false, 'is_applicant' => true, 'is_active' => true, 'email_verified_at' => now()]);
        $program = Program::factory()->create(['status' => 'active']);
        $app = \App\Models\PPDBRegistration::create([
            'name' => 'S', 'gender' => 'laki-laki', 'program_id' => $program->id,
            'applicant_account_id' => $user->id,
            'period_id' => \App\Models\PpdbPeriod::first()->id, 'source' => 'applicant',
        ]);

        $this->actingAs($user)->post(route('portal.slots.book', $app), ['slot_id' => 999999]);
        $this->assertHuman($this->sessionMessages());

        $slot = \App\Models\InterviewSlot::create([
            'period_id' => $app->period_id, 'date' => now('Asia/Jakarta')->addDay()->toDateString(),
            'start_time' => '08:00', 'capacity' => 2, 'booked_count' => 1, 'status' => 'active',
        ]);
        \App\Models\InterviewAppointment::create([
            'application_id' => $app->id, 'slot_id' => $slot->id, 'status' => 'scheduled',
        ]);
        $this->actingAs($user)->post(route('portal.reschedule.store', $app), ['new_slot_id' => 999999, 'reason' => 'x']);
        $msgs = $this->sessionMessages();
        $this->assertHuman($msgs);
        $this->assertContains('Tulis alasan minimal 10 karakter agar jelas.', $msgs);
    }
}
