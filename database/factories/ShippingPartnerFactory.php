<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ShippingPartner;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ShippingPartner>
 */
class ShippingPartnerFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'logo_path' => null,
            'website_url' => 'https://example.com',
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
