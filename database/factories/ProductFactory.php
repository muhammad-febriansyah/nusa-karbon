<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Support\Locale;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = implode(' ', (array) fake()->unique()->words(3));
        $locale = Locale::default();

        return [
            'product_category_id' => ProductCategory::factory(),
            'code' => Str::upper(Str::slug($name, '-')),
            'name' => [$locale => Str::title($name)],
            'slug' => [$locale => Str::slug($name)],
            'short_description' => null,
            'description' => null,
            'shape' => fake()->randomElement(['Cube', 'Hexagonal', 'Finger']),
            'size_mm' => '26 x 26 x 26',
            'master_carton_weight_kg' => null,
            'master_carton_tare_kg' => null,
            'master_carton_cbm' => null,
            'sort_order' => 0,
            'is_active' => true,
            'is_featured' => false,
        ];
    }

    /**
     * Produk dengan data master carton lengkap sehingga masuk Container
     * Calculator.
     */
    public function calculable(): static
    {
        return $this->state(fn (array $attributes) => [
            'master_carton_weight_kg' => 20.000,
            'master_carton_tare_kg' => 1.200,
            'master_carton_cbm' => 0.0430,
            'master_carton_length_cm' => 40.00,
            'master_carton_width_cm' => 30.00,
            'master_carton_height_cm' => 35.00,
            'inner_box_weight_kg' => 1.000,
            'inner_boxes_per_carton' => 20,
            'moq_kg' => 1000.00,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }
}
