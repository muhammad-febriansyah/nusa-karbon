<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Tag;
use App\Support\Locale;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Tag>
 */
class TagFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $locale = Locale::default();
        $name = (string) fake()->unique()->word();

        return [
            'name' => [$locale => Str::title($name)],
            'slug' => [$locale => Str::slug($name)],
        ];
    }
}
