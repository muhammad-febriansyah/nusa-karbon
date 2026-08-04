<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\PackagingOption;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductSpecification;
use App\Support\Locale;
use Illuminate\Database\Seeder;

/**
 * Produk contoh untuk pengembangan dan pengujian Container Calculator.
 *
 * PENTING: seluruh angka master carton di bawah ini masih **data sementara**.
 * Berat, tare, dan volume asli menunggu data master carton dari klien
 * (lihat docs/09-tasks.md bagian "Blocker Data Klien"). Ganti nilainya begitu
 * data resmi diterima — jangan dipakai untuk penawaran ke buyer.
 *
 * Komposisinya sengaja dibuat beragam supaya kedua batas perhitungan teruji:
 * produk padat menjadi weight-limited, produk ringan-besar menjadi
 * volume-limited, dan satu produk sengaja dibiarkan tanpa data master carton
 * untuk menguji peringatan admin serta `Product::scopeCalculable()`.
 *
 * @see docs/06-architecture.md bagian 5.5
 */
final class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $locale = Locale::default();

        /** @var array<string, int> $categoryIds */
        $categoryIds = ProductCategory::query()
            ->get(['id', 'slug'])
            ->mapWithKeys(fn (ProductCategory $category): array => [
                $category->getTranslation('slug', $locale) => $category->id,
            ])
            ->all();

        $products = [
            [
                'code' => 'SHISHA-CUBE-26',
                'category' => 'arang-shisha',
                'name' => 'Briket Shisha Cube 26 mm',
                'short_description' => 'Briket batok kelapa bentuk kubus 26 mm, bakar bersih tanpa aroma.',
                'shape' => 'Cube',
                'size_mm' => '26 x 26 x 26',
                'master_carton_weight_kg' => 20.000,
                'master_carton_tare_kg' => 1.200,
                'master_carton_cbm' => 0.0420,
                'master_carton_length_cm' => 40.00,
                'master_carton_width_cm' => 30.00,
                'master_carton_height_cm' => 35.00,
                'inner_box_weight_kg' => 1.000,
                'inner_boxes_per_carton' => 20,
                'moq_kg' => 1000.00,
                'is_featured' => true,
                'specifications' => [
                    ['Fixed Carbon', '80', '% min', true],
                    ['Ash Content', '2,5', '% max', true],
                    ['Moisture Content', '5', '% max', false],
                    ['Volatile Matter', '15', '% max', false],
                    ['Calorific Value', '7.200', 'kcal/kg', true],
                    ['Burning Time', '120', 'menit', false],
                    ['Density', '1,05', 'g/cm³', false],
                    ['Sulphur Content', '0,1', '% max', false],
                    ['Odour', 'Tanpa aroma', null, false],
                ],
                'packaging' => ['Inner Box 1 kg', 'Master Carton 20 kg'],
            ],
            [
                'code' => 'SHISHA-HEX-22',
                'category' => 'arang-shisha',
                'name' => 'Briket Shisha Hexagonal 22 mm',
                'short_description' => 'Briket heksagonal 22 mm, panas stabil untuk sesi panjang.',
                'shape' => 'Hexagonal',
                'size_mm' => '22 x 22 x 55',
                'master_carton_weight_kg' => 18.000,
                'master_carton_tare_kg' => 1.100,
                'master_carton_cbm' => 0.0396,
                'master_carton_length_cm' => 40.00,
                'master_carton_width_cm' => 30.00,
                'master_carton_height_cm' => 33.00,
                'inner_box_weight_kg' => 1.000,
                'inner_boxes_per_carton' => 18,
                'moq_kg' => 1000.00,
                'is_featured' => true,
                'specifications' => [
                    ['Fixed Carbon', '78', '% min', true],
                    ['Ash Content', '3', '% max', true],
                    ['Moisture Content', '6', '% max', false],
                    ['Calorific Value', '7.000', 'kcal/kg', true],
                    ['Burning Time', '150', 'menit', false],
                    ['Odour', 'Tanpa aroma', null, false],
                ],
                'packaging' => ['Inner Box 1 kg', 'Master Carton 20 kg'],
            ],
            [
                'code' => 'BBQ-PILLOW-50',
                'category' => 'arang-bbq',
                'name' => 'Briket BBQ Pillow 50 mm',
                'short_description' => 'Briket panggangan bentuk bantal, panas tahan lama.',
                'shape' => 'Pillow',
                'size_mm' => '50 x 40 x 25',
                'master_carton_weight_kg' => 25.000,
                'master_carton_tare_kg' => 1.400,
                'master_carton_cbm' => 0.0473,
                'master_carton_length_cm' => 45.00,
                'master_carton_width_cm' => 35.00,
                'master_carton_height_cm' => 30.00,
                'inner_box_weight_kg' => 2.500,
                'inner_boxes_per_carton' => 10,
                'moq_kg' => 2000.00,
                'is_featured' => false,
                'specifications' => [
                    ['Fixed Carbon', '75', '% min', true],
                    ['Ash Content', '4', '% max', true],
                    ['Moisture Content', '7', '% max', false],
                    ['Calorific Value', '6.800', 'kcal/kg', true],
                    ['Burning Time', '180', 'menit', false],
                ],
                'packaging' => ['Inner Box 10 kg', 'Master Carton 20 kg'],
            ],
            [
                'code' => 'BBQ-LUMP-NAT',
                'category' => 'arang-bbq',
                'name' => 'Arang Kayu Lump Natural',
                'short_description' => 'Arang kayu potongan alami, ringan dan bervolume besar.',
                'shape' => null,
                'size_mm' => null,
                'master_carton_weight_kg' => 15.000,
                'master_carton_tare_kg' => 1.300,
                'master_carton_cbm' => 0.0800,
                'master_carton_length_cm' => 50.00,
                'master_carton_width_cm' => 40.00,
                'master_carton_height_cm' => 40.00,
                'inner_box_weight_kg' => 5.000,
                'inner_boxes_per_carton' => 3,
                'moq_kg' => 2000.00,
                'is_featured' => false,
                'specifications' => [
                    ['Fixed Carbon', '72', '% min', true],
                    ['Ash Content', '5', '% max', true],
                    ['Moisture Content', '8', '% max', false],
                    ['Calorific Value', '6.500', 'kcal/kg', true],
                ],
                'packaging' => ['Inner Box 10 kg'],
            ],
            [
                'code' => 'IND-CARBON-FINE',
                'category' => 'arang-industri',
                'name' => 'Arang Halus Industri',
                'short_description' => 'Arang halus untuk kebutuhan industri, kemasan padat.',
                'shape' => null,
                'size_mm' => null,
                'master_carton_weight_kg' => 30.000,
                'master_carton_tare_kg' => 1.500,
                'master_carton_cbm' => 0.0300,
                'master_carton_length_cm' => 40.00,
                'master_carton_width_cm' => 30.00,
                'master_carton_height_cm' => 25.00,
                'inner_box_weight_kg' => null,
                'inner_boxes_per_carton' => null,
                'moq_kg' => 5000.00,
                'is_featured' => false,
                'specifications' => [
                    ['Fixed Carbon', '70', '% min', true],
                    ['Ash Content', '6', '% max', true],
                    ['Moisture Content', '10', '% max', false],
                ],
                'packaging' => ['Master Carton 20 kg'],
            ],
            [
                // Sengaja tanpa data master carton: menguji peringatan admin dan
                // memastikan produk ini tidak muncul di Container Calculator.
                'code' => 'SHISHA-CUSTOM-OEM',
                'category' => 'arang-shisha',
                'name' => 'Briket Shisha OEM Custom',
                'short_description' => 'Bentuk, ukuran, dan kemasan mengikuti permintaan buyer.',
                'shape' => null,
                'size_mm' => null,
                'master_carton_weight_kg' => null,
                'master_carton_tare_kg' => null,
                'master_carton_cbm' => null,
                'master_carton_length_cm' => null,
                'master_carton_width_cm' => null,
                'master_carton_height_cm' => null,
                'inner_box_weight_kg' => null,
                'inner_boxes_per_carton' => null,
                'moq_kg' => null,
                'is_featured' => false,
                'specifications' => [],
                'packaging' => ['Kemasan Custom Buyer'],
            ],
        ];

        /** @var array<string, int> $packagingIds */
        $packagingIds = PackagingOption::query()
            ->get(['id', 'name'])
            ->mapWithKeys(fn (PackagingOption $option): array => [
                $option->getTranslation('name', $locale) => $option->id,
            ])
            ->all();

        foreach ($products as $position => $product) {
            $record = Product::query()->firstOrCreate(
                ['code' => $product['code']],
                [
                    'product_category_id' => $categoryIds[$product['category']] ?? null,
                    'name' => [$locale => $product['name']],
                    'slug' => [$locale => str($product['name'])->slug()->value()],
                    'short_description' => [$locale => $product['short_description']],
                    'shape' => $product['shape'],
                    'size_mm' => $product['size_mm'],
                    'master_carton_weight_kg' => $product['master_carton_weight_kg'],
                    'master_carton_tare_kg' => $product['master_carton_tare_kg'],
                    'master_carton_cbm' => $product['master_carton_cbm'],
                    'master_carton_length_cm' => $product['master_carton_length_cm'],
                    'master_carton_width_cm' => $product['master_carton_width_cm'],
                    'master_carton_height_cm' => $product['master_carton_height_cm'],
                    'inner_box_weight_kg' => $product['inner_box_weight_kg'],
                    'inner_boxes_per_carton' => $product['inner_boxes_per_carton'],
                    'moq_kg' => $product['moq_kg'],
                    'sort_order' => $position,
                    'is_active' => true,
                    'is_featured' => $product['is_featured'],
                ],
            );

            $this->seedSpecifications($record, $product['specifications']);

            $record->packagingOptions()->syncWithoutDetaching(
                collect($product['packaging'])
                    ->map(fn (string $name): ?int => $packagingIds[$name] ?? null)
                    ->filter()
                    ->all(),
            );
        }
    }

    /**
     * @param  array<int, array{string, string, string|null, bool}>  $rows
     */
    private function seedSpecifications(Product $product, array $rows): void
    {
        foreach ($rows as $position => [$parameter, $value, $unit, $highlighted]) {
            ProductSpecification::query()->firstOrCreate(
                ['product_id' => $product->id, 'parameter->'.Locale::default() => $parameter],
                [
                    'parameter' => [Locale::default() => $parameter],
                    'value' => $value,
                    'unit' => $unit,
                    'is_highlighted' => $highlighted,
                    'sort_order' => $position,
                ],
            );
        }
    }
}
