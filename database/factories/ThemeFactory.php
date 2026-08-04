<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Theme;
use App\Support\Color;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Theme>
 */
class ThemeFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = Str::title(implode(' ', (array) fake()->unique()->words(2)));

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'scope' => Theme::SCOPE_BOTH,
            'tokens' => self::defaultTokens(),
            'is_preset' => false,
            'is_active_admin' => false,
            'is_active_public' => false,
        ];
    }

    public function preset(): static
    {
        return $this->state(fn (array $attributes) => ['is_preset' => true]);
    }

    public function activeAdmin(): static
    {
        return $this->state(fn (array $attributes) => ['is_active_admin' => true]);
    }

    public function activePublic(): static
    {
        return $this->state(fn (array $attributes) => ['is_active_public' => true]);
    }

    /**
     * Palet netral yang lolos seluruh ambang kontras; dipakai sebagai titik
     * awal pengujian.
     *
     * @return array<string, string>
     */
    public static function defaultTokens(): array
    {
        $white = '#FFFFFF';
        $ink = '#131418';

        $tokens = [
            'primary' => '#C94A17',
            'secondary' => '#F1F1EE',
            'accent' => '#8A6A17',
            'background' => '#F7F7F4',
            'foreground' => $ink,
            'card' => $white,
            'popover' => $white,
            'muted' => '#F1F1EE',
            'muted_foreground' => '#5F6167',
            'border' => '#E1E1DA',
            'input' => '#E1E1DA',
            'ring' => '#C94A17',
            'success' => '#15803D',
            'destructive' => '#B91C1C',
            'warning' => '#B45309',
            'info' => '#1D4ED8',
            'neutral' => '#4B5563',
            'violet' => '#6D28D9',
            'teal' => '#0F766E',
            'sidebar' => '#FAFAF8',
            'sidebar_accent' => '#F0EFEB',
            'sidebar_primary' => '#C94A17',
            'sidebar_border' => '#E1E1DA',
            'sidebar_ring' => '#C94A17',
            'radius' => '0.5rem',
        ];

        // Teks di atas warna solid dihitung supaya palet bawaan selalu
        // lolos ambang kontras yang sama dengan yang divalidasi server.
        $onColor = [];

        foreach ([
            'primary_foreground' => $tokens['primary'],
            'accent_foreground' => $tokens['accent'],
            'success_foreground' => $tokens['success'],
            'destructive_foreground' => $tokens['destructive'],
            'warning_foreground' => $tokens['warning'],
            'info_foreground' => $tokens['info'],
            'neutral_foreground' => $tokens['neutral'],
            'violet_foreground' => $tokens['violet'],
            'teal_foreground' => $tokens['teal'],
            'sidebar_primary_foreground' => $tokens['sidebar_primary'],
        ] as $key => $background) {
            $onColor[$key] = Color::bestForeground($background);
        }

        $darkText = [
            'secondary_foreground' => $ink,
            'card_foreground' => $ink,
            'popover_foreground' => $ink,
            'sidebar_foreground' => $ink,
            'sidebar_accent_foreground' => $ink,
        ];

        return [...$tokens, ...$onColor, ...$darkText];
    }
}
