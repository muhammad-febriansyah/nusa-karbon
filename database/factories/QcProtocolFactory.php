<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\QcProtocol;
use App\Support\Locale;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QcProtocol>
 */
class QcProtocolFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $locale = Locale::default();

        return [
            'title' => [$locale => 'Drop Test'],
            'description' => [$locale => fake()->sentence()],
            'result_label' => [$locale => 'Breakage tolerance'],
            'result_value' => '< 2.5%',
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
