<?php

namespace Database\Factories;

use App\Enums\ContentStatus;
use App\Models\Gallery;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Gallery>
 */
class GalleryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(4),
            'image' => null,
            'category' => $this->faker->randomElement(['Kegiatan', 'Fasilitas', 'Prestasi']),
            'status' => ContentStatus::Published,
            'order' => $this->faker->numberBetween(0, 10),
        ];
    }
}
