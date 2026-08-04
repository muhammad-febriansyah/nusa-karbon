<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Faq;
use App\Models\FaqCategory;
use App\Support\Locale;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Faq>
 */
class FaqFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $locale = Locale::default();

        return [
            'faq_category_id' => FaqCategory::factory(),
            'question' => [$locale => fake()->sentence().'?'],
            'answer' => [$locale => fake()->paragraph()],
            'sort_order' => 0,
            'is_active' => true,
        ];
    }

    /**
     * FAQ tanpa kategori; tetap tampil di halaman publik pada kelompok
     * "Lainnya".
     */
    public function uncategorised(): static
    {
        return $this->state(fn (array $attributes) => [
            'faq_category_id' => null,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
