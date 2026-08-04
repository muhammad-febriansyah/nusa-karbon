<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\TeamMember;
use App\Support\Locale;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TeamMember>
 */
class TeamMemberFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $locale = Locale::default();

        return [
            'name' => fake()->name(),
            'position' => [$locale => 'Operations Manager'],
            'bio' => [$locale => fake()->sentence()],
            'photo_path' => null,
            'linkedin_url' => null,
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
