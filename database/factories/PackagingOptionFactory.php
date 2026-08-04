<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PackagingOption;
use App\Support\Locale;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PackagingOption>
 */
class PackagingOptionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $locale = Locale::default();

        return [
            'name' => [$locale => 'Inner Box 1 kg'],
            'description' => [$locale => 'Kotak karton isi 1 kg, dibungkus plastik segel.'],
            'image_path' => null,
            'net_weight_kg' => 1,
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

    public function withImage(): static
    {
        return $this->state(fn (array $attributes) => [
            'image_path' => PackagingOption::IMAGE_DIRECTORY.'/contoh.webp',
        ]);
    }
}
