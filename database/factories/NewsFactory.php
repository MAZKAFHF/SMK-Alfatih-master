<?php

namespace Database\Factories;

use App\Enums\ContentStatus;
use App\Models\News;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<News>
 */
class NewsFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => $this->faker->unique()->sentence(5),
            'slug' => fn (array $attrs) => Str::slug($attrs['title']),
            'thumbnail' => null,
            'content' => $this->faker->paragraphs(5, true),
            'status' => ContentStatus::Published,
            'author_id' => null,
            'published_at' => $this->faker->dateTimeBetween('-2 months'),
        ];
    }
}
