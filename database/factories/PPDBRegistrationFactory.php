<?php

namespace Database\Factories;

use App\Enums\RegistrationStatus;
use App\Models\PPDBRegistration;
use App\Models\Program;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PPDBRegistration>
 */
class PPDBRegistrationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'registration_number' => sprintf(
                'PPDB-%s-%05d',
                now()->year,
                fake()->unique()->numberBetween(1, 99999)
            ),
            'name' => $this->faker->name(),
            'nisn' => $this->faker->numerify('##########'),
            'birth_place' => $this->faker->city(),
            'birth_date' => $this->faker->dateTimeBetween('-16 years', '-13 years')->format('Y-m-d'),
            'gender' => $this->faker->randomElement(['laki-laki', 'perempuan']),
            'address' => $this->faker->address(),
            'school_origin' => $this->faker->randomElement(['SMP', 'MTs']).' '.$this->faker->lastName(),
            'phone' => $this->faker->numerify('08##########'),
            'email' => $this->faker->safeEmail(),
            'parent_name' => $this->faker->name(),
            'program_id' => Program::factory(),
            'status' => RegistrationStatus::Pending,
        ];
    }
}
