<?php

namespace Tests\Feature;

use App\Models\PPDBRegistration;
use App\Models\PpdbPeriod;
use App\Models\Program;
use App\Models\User;
use App\Services\PpdbAvailability;
use App\Services\PpdbContext;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class PpdbApplicationWindowTest extends TestCase
{
    use RefreshDatabase;

    private Program $program;

    protected function setUp(): void
    {
        parent::setUp();
        $this->program = Program::factory()->create(['status' => 'active']);
    }

    private function period(string $state, ?int $quota = 10): ?PpdbPeriod
    {
        PpdbPeriod::query()->delete();
        PpdbContext::flush();
        $now = CarbonImmutable::now('Asia/Jakarta');

        if ($state === PpdbAvailability::NO_PERIOD) {
            return null;
        }

        $attributes = match ($state) {
            PpdbAvailability::UPCOMING => [
                'status' => 'upcoming', 'opens_at' => $now->addDay(), 'closes_at' => $now->addDays(2),
            ],
            PpdbAvailability::CLOSED => [
                'status' => 'closed', 'opens_at' => $now->subDays(2), 'closes_at' => $now->subDay(),
            ],
            default => [
                'status' => 'open', 'opens_at' => $now->subDay(), 'closes_at' => $now->addDay(),
            ],
        };

        $period = PpdbPeriod::create(array_merge([
            'academic_year' => 'WINDOW-'.strtoupper($state),
            'quota' => $quota,
            'is_open' => $state !== PpdbAvailability::CLOSED,
            'is_active' => in_array($state, [PpdbAvailability::OPEN, PpdbAvailability::FULL, PpdbAvailability::UPCOMING], true),
            'is_archived' => false,
        ], $attributes));

        if ($state === PpdbAvailability::FULL) {
            PPDBRegistration::create([
                'name' => 'Pengisi Kuota', 'gender' => 'laki-laki', 'program_id' => $this->program->id,
                'period_id' => $period->id, 'academic_year' => $period->academic_year,
                'source' => 'admin_manual', 'application_status' => 'submitted', 'status' => 'pending',
            ]);
        }

        PpdbContext::flush();

        return $period->fresh();
    }

    private function assertAccountAndApplicationRule(string $state, bool $allowed): void
    {
        $this->period($state, $state === PpdbAvailability::FULL ? 1 : 10);
        $email = strtolower($state).'@window.test';

        $this->post(route('portal.register.store'), [
            'name' => 'Orang Tua '.$state,
            'email' => $email,
            'password' => 'Pass12345',
            'password_confirmation' => 'Pass12345',
        ])->assertRedirect(route('portal.dashboard'));

        $user = User::where('email', $email)->firstOrFail();
        $this->assertTrue($user->is_applicant);

        $verificationUrl = URL::temporarySignedRoute(
            'portal.verification.verify', now()->addHour(), ['id' => $user->id, 'hash' => sha1($user->email)]
        );
        $this->get($verificationUrl)->assertRedirect(route('portal.dashboard'));
        $this->assertNotNull($user->fresh()->email_verified_at);

        $this->post(route('portal.logout'))->assertRedirect(route('portal.login'));
        $this->post(route('portal.login.store'), ['email' => $email, 'password' => 'Pass12345'])
            ->assertRedirect(route('portal.dashboard'));
        $this->assertAuthenticatedAs($user);

        $get = $this->get(route('portal.applications.create'));
        $allowed ? $get->assertOk() : $get->assertRedirect(route('portal.dashboard'))->assertSessionHas('error');

        $before = PPDBRegistration::count();
        $post = $this->post(route('portal.applications.store'), [
            'name' => 'Anak '.$state, 'gender' => 'laki-laki', 'program_id' => $this->program->id,
        ]);

        if ($allowed) {
            $post->assertRedirect();
            $this->assertSame($before + 1, PPDBRegistration::count());
        } else {
            $post->assertRedirect(route('portal.dashboard'))->assertSessionHas('error');
            $this->assertSame($before, PPDBRegistration::count());
        }
    }

    public function test_no_period_allows_account_but_blocks_new_student(): void
    {
        $this->assertAccountAndApplicationRule(PpdbAvailability::NO_PERIOD, false);
    }

    public function test_upcoming_allows_account_but_blocks_new_student(): void
    {
        $this->assertAccountAndApplicationRule(PpdbAvailability::UPCOMING, false);
    }

    public function test_open_allows_account_and_new_student(): void
    {
        $this->assertAccountAndApplicationRule(PpdbAvailability::OPEN, true);
    }

    public function test_full_allows_account_but_blocks_new_student(): void
    {
        $this->assertAccountAndApplicationRule(PpdbAvailability::FULL, false);
    }

    public function test_closed_allows_account_but_blocks_new_student(): void
    {
        $this->assertAccountAndApplicationRule(PpdbAvailability::CLOSED, false);
    }

    public function test_direct_post_closed_is_human_safe_and_does_not_mutate_database(): void
    {
        $this->period(PpdbAvailability::CLOSED);
        $user = User::factory()->create(['is_admin' => false, 'is_applicant' => true, 'is_active' => true]);
        $before = PPDBRegistration::count();

        $this->actingAs($user)->post(route('portal.applications.store'), [
            'name' => 'Tempa Langsung', 'gender' => 'laki-laki', 'program_id' => $this->program->id,
        ])->assertRedirect(route('portal.dashboard'))
            ->assertSessionHas('error', 'Periode pendaftaran telah ditutup. Anda tetap dapat masuk ke Portal untuk melihat pendaftaran yang sudah ada.');

        $this->assertSame($before, PPDBRegistration::count());
    }

    public function test_stale_open_form_is_rejected_at_closing_boundary(): void
    {
        PpdbPeriod::query()->delete();
        $opens = CarbonImmutable::parse('2026-09-25 14:41:00', 'Asia/Jakarta');
        $closes = CarbonImmutable::parse('2026-09-26 12:30:00', 'Asia/Jakarta');
        $period = PpdbPeriod::create([
            'academic_year' => 'STALE', 'status' => 'open', 'opens_at' => $opens, 'closes_at' => $closes,
            'quota' => 10, 'is_open' => true, 'is_active' => true, 'is_archived' => false,
        ]);
        PpdbContext::flush();
        $user = User::factory()->create(['is_admin' => false, 'is_applicant' => true, 'is_active' => true]);

        $this->travelTo($opens);
        $this->actingAs($user)->get(route('portal.applications.create'))->assertOk();
        $this->travelTo($closes);
        $this->post(route('portal.applications.store'), [
            'name' => 'Stale Page', 'gender' => 'perempuan', 'program_id' => $this->program->id,
        ])->assertRedirect(route('portal.dashboard'))->assertSessionHas('error');

        $this->assertDatabaseMissing('ppdb_registrations', ['name' => 'Stale Page', 'period_id' => $period->id]);
        $this->travelBack();
    }

    public function test_opening_boundary_blocks_one_second_before_and_allows_exactly_at_open(): void
    {
        PpdbPeriod::query()->delete();
        $opens = CarbonImmutable::parse('2026-09-25 14:41:00', 'Asia/Jakarta');
        PpdbPeriod::create([
            'academic_year' => 'OPEN-BOUNDARY', 'status' => 'upcoming',
            'opens_at' => $opens, 'closes_at' => $opens->addDay(),
            'quota' => 10, 'is_open' => true, 'is_active' => true, 'is_archived' => false,
        ]);
        PpdbContext::flush();
        $user = User::factory()->create(['is_admin' => false, 'is_applicant' => true, 'is_active' => true]);
        $this->actingAs($user);

        $this->travelTo($opens->subSecond());
        $this->post(route('portal.applications.store'), [
            'name' => 'Terlalu Awal', 'gender' => 'laki-laki', 'program_id' => $this->program->id,
        ])->assertRedirect(route('portal.dashboard'))->assertSessionHas('error');
        $this->assertDatabaseMissing('ppdb_registrations', ['name' => 'Terlalu Awal']);

        $this->travelTo($opens);
        $this->post(route('portal.applications.store'), [
            'name' => 'Tepat Waktu', 'gender' => 'perempuan', 'program_id' => $this->program->id,
        ])->assertRedirect();
        $this->assertDatabaseHas('ppdb_registrations', ['name' => 'Tepat Waktu', 'source' => 'applicant']);
        $this->travelBack();
    }

    public function test_cached_open_period_cannot_bypass_authoritative_closed_status(): void
    {
        $period = $this->period(PpdbAvailability::OPEN);
        $user = User::factory()->create(['is_admin' => false, 'is_applicant' => true, 'is_active' => true]);
        $this->assertSame($period->id, PpdbContext::current()?->id); // prime cache
        $period->update(['status' => 'closed', 'is_active' => false]); // sengaja tanpa flush

        $this->actingAs($user)->post(route('portal.applications.store'), [
            'name' => 'Cache Bypass', 'gender' => 'laki-laki', 'program_id' => $this->program->id,
        ])->assertRedirect(route('portal.dashboard'))->assertSessionHas('error');

        $this->assertDatabaseMissing('ppdb_registrations', ['name' => 'Cache Bypass']);
    }

    public function test_multiple_students_stop_immediately_after_close_and_existing_remains_visible(): void
    {
        $period = $this->period(PpdbAvailability::OPEN);
        $user = User::factory()->create(['is_admin' => false, 'is_applicant' => true, 'is_active' => true]);
        $this->actingAs($user);

        foreach (['Anak A', 'Anak B'] as $name) {
            $this->post(route('portal.applications.store'), [
                'name' => $name, 'gender' => 'laki-laki', 'program_id' => $this->program->id,
            ])->assertRedirect();
        }
        $first = $user->applications()->where('name', 'Anak A')->firstOrFail();
        $period->update(['status' => 'closed', 'is_active' => false]);

        $this->post(route('portal.applications.store'), [
            'name' => 'Anak C', 'gender' => 'perempuan', 'program_id' => $this->program->id,
        ])->assertRedirect(route('portal.dashboard'))->assertSessionHas('error');
        $this->assertSame(2, $user->applications()->count());
        $this->get(route('portal.applications.show', $first))->assertOk()->assertSee('Anak A');
    }

    public function test_full_quota_blocks_draft_creation_without_affecting_existing_application(): void
    {
        $period = $this->period(PpdbAvailability::FULL, 1);
        $existing = PPDBRegistration::where('period_id', $period->id)->firstOrFail();
        $user = User::factory()->create(['is_admin' => false, 'is_applicant' => true, 'is_active' => true]);

        $this->actingAs($user)->post(route('portal.applications.store'), [
            'name' => 'Melebihi Kuota', 'gender' => 'laki-laki', 'program_id' => $this->program->id,
        ])->assertRedirect(route('portal.dashboard'))->assertSessionHas('error', 'Kuota PPDB untuk periode ini telah terpenuhi.');

        $this->assertDatabaseHas('ppdb_registrations', ['id' => $existing->id]);
        $this->assertDatabaseMissing('ppdb_registrations', ['name' => 'Melebihi Kuota']);
    }

    public function test_removed_public_form_redirects_without_mutating_database(): void
    {
        $this->period(PpdbAvailability::CLOSED);
        $before = PPDBRegistration::count();

        $this->get('/ppdb/siswa')->assertStatus(301)->assertRedirect(route('portal.register'));
        $this->post('/ppdb', [])->assertStatus(303)->assertRedirect(route('portal.register'));
        $this->assertSame($before, PPDBRegistration::count());
    }
}
