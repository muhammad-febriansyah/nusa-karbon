<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PostStatus;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\User;
use App\Support\Locale;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $locale = Locale::default();
        $title = Str::title(implode(' ', (array) fake()->unique()->words(4)));

        return [
            'post_category_id' => PostCategory::factory(),
            'user_id' => User::factory(),
            'title' => [$locale => $title],
            'slug' => [$locale => Str::slug($title)],
            'excerpt' => [$locale => fake()->sentence()],
            'content' => [$locale => '<p>'.fake()->paragraph().'</p>'],
            'cover_path' => null,
            'meta_title' => null,
            'meta_description' => null,
            'status' => PostStatus::Draft,
            'published_at' => null,
            'reading_minutes' => 1,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PostStatus::Published,
            'published_at' => now()->subDay(),
        ]);
    }

    /**
     * Artikel terjadwal yang waktunya belum tiba.
     */
    public function scheduled(int $daysAhead = 3): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PostStatus::Scheduled,
            'published_at' => now()->addDays($daysAhead),
        ]);
    }

    /**
     * Artikel terjadwal yang waktunya sudah lewat; menunggu command menerbitkan.
     */
    public function dueForPublishing(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PostStatus::Scheduled,
            'published_at' => now()->subMinute(),
        ]);
    }

    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PostStatus::Archived,
        ]);
    }
}
