<?php

namespace Tests\Feature;

use App\Models\PPDBRegistration;
use App\Models\PpdbPeriod;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PpdbConcurrencyTest extends TestCase
{
    use RefreshDatabase;

    private function program(): Program
    {
        return Program::factory()->create(['status' => 'active']);
    }

    private function fullPayload(string $name, int $programId, int $seq, array $overrides = []): array
    {
        return array_merge([
            'name' => $name,
            'nik' => str_pad((string) (3201010105100000 + $seq), 16, '0', STR_PAD_LEFT),
            'nisn' => str_pad((string) (1000000000 + $seq), 10, '0', STR_PAD_LEFT),
            'birth_place' => 'Bogor',
            'birth_date' => '2010-05-12',
            'gender' => 'laki-laki',
            'address' => 'Jl. Merdeka No. '.$seq,
            'province' => 'Jawa Barat',
            'city' => 'Kota Bogor',
            'district' => 'Bogor Tengah',
            'village' => 'Pabaton',
            'postal_code' => '16121',
            'school_origin' => 'SMPN 1 Bogor',
            'phone' => '0812'.str_pad((string) $seq, 8, '0', STR_PAD_LEFT),
            'father_name' => 'Ayah '.$name,
            'father_phone' => '0813'.str_pad((string) $seq, 8, '0', STR_PAD_LEFT),
            'mother_name' => 'Ibu '.$name,
            'program_id' => $programId,
        ], $overrides);
    }

    public function test_50_sequential_registrations_all_unique(): void
    {
        $program = $this->program();
        $period = PpdbPeriod::query()->firstOrFail();
        $numbers = [];

        for ($i = 0; $i < 50; $i++) {
            $payload = $this->fullPayload("Siswa $i", $program->id, $i + 1);
            $reg = PPDBRegistration::create(array_merge($payload, [
                'period_id' => $period->id,
                'academic_year' => $period->academic_year,
                'application_status' => 'draft',
                'source' => 'applicant',
            ]));
            $this->assertNotNull($reg);
            $numbers[] = $reg->registration_number;
        }

        $this->assertCount(50, array_unique($numbers), 'All registration numbers must be unique');
        foreach ($numbers as $n) {
            $this->assertMatchesRegularExpression('/^PPDB-\d{4}-\d{5}$/', $n);
        }
        $this->assertEquals(50, PPDBRegistration::count());
    }

    public function test_double_submit_is_blocked(): void
    {
        $program = $this->program();
        $period = PpdbPeriod::query()->firstOrFail();
        $period->update(['status' => 'open', 'is_open' => true, 'status_override' => 'open']);
        $user = User::factory()->create(['is_applicant' => true, 'is_admin' => false]);
        $payload = $this->fullPayload('Double Tester', $program->id, 901, ['gender' => 'perempuan']);

        $this->actingAs($user)->post(route('portal.applications.store'), $payload)->assertRedirect();
        $this->actingAs($user)->post(route('portal.applications.store'), $payload)->assertSessionHasErrors('name');
        $this->assertEquals(1, PPDBRegistration::where('name', 'Double Tester')->count());
    }

    public function test_registration_number_format_remains_valid_after_soft_delete(): void
    {
        $program = $this->program();
        $period = PpdbPeriod::query()->firstOrFail();
        $first = PPDBRegistration::create(array_merge($this->fullPayload('Siswa A', $program->id, 801), [
            'period_id' => $period->id, 'application_status' => 'draft', 'source' => 'applicant',
        ]));
        $first->delete(); // soft
        $second = PPDBRegistration::create(array_merge($this->fullPayload('Siswa B', $program->id, 802, ['gender' => 'perempuan']), [
            'period_id' => $period->id, 'application_status' => 'draft', 'source' => 'applicant',
        ]));
        $this->assertNotEquals($first->registration_number, $second->registration_number);
        // even with trashed, numbers unique
        $this->assertCount(2, PPDBRegistration::withTrashed()->get()->pluck('registration_number')->unique());
    }
}
