<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\PackagingOption;
use App\Support\Locale;
use Illuminate\Database\Seeder;

/**
 * Opsi kemasan standar ekspor briket. Gambar diunggah admin belakangan.
 */
final class PackagingOptionSeeder extends Seeder
{
    public function run(): void
    {
        $locale = Locale::default();

        $options = [
            [
                'name' => 'Inner Box 1 kg',
                'description' => 'Kotak karton isi 1 kg, dibungkus plastik segel.',
                'net_weight_kg' => 1.000,
            ],
            [
                'name' => 'Inner Box 10 kg',
                'description' => 'Kotak karton isi 10 kg untuk pembelian curah.',
                'net_weight_kg' => 10.000,
            ],
            [
                'name' => 'Master Carton 20 kg',
                'description' => 'Master carton standar ekspor isi 20 kg.',
                'net_weight_kg' => 20.000,
            ],
            [
                'name' => 'Kemasan Custom Buyer',
                'description' => 'Kemasan mengikuti desain dan merek buyer.',
                'net_weight_kg' => null,
            ],
        ];

        foreach ($options as $position => $option) {
            PackagingOption::query()->firstOrCreate(
                ["name->{$locale}" => $option['name']],
                [
                    'name' => [$locale => $option['name']],
                    'description' => [$locale => $option['description']],
                    'net_weight_kg' => $option['net_weight_kg'],
                    'sort_order' => $position,
                    'is_active' => true,
                ],
            );
        }
    }
}
