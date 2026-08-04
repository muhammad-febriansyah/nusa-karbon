<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PostCategory;
use App\Support\Locale;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<PostCategory>
 */
class PostCategoryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $locale = Locale::default();
        $name = Str::title(implode(' ', (array) fake()->unique()->words(2)));

        return [
            'name' => [$locale => $name],
            'slug' => [$locale => Str::slug($name)],
            'description' => [$locale => fake()->sentence()],
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
