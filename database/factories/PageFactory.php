<?php

namespace Database\Factories;

use App\Enums\ContentStatus;
use App\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Page>
 */
class PageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(4),
            'slug' => fn (array $attrs) => Str::slug($attrs['title']),
            'content' => $this->faker->paragraphs(6, true),
            'image' => null,
            'meta_title' => null,
            'meta_description' => $this->faker->sentence(),
            'status' => ContentStatus::Published,
            'order' => $this->faker->numberBetween(0, 10),
        ];
    }
}
