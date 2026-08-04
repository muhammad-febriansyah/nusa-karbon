<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Incoterm;
use App\Enums\RfqStatus;
use App\Models\RfqRequest;
use App\Support\Locale;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RfqRequest>
 */
class RfqRequestFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'reference_no' => 'RFQ-'.now()->format('Ymd').'-'.fake()->unique()->numerify('####'),
            'product_id' => null,
            'company_name' => fake()->company(),
            'contact_name' => fake()->name(),
            'email' => fake()->unique()->companyEmail(),
            'phone' => fake()->numerify('+9715#######'),
            'whatsapp' => fake()->numerify('+9715#######'),
            'country_code' => fake()->randomElement(['AE', 'SA', 'DE', 'TR', 'RU']),
            'incoterms' => Incoterm::Fob,
            'port_of_destination' => fake()->city(),
            'monthly_tonnage' => fake()->randomElement(['54 MT', '3 x 40HC', 'two containers monthly']),
            'container_type_id' => null,
            'packaging_notes' => null,
            'message' => fake()->paragraph(),
            'status' => RfqStatus::New,
            'is_read' => false,
            'locale' => Locale::default(),
            'source_url' => 'https://nusacarbon.test/products',
            'ip_address' => fake()->ipv4(),
            'user_agent' => 'Mozilla/5.0',
        ];
    }

    public function read(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_read' => true,
        ]);
    }

    public function status(RfqStatus $status): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => $status,
        ]);
    }

    public function spam(): static
    {
        return $this->status(RfqStatus::Spam);
    }
}
