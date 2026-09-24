<?php

namespace Database\Factories;

use App\Enums\ProgramStatus;
use App\Models\Program;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Program>
 */
class ProgramFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->words(2, true),
            'slug' => fn (array $attrs) => Str::slug($attrs['name']),
            'short_description' => $this->faker->sentence(8),
            'description' => $this->faker->paragraphs(4, true),
            'image' => null,
            'status' => ProgramStatus::Active,
            'order' => $this->faker->numberBetween(0, 10),
        ];
    }
}
