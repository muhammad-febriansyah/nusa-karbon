<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Gallery;
use App\Support\Locale;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Gallery>
 */
class GalleryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $locale = Locale::default();
        $title = Str::title(implode(' ', (array) fake()->unique()->words(2)));

        return [
            'title' => [$locale => $title],
            'slug' => [$locale => Str::slug($title)],
            'description' => [$locale => fake()->sentence()],
            'cover_path' => null,
            'sort_order' => 0,
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
