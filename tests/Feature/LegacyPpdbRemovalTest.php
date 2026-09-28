<?php

namespace Tests\Feature;

use App\Models\PPDBRegistration;
use App\Models\PpdbPeriod;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class LegacyPpdbRemovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_and_portal_surfaces_expose_only_account_based_flow(): void
    {
        foreach (['/', '/ppdb'] as $url) {
            $this->get($url)->assertOk()
                ->assertDontSee('Cek Status Lama')
                ->assertDontSee('Status Lama')
                ->assertDontSee('formulir cepat');
        }

        $user = User::factory()->create(['is_applicant' => true, 'is_admin' => false]);
        $this->actingAs($user)->get(route('portal.dashboard'))->assertOk()
            ->assertDontSee('Cek Status Lama')
            ->assertDontSee('registration_number', false);
    }

    public function test_old_public_urls_only_redirect_and_cannot_create_records(): void
    {
        $before = PPDBRegistration::count();

        $this->get('/ppdb/status?registration_number=PPDB-2026-00001')
            ->assertStatus(301)->assertRedirect(route('portal.login'));
        $this->get('/ppdb/siswa')->assertStatus(301)->assertRedirect(route('portal.register'));
        $this->post('/ppdb', ['name' => 'Tidak Boleh Tersimpan'])
            ->assertStatus(303)->assertRedirect(route('portal.register'));

        $this->assertSame($before, PPDBRegistration::count());
        $this->assertFalse(Route::has('ppdb.status'));
        $this->assertFalse(Route::has('ppdb.siswa'));
        $this->assertFalse(Route::has('ppdb.store'));
    }

    public function test_admin_has_no_old_status_control_or_route(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'is_superadmin' => true]);
        $registration = PPDBRegistration::factory()->create([
            'program_id' => Program::factory()->create(['status' => 'active']),
        ]);

        $this->actingAs($admin)->get(route('admin.registrations.show', $registration))
            ->assertOk()->assertDontSee('Status Lama')->assertDontSee('Simpan Status Lama');
        $this->assertFalse(Route::has('admin.registrations.update'));
        $this->assertFalse(Route::has('admin.settings.ppdb'));
    }

    public function test_completed_period_records_remain_available_by_account_and_admin_period(): void
    {
        $period = PpdbPeriod::query()->firstOrFail();
        $period->update(['status' => PpdbPeriod::STATUS_CLOSED, 'is_open' => false]);
        $user = User::factory()->create(['is_applicant' => true, 'is_admin' => false]);
        $admin = User::factory()->create(['is_admin' => true]);
        $registration = PPDBRegistration::factory()->create([
            'name' => 'Riwayat Aman',
            'period_id' => $period->id,
            'applicant_account_id' => $user->id,
            'program_id' => Program::factory()->create(['status' => 'active']),
        ]);

        $this->actingAs($user)->get(route('portal.dashboard'))
            ->assertOk()->assertSee('Riwayat Aman')->assertSee('Riwayat');
        $this->actingAs($admin)->get(route('admin.registrations.index', ['period_id' => $period->id]))
            ->assertOk()->assertSee('Riwayat Aman');
        $this->assertDatabaseHas('ppdb_registrations', ['id' => $registration->id]);
    }
}
