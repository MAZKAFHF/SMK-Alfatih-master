<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Models\ApplicationDecision;
use App\Models\InterviewSlot;
use App\Models\PpdbPeriod;
use App\Models\PPDBRegistration;
use App\Models\Program;
use App\Models\RescheduleRequest;
use App\Models\StatusHistory;
use App\Models\User;
use App\Services\DecisionService;
use App\Services\DocumentService;
use App\Services\PpdbPeriodService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PpdbLogicHardeningTest extends TestCase
{
    use RefreshDatabase;

    private function period(string $year, string $status = 'open'): PpdbPeriod
    {
        return PpdbPeriod::updateOrCreate(['academic_year' => $year], [
            'status' => $status,
            'opens_at' => now()->subDay(),
            'closes_at' => now()->addDays(30),
            'is_active' => $status === 'open',
        ]);
    }

    private function applicant(): User
    {
        return User::factory()->create([
            'is_admin' => false, 'is_applicant' => true, 'is_active' => true,
            'email_verified_at' => now(),
        ]);
    }

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true, 'is_superadmin' => true, 'is_active' => true]);
    }

    private function application(User $user, PpdbPeriod $period, ApplicationStatus $status = ApplicationStatus::Verified): PPDBRegistration
    {
        return PPDBRegistration::create([
            'name' => 'Siswa Hardening',
            'gender' => 'laki-laki',
            'program_id' => Program::factory()->create(['status' => 'active'])->id,
            'applicant_account_id' => $user->id,
            'period_id' => $period->id,
            'application_status' => $status,
            'status' => $status->toLegacy(),
            'source' => 'applicant',
        ]);
    }

    private function slot(PpdbPeriod $period, string $time = '08:00'): InterviewSlot
    {
        return InterviewSlot::create([
            'period_id' => $period->id,
            'date' => now('Asia/Jakarta')->addDay()->toDateString(),
            'start_time' => $time,
            'capacity' => 5,
            'status' => 'active',
        ]);
    }

    public function test_direct_booking_cannot_cross_period_boundary(): void
    {
        $user = $this->applicant();
        $appPeriod = $this->period('2026/2027');
        $otherPeriod = $this->period('2027/2028');
        $app = $this->application($user, $appPeriod);

        $this->actingAs($user)->post(route('portal.slots.book', $app), [
            'slot_id' => $this->slot($otherPeriod)->id,
        ])->assertSessionHasErrors('slot');

        $this->assertDatabaseMissing('interview_appointments', ['application_id' => $app->id]);
        $this->assertEquals(ApplicationStatus::Verified, $app->fresh()->application_status);
    }

    public function test_reschedule_rejects_cross_period_and_duplicate_pending_requests(): void
    {
        $user = $this->applicant();
        $period = $this->period('2026/2027');
        $other = $this->period('2027/2028');
        $app = $this->application($user, $period);
        $current = $this->slot($period, '08:00');
        $replacement = $this->slot($period, '09:00');

        $this->actingAs($user)->post(route('portal.slots.book', $app), ['slot_id' => $current->id])->assertRedirect();
        $this->actingAs($user)->post(route('portal.reschedule.store', $app), [
            'reason' => 'Perlu mengganti jadwal karena agenda keluarga.',
            'new_slot_id' => $this->slot($other)->id,
        ])->assertSessionHasErrors('new_slot_id');

        $payload = ['reason' => 'Perlu mengganti jadwal karena agenda keluarga.', 'new_slot_id' => $replacement->id];
        $this->actingAs($user)->post(route('portal.reschedule.store', $app), $payload)->assertRedirect();
        $this->actingAs($user)->post(route('portal.reschedule.store', $app), $payload)->assertSessionHasErrors('new_slot_id');
        $this->assertSame(1, RescheduleRequest::where('appointment_id', $app->fresh()->appointment->id)->where('status', 'pending')->count());
    }

    public function test_reschedule_decision_is_single_use(): void
    {
        $user = $this->applicant();
        $admin = $this->admin();
        $period = $this->period('2026/2027');
        $app = $this->application($user, $period);
        $current = $this->slot($period, '08:00');
        $replacement = $this->slot($period, '09:00');
        $this->actingAs($user)->post(route('portal.slots.book', $app), ['slot_id' => $current->id]);
        $this->actingAs($user)->post(route('portal.reschedule.store', $app), [
            'reason' => 'Perlu mengganti jadwal karena agenda keluarga.', 'new_slot_id' => $replacement->id,
        ]);
        $request = RescheduleRequest::firstOrFail();

        $this->actingAs($admin)->post(route('admin.reschedules.decide', $request), ['decision' => 'approved'])->assertRedirect();
        $this->actingAs($admin)->post(route('admin.reschedules.decide', $request), ['decision' => 'approved'])->assertSessionHasErrors('decision');
        $this->assertEquals($replacement->id, $app->fresh()->appointment->slot_id);
    }

    public function test_document_review_requires_real_file_and_correct_workflow_stage(): void
    {
        Storage::fake('ppdb_private');
        $admin = $this->admin();
        $app = $this->application($this->applicant(), $this->period('2026/2027'), ApplicationStatus::Submitted);
        DocumentService::ensurePlaceholders($app);
        $doc = $app->documents()->where('type', 'kk')->firstOrFail();

        $this->actingAs($admin)->postJson(route('admin.documents.review', $doc), [
            'status' => 'valid',
        ])->assertUnprocessable();

        Storage::disk('ppdb_private')->put('hardening/kk.pdf', 'pdf');
        $doc->update(['path' => 'hardening/kk.pdf', 'status' => 'uploaded']);
        $app->update(['application_status' => ApplicationStatus::Passed, 'status' => ApplicationStatus::Passed->toLegacy()]);
        $this->actingAs($admin)->postJson(route('admin.documents.review', $doc->fresh()), [
            'status' => 'needs_revision', 'admin_note' => 'Tidak boleh terlambat.',
        ])->assertUnprocessable();
        $this->assertEquals('uploaded', $doc->fresh()->status->value);
    }

    public function test_completed_period_cannot_use_generic_open_or_close_transitions(): void
    {
        $period = $this->period('2026/2027', 'completed');
        $period->update(['results_released_at' => now(), 'operational_completed_at' => now()]);

        $this->expectException(ValidationException::class);
        PpdbPeriodService::open($period);
    }

    public function test_completed_period_cannot_be_closed_back_into_unlocked_state(): void
    {
        $period = $this->period('2026/2027', 'completed');
        $period->update(['results_released_at' => now(), 'operational_completed_at' => now()]);

        try {
            PpdbPeriodService::close($period);
            $this->fail('Expected lifecycle transition to be rejected.');
        } catch (ValidationException) {
            $this->assertEquals('completed', $period->fresh()->status);
            $this->assertTrue($period->fresh()->isLockedForOperations());
        }
    }

    public function test_interview_cannot_finish_early_or_flip_terminal_attendance(): void
    {
        $admin = $this->admin();
        $user = $this->applicant();
        $period = $this->period('2026/2027');
        $app = $this->application($user, $period);
        $slot = $this->slot($period);
        $this->actingAs($user)->post(route('portal.slots.book', $app), ['slot_id' => $slot->id]);
        $appointment = $app->fresh()->appointment;

        $this->actingAs($admin)->post(route('admin.appointments.complete', $appointment), ['attendance' => 'attended'])
            ->assertSessionHasErrors('attendance');
        $slot->update(['date' => now('Asia/Jakarta')->subDay()->toDateString()]);
        $this->actingAs($admin)->post(route('admin.appointments.complete', $appointment), ['attendance' => 'attended'])->assertRedirect();
        $this->actingAs($admin)->post(route('admin.appointments.complete', $appointment), ['attendance' => 'no_show'])
            ->assertSessionHasErrors('attendance');
        $this->assertEquals('attended', $appointment->fresh()->status->value);
        $this->assertEquals(ApplicationStatus::WaitingDecision, $app->fresh()->application_status);
    }

    public function test_result_release_is_idempotent(): void
    {
        $admin = $this->admin();
        $app = $this->application($this->applicant(), $this->period('2026/2027'), ApplicationStatus::Passed);
        $decision = ApplicationDecision::create([
            'application_id' => $app->id, 'result' => 'passed', 'decided_by' => $admin->id, 'decided_at' => now(),
        ]);

        $this->assertTrue(DecisionService::release($decision, $admin->id));
        $historyCount = StatusHistory::where('application_id', $app->id)->where('note', 'Hasil dirilis ke pendaftar')->count();
        $notificationCount = $app->account->notifications()->count();
        $this->assertFalse(DecisionService::release($decision->fresh(), $admin->id));
        $this->assertSame($historyCount, StatusHistory::where('application_id', $app->id)->where('note', 'Hasil dirilis ke pendaftar')->count());
        $this->assertSame($notificationCount, $app->account->notifications()->count());
    }

    public function test_slot_creation_uses_explicit_selected_period(): void
    {
        $admin = $this->admin();
        $selected = $this->period('2026/2027', 'closed');
        $this->period('2027/2028', 'open');

        $this->actingAs($admin)->post(route('admin.slots.store'), [
            'period_id' => $selected->id,
            'date' => now('Asia/Jakarta')->addDay()->format('Y-m-d'),
            'start_time' => '08:00', 'capacity' => 2,
        ])->assertRedirect();

        $this->assertDatabaseHas('interview_slots', ['period_id' => $selected->id, 'start_time' => '08:00']);
    }
}
