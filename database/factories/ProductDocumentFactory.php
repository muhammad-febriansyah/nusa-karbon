<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\DocumentType;
use App\Models\Product;
use App\Models\ProductDocument;
use App\Support\Locale;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductDocument>
 */
class ProductDocumentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $locale = Locale::default();

        return [
            'product_id' => Product::factory(),
            'type' => DocumentType::Coa,
            'title' => [$locale => 'Certificate of Analysis'],
            'file_path' => 'products/documents/coa-'.$this->faker->unique()->lexify('????????').'.pdf',
            'file_name' => 'coa.pdf',
            'file_size' => 512 * 1024,
            'mime_type' => 'application/pdf',
            'batch_reference' => 'SP-2409',
            'issued_at' => now()->subMonth()->toDateString(),
            'download_count' => 0,
            'is_active' => true,
        ];
    }

    public function ofType(DocumentType $type): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => $type,
            'title' => [Locale::default() => $type->label()],
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
