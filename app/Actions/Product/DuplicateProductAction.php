<?php

declare(strict_types=1);

namespace App\Actions\Product;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Support\Locale;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final readonly class DuplicateProductAction
{
    public function __construct(
        private ProductRepositoryInterface $products,
    ) {}

    /**
     * Salinan dibuat nonaktif supaya tidak langsung tampil di website publik
     * sebelum diperiksa. Kode dan slug diberi akhiran agar tetap unik.
     */
    public function handle(Product $product): Product
    {
        return DB::transaction(function () use ($product): Product {
            $copy = $product->replicate([
                'created_by',
                'updated_by',
                'created_at',
                'updated_at',
                'deleted_at',
            ]);

            $copy->code = $this->uniqueCode($product->code);
            $copy->name = $this->suffixTranslations(
                $product->getTranslations('name'),
                ' (Salinan)',
            );
            $copy->slug = $this->uniqueSlugs($product->getTranslations('slug'));
            $copy->is_active = false;
            $copy->is_featured = false;
            $copy->sort_order = $this->products->nextSortOrder();

            $copy->save();

            foreach ($product->getMedia(Product::COLLECTION_GALLERY) as $media) {
                $media->copy($copy, Product::COLLECTION_GALLERY);
            }

            foreach ($product->getMedia(Product::COLLECTION_COVER) as $media) {
                $media->copy($copy, Product::COLLECTION_COVER);
            }

            return $copy->fresh(['category', 'media']) ?? $copy;
        });
    }

    private function uniqueCode(string $code): string
    {
        $candidate = Str::limit($code, 40, '').'-COPY';
        $attempt = 1;

        while ($this->products->codeExists($candidate)) {
            $attempt++;
            $candidate = Str::limit($code, 38, '').'-COPY'.$attempt;
        }

        return $candidate;
    }

    /**
     * @param  array<string, string|null>  $translations
     * @return array<string, string|null>
     */
    private function suffixTranslations(array $translations, string $suffix): array
    {
        foreach ($translations as $locale => $value) {
            if ($value !== null && $value !== '') {
                $translations[$locale] = $value.$suffix;
            }
        }

        return $translations;
    }

    /**
     * @param  array<string, string|null>  $slugs
     * @return array<string, string|null>
     */
    private function uniqueSlugs(array $slugs): array
    {
        foreach (Locale::supported() as $locale) {
            $value = $slugs[$locale] ?? null;

            if ($value === null || $value === '') {
                continue;
            }

            $candidate = $value.'-copy';
            $attempt = 1;

            while ($this->products->slugExists($locale, $candidate)) {
                $attempt++;
                $candidate = $value.'-copy'.$attempt;
            }

            $slugs[$locale] = $candidate;
        }

        return $slugs;
    }
}
