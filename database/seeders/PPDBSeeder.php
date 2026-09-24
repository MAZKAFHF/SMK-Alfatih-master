<?php

namespace Database\Seeders;

use App\Enums\RegistrationStatus;
use App\Models\PPDBRegistration;
use App\Models\Program;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PPDBSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $programIds = Program::pluck('id')->all();

        if (empty($programIds)) {
            return;
        }

        $registrations = PPDBRegistration::factory()->count(15)->create([
            'program_id' => fn () => fake()->randomElement($programIds),
        ]);

        foreach ($registrations->take(6) as $registration) {
            $registration->update(['status' => RegistrationStatus::Accepted]);
        }

        foreach ($registrations->slice(6, 3) as $registration) {
            $registration->update(['status' => RegistrationStatus::Rejected]);
        }

        foreach ($registrations->slice(9, 2) as $registration) {
            $registration->update(['status' => RegistrationStatus::Cancelled]);
        }
    }
}
