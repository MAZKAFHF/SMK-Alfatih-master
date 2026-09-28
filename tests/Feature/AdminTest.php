<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Models\PPDBRegistration;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
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
            'application_status' => ApplicationStatus::Passed,
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

    public function test_admin_can_delete_registration(): void
    {
        $registration = $this->registration();

        $this->actingAs($this->admin())
            ->delete(route('admin.registrations.destroy', $registration))
            ->assertRedirect(route('admin.registrations.index'))
            ->assertSessionHas('success');

        $this->assertSoftDeleted('ppdb_registrations', ['id' => $registration->id]);
    }

    public function test_mass_delete_route_and_button_are_removed(): void
    {
        $this->registration();
        $this->assertFalse(\Illuminate\Support\Facades\Route::has('admin.registrations.destroy-all'));
        $this->actingAs($this->superadmin())->get(route('admin.registrations.index'))->assertOk()->assertDontSee('Hapus Semua', false);
        $this->assertDatabaseCount('ppdb_registrations', 1);
    }

    public function test_permanent_delete_removes_private_documents_and_revisions(): void
    {
        Storage::fake('ppdb_private');
        $registration = $this->registration();
        $document = \App\Models\ApplicationDocument::create([
            'application_id' => $registration->id, 'type' => 'kk', 'status' => 'uploaded',
            'disk' => 'ppdb_private', 'path' => 'period-test/app-test/current.pdf', 'version' => 2,
        ]);
        \App\Models\DocumentRevision::create(['document_id' => $document->id, 'path' => 'period-test/app-test/old.pdf', 'version' => 1]);
        Storage::disk('ppdb_private')->put($document->path, 'current');
        Storage::disk('ppdb_private')->put('period-test/app-test/old.pdf', 'old');
        $registration->delete();

        $this->actingAs($this->superadmin())->delete(route('admin.registrations.force-delete', $registration->id))->assertRedirect();
        Storage::disk('ppdb_private')->assertMissing($document->path);
        Storage::disk('ppdb_private')->assertMissing('period-test/app-test/old.pdf');
        $this->assertDatabaseMissing('ppdb_registrations', ['id' => $registration->id]);
    }

    public function test_admin_work_queue_lists_actionable_application(): void
    {
        $registration = $this->registration();
        $registration->update(['application_status' => ApplicationStatus::Submitted]);
        $this->actingAs($this->admin())->get(route('admin.work-queue.index'))
            ->assertOk()->assertSee('Antrean Kerja')->assertSee($registration->registration_number);
    }

}
