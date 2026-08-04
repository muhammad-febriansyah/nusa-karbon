<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Certificate;
use App\Support\Locale;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Certificate>
 */
class CertificateFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $locale = Locale::default();

        return [
            'name' => [$locale => 'ISO '.fake()->unique()->numberBetween(9000, 9999)],
            'issuer' => fake()->company(),
            'certificate_number' => fake()->bothify('CERT-####-??'),
            'description' => [$locale => fake()->sentence()],
            'logo_path' => null,
            'file_path' => null,
            'issued_at' => now()->subYear()->toDateString(),
            'expires_at' => now()->addYear()->toDateString(),
            'show_in_trust_bar' => false,
            'sort_order' => 0,
            'is_active' => true,
        ];
    }

    public function trustBar(): static
    {
        return $this->state(fn (array $attributes) => [
            'show_in_trust_bar' => true,
        ]);
    }

    /**
     * Sertifikat yang kedaluwarsa dalam waktu dekat, dipakai menguji
     * peringatan dashboard.
     */
    public function expiringSoon(int $days = 30): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => now()->addDays($days)->toDateString(),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => now()->subDay()->toDateString(),
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
