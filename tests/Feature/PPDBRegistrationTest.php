<?php

namespace Tests\Feature;

use App\Models\PPDBRegistration;
use App\Models\Program;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PPDBRegistrationTest extends TestCase
{
    use RefreshDatabase;

    private function program(): Program
    {
        return Program::factory()->create(['status' => 'active']);
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Ahmad Fauzi',
            'nisn' => '1234567890',
            'birth_place' => 'Bogor',
            'birth_date' => '2010-05-12',
            'gender' => 'laki-laki',
            'address' => 'Jl. Merdeka No. 1, Bogor',
            'school_origin' => 'SMPN 1 Bogor',
            'phone' => '081234567890',
            'email' => 'ahmad@example.com',
            'parent_name' => 'H. Muhammad Fauzi',
            'program_id' => $this->program()->id,
        ], $overrides);
    }

    public function test_ppdb_page_links_to_registration_form(): void
    {
        $this->get('/ppdb')
            ->assertOk()
            ->assertSee(route('ppdb.siswa'))
            ->assertSee(route('ppdb.status'));
    }

    public function test_siswa_page_displays_registration_form_with_programs(): void
    {
        $program = $this->program();

        $this->get('/ppdb/siswa')
            ->assertOk()
            ->assertSee($program->name)
            ->assertSee('name="name"', false)
            ->assertSee('name="program_id"', false);
    }

    public function test_store_creates_registration_with_generated_number(): void
    {
        $response = $this->post('/ppdb', $this->validPayload());

        $response->assertRedirect(route('ppdb.status', ['registration_number' => 'PPDB-'.now()->year.'-00001']));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('ppdb_registrations', [
            'name' => 'Ahmad Fauzi',
            'registration_number' => 'PPDB-'.now()->year.'-00001',
            'status' => 'pending',
        ]);
    }

    public function test_store_validates_required_fields(): void
    {
        $this->post('/ppdb', [])
            ->assertSessionHasErrors(['name', 'gender', 'program_id']);
    }

    public function test_store_validates_program_exists(): void
    {
        $this->post('/ppdb', $this->validPayload(['program_id' => 999]))
            ->assertSessionHasErrors(['program_id']);
    }

    public function test_status_page_shows_registration_when_number_matches(): void
    {
        $registration = PPDBRegistration::factory()->create();

        $this->get('/ppdb/status?registration_number='.$registration->registration_number)
            ->assertOk()
            ->assertSee($registration->registration_number)
            ->assertSee($registration->name);
    }

    public function test_status_page_shows_not_found_message_for_unknown_number(): void
    {
        $this->get('/ppdb/status?registration_number=PPDB-2026-99999')
            ->assertOk()
            ->assertSee('Data tidak ditemukan');
    }
}
