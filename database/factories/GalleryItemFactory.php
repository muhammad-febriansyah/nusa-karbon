<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\GalleryItemType;
use App\Models\Gallery;
use App\Models\GalleryItem;
use App\Support\Locale;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GalleryItem>
 */
class GalleryItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'gallery_id' => Gallery::factory(),
            'type' => GalleryItemType::Image,
            'file_path' => 'galleries/items/contoh.jpg',
            'video_url' => null,
            'caption' => [Locale::default() => fake()->sentence(4)],
            'sort_order' => 0,
        ];
    }

    public function video(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => GalleryItemType::Video,
            'file_path' => null,
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        ]);
    }
}
