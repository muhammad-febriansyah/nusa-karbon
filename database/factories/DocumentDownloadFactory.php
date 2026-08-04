<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\DocumentDownload;
use App\Models\ProductDocument;
use App\Support\Locale;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DocumentDownload>
 */
class DocumentDownloadFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_document_id' => ProductDocument::factory(),
            'ip_address' => $this->faker->ipv4(),
            'country_code' => 'ID',
            'user_agent' => $this->faker->userAgent(),
            'referer' => null,
            'locale' => Locale::default(),
        ];
    }
}
