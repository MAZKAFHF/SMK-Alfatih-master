<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private function superadmin(): User
    {
        return User::factory()->create(['is_admin' => true, 'is_superadmin' => true]);
    }

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    public function test_guest_is_redirected_when_accessing_users_page(): void
    {
        $this->get(route('admin.users.index'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_regular_admin_cannot_access_users_management(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.users.index'))
            ->assertForbidden();
    }

    public function test_superadmin_can_view_users_management(): void
    {
        $superadmin = $this->superadmin();

        $this->actingAs($superadmin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee($superadmin->name);
    }

    public function test_admin_and_portal_accounts_are_separated_into_correct_tabs(): void
    {
        $superadmin = $this->superadmin();
        $admin = $this->admin();
        $applicant = User::factory()->create([
            'name' => 'Pemohon Portal',
            'is_admin' => false,
            'is_superadmin' => false,
            'is_applicant' => true,
        ]);

        $this->actingAs($superadmin)
            ->get(route('admin.users.index', ['tab' => 'admins']))
            ->assertOk()
            ->assertSee($admin->name)
            ->assertDontSee($applicant->name)
            ->assertSee('Akun Admin')
            ->assertSee('Pengguna Portal PPDB');

        $this->actingAs($superadmin)
            ->get(route('admin.users.index', ['tab' => 'portal']))
            ->assertOk()
            ->assertSee($applicant->name)
            ->assertDontSee($admin->email)
            ->assertSee('Pengguna Portal PPDB')
            ->assertSee('data-account-type="portal"', false)
            ->assertDontSee('data-account-type="admin"', false);
    }

    public function test_portal_account_cannot_be_promoted_by_tampering_with_update_request(): void
    {
        $superadmin = $this->superadmin();
        $applicant = User::factory()->create([
            'is_admin' => false,
            'is_superadmin' => false,
            'is_applicant' => true,
        ]);

        $this->actingAs($superadmin)
            ->put(route('admin.users.update', $applicant), [
                'name' => $applicant->name,
                'email' => $applicant->email,
                'role' => 'superadmin',
            ])->assertRedirect();

        $fresh = $applicant->fresh();
        $this->assertFalse($fresh->is_admin);
        $this->assertFalse($fresh->is_superadmin);
    }

    public function test_user_edit_controls_are_wired_to_the_application_script(): void
    {
        $superadmin = $this->superadmin();

        $this->actingAs($superadmin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('data-edit-user', false)
            ->assertSee('id="edit-user-role"', false)
            ->assertDontSee('onclick="fillEditModal', false);

        $script = file_get_contents(resource_path('js/app.interactions.js'));
        $this->assertStringContainsString('function initUserEditor()', $script);
        $this->assertStringContainsString("closest('[data-edit-user]')", $script);
        $this->assertStringContainsString("button.dataset.accountType === 'portal'", $script);
    }

    public function test_superadmin_can_create_new_admin(): void
    {
        $this->actingAs($this->superadmin())
            ->post(route('admin.users.store'), [
                'name' => 'Admin Baru',
                'email' => 'adminbaru@smkalfatih.sch.id',
                'password' => 'StrongPassword123',
                'role' => 'admin',
            ])->assertRedirect()
            ->assertSessionHas('success');

        $user = User::where('email', 'adminbaru@smkalfatih.sch.id')->first();

        $this->assertNotNull($user);
        $this->assertTrue($user->is_admin);
        $this->assertFalse($user->is_superadmin);
        $this->assertTrue(Hash::check('StrongPassword123', $user->password));
    }

    public function test_create_validates_unique_email_and_min_password(): void
    {
        $existing = $this->admin();

        $this->actingAs($this->superadmin())
            ->post(route('admin.users.store'), [
                'name' => 'Duplikat',
                'email' => $existing->email,
                'password' => '123',
                'role' => 'admin',
            ])->assertSessionHasErrors(['email', 'password']);
    }

    public function test_superadmin_can_update_user_password(): void
    {
        $superadmin = $this->superadmin();
        $target = $this->admin();

        $this->actingAs($superadmin)
            ->put(route('admin.users.update', $target), [
                'name' => $target->name,
                'email' => $target->email,
                'password' => 'NewStrongPassword123',
            ])->assertRedirect()
            ->assertSessionHas('success');

        $this->assertTrue(Hash::check('NewStrongPassword123', $target->fresh()->password));
    }

    public function test_update_ignores_blank_password(): void
    {
        $superadmin = $this->superadmin();
        $target = $this->admin();
        $oldHash = $target->password;

        $this->actingAs($superadmin)
            ->put(route('admin.users.update', $target), [
                'name' => 'Nama Diubah',
                'email' => $target->email,
                'password' => '',
            ])->assertRedirect()
            ->assertSessionHas('success');

        $fresh = $target->fresh();
        $this->assertSame('Nama Diubah', $fresh->name);
        $this->assertSame($oldHash, $fresh->password);
    }

    public function test_superadmin_cannot_change_own_role(): void
    {
        $superadmin = $this->superadmin();

        $this->actingAs($superadmin)
            ->put(route('admin.users.update', $superadmin), [
                'name' => $superadmin->name,
                'email' => $superadmin->email,
                'role' => 'admin',
            ])->assertRedirect()
            ->assertSessionHas('success');

        $this->assertTrue($superadmin->fresh()->is_superadmin);
    }

    public function test_superadmin_can_promote_admin_to_superadmin(): void
    {
        $superadmin = $this->superadmin();
        $target = $this->admin();

        $this->actingAs($superadmin)
            ->put(route('admin.users.update', $target), [
                'name' => $target->name,
                'email' => $target->email,
                'role' => 'superadmin',
            ])->assertRedirect()
            ->assertSessionHas('success');

        $this->assertTrue($target->fresh()->is_superadmin);
    }
}
