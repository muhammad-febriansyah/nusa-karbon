<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ContainerType;
use App\Support\Locale;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContainerType>
 */
class ContainerTypeFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $locale = Locale::default();

        return [
            'code' => 'CT-'.fake()->unique()->numerify('####'),
            'name' => [$locale => '20FT Dry Van'],
            'max_payload_kg' => 18000,
            'max_cbm' => 28,
            'internal_length_m' => 5.90,
            'internal_width_m' => 2.35,
            'internal_height_m' => 2.39,
            'sort_order' => 0,
            'is_active' => true,
        ];
    }

    public function highCube(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => [Locale::default() => '40FT High Cube'],
            'max_payload_kg' => 26000,
            'max_cbm' => 68,
            'internal_length_m' => 12.03,
            'internal_width_m' => 2.35,
            'internal_height_m' => 2.69,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
