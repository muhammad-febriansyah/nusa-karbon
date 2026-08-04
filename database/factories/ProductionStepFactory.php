<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ProductionStep;
use App\Support\Locale;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductionStep>
 */
class ProductionStepFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $locale = Locale::default();

        return [
            'title' => [$locale => 'Karbonisasi'],
            'description' => [$locale => fake()->sentence()],
            'image_path' => null,
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
