<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\SettingType;
use App\Models\SiteSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SiteSetting>
 */
class SiteSettingFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'group' => 'identity',
            'key' => fake()->unique()->slug(2, false),
            'label' => fake()->words(2, true),
            'description' => null,
            'value' => ['data' => fake()->word()],
            'type' => SettingType::Str,
            'rules' => null,
            'sort_order' => 0,
            'is_public' => true,
        ];
    }

    public function ofType(SettingType $type, mixed $value = null): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => $type,
            'value' => ['data' => $value],
        ]);
    }

    public function secret(): static
    {
        return $this->state(fn (array $attributes) => [
            'key' => 'turnstile_secret_key',
            'group' => 'system',
            'is_public' => false,
            'value' => ['data' => null],
        ]);
    }
}
