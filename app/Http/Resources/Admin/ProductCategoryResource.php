<?php

declare(strict_types=1);

namespace App\Http\Resources\Admin;

use App\Models\ProductCategory;
use App\Support\Locale;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ProductCategory
 */
final class ProductCategoryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->getTranslations('name'),
            'slug' => $this->getTranslations('slug'),
            'description' => $this->getTranslations('description'),
            'icon' => $this->icon,
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
            'completeness' => $this->completeness(),
            'created_at' => $this->created_at?->format('d M Y, H:i'),
        ];
    }

    /**
     * Kelengkapan terjemahan per locale, dipakai `TranslationIndicator`.
     * Field wajib untuk dianggap lengkap: nama dan slug.
     *
     * @return array<string, string>
     */
    private function completeness(): array
    {
        $names = $this->getTranslations('name');
        $slugs = $this->getTranslations('slug');

        $result = [];

        foreach (Locale::supported() as $locale) {
            $filled = collect([$names[$locale] ?? null, $slugs[$locale] ?? null])
                ->filter(fn (?string $value) => $value !== null && $value !== '')
                ->count();

            $result[$locale] = match ($filled) {
                2 => 'complete',
                0 => 'empty',
                default => 'partial',
            };
        }

        return $result;
    }
}
