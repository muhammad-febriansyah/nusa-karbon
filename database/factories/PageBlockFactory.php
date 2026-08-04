<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PageBlockType;
use App\Models\PageBlock;
use App\Support\Locale;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PageBlock>
 */
class PageBlockFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $locale = Locale::default();

        return [
            'page_key' => 'about',
            'block_key' => fake()->unique()->slug(2, false),
            'type' => PageBlockType::RichText,
            'title' => [$locale => 'Profil Perusahaan'],
            'content' => [$locale => '<p>Isi blok.</p>'],
            'image_path' => null,
            'extra' => null,
            'sort_order' => 0,
            'is_active' => true,
        ];
    }

    public function ofType(PageBlockType $type): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => $type,
        ]);
    }
}
