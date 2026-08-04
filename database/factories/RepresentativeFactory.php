<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Representative;
use App\Support\Locale;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Representative>
 */
class RepresentativeFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $locale = Locale::default();

        return [
            'name' => fake()->name(),
            'position' => [$locale => 'Export Sales Manager'],
            'region' => fake()->randomElement(['MENA', 'Europe', 'Asia']),
            'email' => fake()->unique()->userName().'@nusacarbon.com',
            'phone' => '+6221'.fake()->numerify('#######'),
            'whatsapp' => '+62812'.fake()->numerify('#######'),
            'photo_path' => null,
            'started_at' => now()->subYear()->toDateString(),
            'ended_at' => null,
            'sort_order' => 0,
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
            'ended_at' => now()->toDateString(),
        ]);
    }
}
