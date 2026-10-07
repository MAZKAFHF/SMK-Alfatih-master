<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Models\InterviewSlot;
use App\Models\PpdbPeriod;
use App\Models\PPDBRegistration;
use App\Models\Program;
use App\Models\User;
use App\Services\DecisionService;
use App\Services\DocumentService;
use App\Services\InterviewCompletion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * REGRESI BUG: keputusan Lulus -> Belum Lulus diblokir dengan pesan
 * "Keputusan hanya dapat ditetapkan setelah wawancara selesai"
 * padahal wawancara sudah selesai, dan baru bisa setelah re-save interview.
 *
 * Akar: DecisionService hanya mengizinkan waiting_decision/interviewed,
 * sehingga setelah keputusan pertama (passed) edit kedua selalu ditolak.
 * Re-save interview me-reset status ke waiting_decision sehingga lolos semu.
 */
class InterviewDecisionRegressionTest extends TestCase
{
    use RefreshDatabase;

    private function period(): PpdbPeriod
    {
        return PpdbPeriod::first() ?? PpdbPeriod::create(['academic_year' => '2026/2027', 'is_open' => true]);
    }

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true, 'is_superadmin' => true, 'is_active' => true]);
    }

    private function applicant(): User
    {
        return User::factory()->create(['is_admin' => false, 'is_applicant' => true, 'is_active' => true, 'email_verified_at' => now()]);
    }

    private function makeAppWithInterview(User $user, Program $program, PpdbPeriod $period): PPDBRegistration
    {
        $base = PpdbFinalSubmissionTest::completeData($program->id);
        $app = PPDBRegistration::create(array_merge($base, [
            'applicant_account_id' => $user->id, 'period_id' => $period->id, 'source' => 'applicant',
            'application_status' => ApplicationStatus::Verified, 'status' => 'pending',
        ]));
        DocumentService::ensurePlaceholders($app);

        return $app->fresh();
    }

    private function completeInterviewViaHttp(User $admin, PPDBRegistration $app): void
    {
        $slot = InterviewSlot::create([
            'period_id' => $app->period_id, 'date' => now('Asia/Jakarta')->addDay()->toDateString(),
            'start_time' => '08:00', 'capacity' => 5, 'status' => 'active',
        ]);
        $this->actingAs($app->account ?? $this->applicant())->post(route('portal.slots.book', $app), ['slot_id' => $slot->id]);
        $slot->update(['date' => now('Asia/Jakarta')->subDay()->toDateString()]);
        $appt = $app->fresh()->appointment;
        $this->actingAs($admin)->post(route('admin.appointments.complete', $appt), [
            'attendance' => 'attended',
            'interview_notes' => 'Komunikatif.',
            'tahfizh_notes' => 'Lancar.',
            'tahsin_notes' => 'Baik.',
            'recommendation' => 'Layak.',
        ])->assertRedirect();
    }

    public function test_original_bug_decision_edit_after_reload_without_resave(): void
    {
        $admin = $this->admin();
        $user = $this->applicant();
        $program = Program::factory()->create(['status' => 'active']);
        $period = $this->period();
        $app = $this->makeAppWithInterview($user, $program, $period);
        $this->completeInterviewViaHttp($admin, $app);

        // Keputusan pertama LULUS.
        $this->actingAs($admin)->post(route('admin.registrations.decide', $app), [
            'result' => 'passed', 'confirm' => 1,
        ])->assertRedirect();
        $this->assertEquals(ApplicationStatus::Passed, $app->fresh()->application_status);

        // Simulasi navigate away + reload fresh request.
        $fresh = PPDBRegistration::find($app->id);

        // Ubah LULUS -> Belum Lulus TANPA re-save interview.
        $this->actingAs($admin)->post(route('admin.registrations.decide', $fresh), [
            'result' => 'not_passed', 'confirm' => 1,
        ])->assertRedirect();

        $this->assertEquals(ApplicationStatus::NotPassed, $fresh->fresh()->application_status);
        $this->assertEquals('not_passed', $fresh->fresh()->decision->result->value);
    }

    public function test_persistence_across_fresh_request(): void
    {
        $admin = $this->admin();
        $user = $this->applicant();
        $program = Program::factory()->create(['status' => 'active']);
        $app = $this->makeAppWithInterview($user, $program, $this->period());
        $this->completeInterviewViaHttp($admin, $app);

        // Fresh request baru.
        $fresh = PPDBRegistration::with(['appointment.assessment'])->find($app->id);
        $this->assertTrue(InterviewCompletion::isCompleted($fresh));
    }

    public function test_real_incomplete_interview_remains_blocked(): void
    {
        $admin = $this->admin();
        $user = $this->applicant();
        $program = Program::factory()->create(['status' => 'active']);
        $period = $this->period();
        $app = $this->makeAppWithInterview($user, $program, $period);

        // Booking slot saja, belum complete.
        $slot = InterviewSlot::create([
            'period_id' => $period->id, 'date' => now('Asia/Jakarta')->addDay()->toDateString(),
            'start_time' => '09:00', 'capacity' => 5, 'status' => 'active',
        ]);
        $this->actingAs($user)->post(route('portal.slots.book', $app), ['slot_id' => $slot->id])->assertRedirect();
        $this->assertEquals(ApplicationStatus::Scheduled, $app->fresh()->application_status);

        $response = $this->actingAs($admin)->post(route('admin.registrations.decide', $app), [
            'result' => 'passed', 'confirm' => 1,
        ]);
        // Guard tetap menolak dengan pesan manusia.
        $response->assertSessionHas('error', 'Keputusan hanya dapat ditetapkan setelah wawancara selesai.');
        $this->assertNull($app->fresh()->decision);
    }

    public function test_existing_decision_can_be_edited(): void
    {
        $admin = $this->admin();
        $user = $this->applicant();
        $program = Program::factory()->create(['status' => 'active']);
        $app = $this->makeAppWithInterview($user, $program, $this->period());
        $this->completeInterviewViaHttp($admin, $app);

        $this->actingAs($admin)->post(route('admin.registrations.decide', $app), ['result' => 'passed', 'confirm' => 1])->assertRedirect();
        $this->actingAs($admin)->post(route('admin.registrations.decide', $app->fresh()), ['result' => 'not_passed', 'confirm' => 1])->assertRedirect();
        // Balik lagi juga boleh.
        $this->actingAs($admin)->post(route('admin.registrations.decide', $app->fresh()), ['result' => 'passed', 'confirm' => 1])->assertRedirect();
        $this->assertEquals('passed', $app->fresh()->decision->result->value);
    }

    public function test_historical_record_with_decision_is_recognized(): void
    {
        $admin = $this->admin();
        $user = $this->applicant();
        $program = Program::factory()->create(['status' => 'active']);
        $period = $this->period();
        $app = $this->makeAppWithInterview($user, $program, $period);
        $this->completeInterviewViaHttp($admin, $app);
        $this->actingAs($admin)->post(route('admin.registrations.decide', $app), ['result' => 'passed', 'confirm' => 1])->assertRedirect();

        // Simulasi record lama: appointment hilang (mis. sebelum refactor),
        // tetapi decision + passed tersisa. Kanonis harus tetap izinkan edit
        // karena decision membuktikan wawancara pernah selesai.
        $fresh = $app->fresh();
        $fresh->appointment->delete();
        $reloaded = PPDBRegistration::with(['appointment.assessment', 'decision'])->find($app->id);
        $this->assertTrue(InterviewCompletion::isCompleted($reloaded));

        $this->actingAs($admin)->post(route('admin.registrations.decide', $reloaded), ['result' => 'not_passed', 'confirm' => 1])->assertRedirect();
        $this->assertEquals('not_passed', $reloaded->fresh()->decision->result->value);
    }

    public function test_ambiguous_legacy_record_is_not_falsely_completed(): void
    {
        $user = $this->applicant();
        $program = Program::factory()->create(['status' => 'active']);
        $app = $this->makeAppWithInterview($user, $program, $this->period());
        // Status masih verified, tanpa appointment/assessment/decision.
        $this->assertFalse(InterviewCompletion::isCompleted($app->fresh()));
    }

    public function test_no_show_does_not_unlock_decision(): void
    {
        $admin = $this->admin();
        $user = $this->applicant();
        $program = Program::factory()->create(['status' => 'active']);
        $period = $this->period();
        $app = $this->makeAppWithInterview($user, $program, $period);
        $slot = InterviewSlot::create([
            'period_id' => $period->id, 'date' => now('Asia/Jakarta')->addDay()->toDateString(),
            'start_time' => '10:00', 'capacity' => 5, 'status' => 'active',
        ]);
        $this->actingAs($user)->post(route('portal.slots.book', $app), ['slot_id' => $slot->id])->assertRedirect();
        $appt = $app->fresh()->appointment;
        $this->actingAs($admin)->post(route('admin.appointments.complete', $appt), ['attendance' => 'no_show'])->assertRedirect();

        $this->assertFalse(InterviewCompletion::isCompleted($app->fresh()));
        $response = $this->actingAs($admin)->post(route('admin.registrations.decide', $app->fresh()), ['result' => 'passed', 'confirm' => 1]);
        $response->assertSessionHas('error');
    }
}
