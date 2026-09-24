<?php

namespace Database\Factories;

use App\Enums\ContentStatus;
use App\Models\Announcement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Announcement>
 */
class AnnouncementFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => $this->faker->unique()->sentence(5),
            'content' => $this->faker->paragraphs(3, true),
            'status' => ContentStatus::Published,
            'published_at' => $this->faker->dateTimeBetween('-1 month'),
        ];
    }
}
