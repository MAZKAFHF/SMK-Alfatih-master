<?php

namespace Tests\Feature;

use App\Enums\RegistrationStatus;
use App\Models\PPDBRegistration;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    private function superadmin(): User
    {
        return User::factory()->create(['is_admin' => true, 'is_superadmin' => true]);
    }

    private function nonAdmin(): User
    {
        return User::factory()->create(['is_admin' => false]);
    }

    private function registration(): PPDBRegistration
    {
        return PPDBRegistration::factory()->create([
            'program_id' => Program::factory()->create(['status' => 'active']),
        ]);
    }

    public function test_guest_is_redirected_to_login_when_accessing_dashboard(): void
    {
        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_guest_can_view_login_page(): void
    {
        $this->get(route('admin.login'))
            ->assertOk()
            ->assertSee('Masuk Admin');
    }

    public function test_non_admin_cannot_access_admin_pages(): void
    {
        $this->actingAs($this->nonAdmin())
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_admin_can_login_with_valid_credentials(): void
    {
        $admin = $this->admin();

        $this->post(route('admin.login.attempt'), [
            'email' => $admin->email,
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin);
    }

    public function test_admin_login_rejects_invalid_credentials(): void
    {
        $this->admin();

        $this->post(route('admin.login.attempt'), [
            'email' => 'admin@smkalfatih.sch.id',
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_admin_can_logout(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('admin.logout'))
            ->assertRedirect(route('admin.login'));

        $this->assertGuest();
    }

    public function test_admin_dashboard_shows_stats_and_recent_registrations(): void
    {
        $this->registration();
        PPDBRegistration::factory()->create([
            'status' => RegistrationStatus::Accepted,
            'program_id' => Program::factory()->create(['status' => 'active']),
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Total Pendaftar')
            ->assertSee('2');
    }

    public function test_admin_can_filter_and_view_registrations(): void
    {
        $this->registration();

        $this->actingAs($this->admin())
            ->get(route('admin.registrations.index'))
            ->assertOk();
    }

    public function test_admin_can_update_registration_status(): void
    {
        $registration = $this->registration();

        $this->actingAs($this->admin())
            ->put(route('admin.registrations.update', $registration), [
                'status' => 'accepted',
            ])->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('ppdb_registrations', [
            'id' => $registration->id,
            'status' => 'accepted',
        ]);
    }

    public function test_admin_can_delete_registration(): void
    {
        $registration = $this->registration();

        $this->actingAs($this->admin())
            ->delete(route('admin.registrations.destroy', $registration))
            ->assertRedirect(route('admin.registrations.index'))
            ->assertSessionHas('success');

        $this->assertSoftDeleted('ppdb_registrations', ['id' => $registration->id]);
    }

    public function test_admin_can_delete_all_registrations(): void
    {
        $this->registration();
        $this->registration();

        // Mass delete now requires superadmin + password + confirmation + soft delete
        $superadmin = $this->superadmin();

        $this->actingAs($superadmin)
            ->delete(route('admin.registrations.destroy-all'), [
                'password' => 'password',
                'confirmation' => 'HAPUS SEMUA',
            ])
            ->assertRedirect(route('admin.registrations.index'))
            ->assertSessionHas('success');

        // Soft deleted so count without trashed is 0, but raw count still 2
        $this->assertEquals(0, PPDBRegistration::count());
        $this->assertEquals(2, PPDBRegistration::withTrashed()->count());
    }

    public function test_delete_all_warns_when_there_is_no_data(): void
    {
        $superadmin = $this->superadmin();
        $this->actingAs($superadmin)
            ->delete(route('admin.registrations.destroy-all'), [
                'password' => 'password',
                'confirmation' => 'HAPUS SEMUA',
            ])
            ->assertRedirect()
            ->assertSessionHas('warning');
    }

    public function test_guest_cannot_delete_all_registrations(): void
    {
        $this->registration();

        $this->delete(route('admin.registrations.destroy-all'))
            ->assertRedirect(route('admin.login'));

        $this->assertDatabaseCount('ppdb_registrations', 1);
    }

    public function test_guest_cannot_update_registration_status(): void
    {
        $registration = $this->registration();

        $this->put(route('admin.registrations.update', $registration), ['status' => 'accepted'])
            ->assertRedirect(route('admin.login'));

        $this->assertDatabaseHas('ppdb_registrations', ['id' => $registration->id, 'status' => 'pending']);
    }
}
