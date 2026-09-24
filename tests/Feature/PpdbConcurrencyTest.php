<?php

namespace Tests\Feature;

use App\Models\PPDBRegistration;
use App\Models\Program;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PpdbConcurrencyTest extends TestCase
{
    use RefreshDatabase;

    private function program(): Program
    {
        return Program::factory()->create(['status' => 'active']);
    }

    public function test_50_sequential_registrations_all_unique(): void
    {
        $program = $this->program();
        $numbers = [];
        // Bypass throttle for stress test (still tests atomicity)
        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);

        for ($i = 0; $i < 50; $i++) {
            $payload = [
                'name' => "Siswa $i",
                'gender' => 'laki-laki',
                'program_id' => $program->id,
                'nisn' => 'NISN' . str_pad((string) $i, 5, '0', STR_PAD_LEFT),
            ];
            $this->post('/ppdb', $payload)->assertRedirect();
            $reg = PPDBRegistration::where('name', "Siswa $i")->first();
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
        $payload = [
            'name' => 'Double Tester',
            'gender' => 'perempuan',
            'program_id' => $program->id,
            'phone' => '081234567890',
        ];

        // first succeeds
        $this->post('/ppdb', $payload)->assertRedirect();
        // immediate duplicate within 2min should be blocked with error (not redirect success)
        $this->post('/ppdb', $payload)->assertSessionHas('error');
        $this->assertEquals(1, PPDBRegistration::where('name', 'Double Tester')->count());
    }

    public function test_registration_number_format_remains_valid_after_soft_delete(): void
    {
        $program = $this->program();
        $this->post('/ppdb', ['name' => 'A', 'gender' => 'laki-laki', 'program_id' => $program->id])->assertRedirect();
        $first = PPDBRegistration::first();
        $first->delete(); // soft
        $this->post('/ppdb', ['name' => 'B', 'gender' => 'perempuan', 'program_id' => $program->id])->assertRedirect();
        $second = PPDBRegistration::where('name', 'B')->first();
        $this->assertNotEquals($first->registration_number, $second->registration_number);
        // even with trashed, numbers unique
        $this->assertCount(2, PPDBRegistration::withTrashed()->get()->pluck('registration_number')->unique());
    }
}
