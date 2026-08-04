<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ProductCategory;
use App\Support\Locale;
use Illuminate\Database\Seeder;

/**
 * Kategori awal sesuai lini produk Nusacarbon.
 */
final class ProductCategorySeeder extends Seeder
{
    public function run(): void
    {
        $locale = Locale::default();

        $categories = [
            [
                'name' => 'Arang Shisha',
                'slug' => 'arang-shisha',
                'description' => 'Briket arang batok kelapa untuk shisha, bakar bersih tanpa asap dan tanpa aroma.',
                'icon' => 'flame',
            ],
            [
                'name' => 'Arang BBQ',
                'slug' => 'arang-bbq',
                'description' => 'Briket arang untuk panggangan, panas stabil dan tahan lama.',
                'icon' => 'beef',
            ],
            [
                'name' => 'Arang Industri',
                'slug' => 'arang-industri',
                'description' => 'Arang untuk kebutuhan industri dan pengolahan.',
                'icon' => 'factory',
            ],
        ];

        foreach ($categories as $position => $category) {
            ProductCategory::query()->firstOrCreate(
                ["slug->{$locale}" => $category['slug']],
                [
                    'name' => [$locale => $category['name']],
                    'slug' => [$locale => $category['slug']],
                    'description' => [$locale => $category['description']],
                    'icon' => $category['icon'],
                    'sort_order' => $position,
                    'is_active' => true,
                ],
            );
        }
    }
}
