<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductSpecification;
use App\Support\Locale;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductSpecification>
 */
class ProductSpecificationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $locale = Locale::default();

        return [
            'product_id' => Product::factory(),
            'parameter' => [$locale => 'Fixed Carbon'],
            'value' => '80',
            'unit' => '% min',
            'is_highlighted' => false,
            'sort_order' => 0,
        ];
    }

    public function highlighted(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_highlighted' => true,
        ]);
    }
}
