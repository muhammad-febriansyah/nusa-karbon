<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ProductCategory;
use App\Support\Locale;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ProductCategory>
 */
class ProductCategoryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // `words()` mengembalikan larik; digabung sendiri supaya tipenya jelas
        // string tanpa perlu memaksa cast.
        $name = implode(' ', (array) fake()->unique()->words(2));
        $locale = Locale::default();

        return [
            'name' => [$locale => Str::title($name)],
            'slug' => [$locale => Str::slug($name)],
            'description' => null,
            'icon' => null,
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
