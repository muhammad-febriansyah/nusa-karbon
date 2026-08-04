<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ContainerType;
use App\Support\Locale;
use Illuminate\Database\Seeder;

/**
 * Dua kontainer yang dipakai untuk ekspor briket.
 *
 * `max_payload_kg` dan `max_cbm` adalah kapasitas muat praktis, bukan volume
 * geometris kontainer — angkanya dipakai langsung Container Calculator.
 *
 * @see docs/04-erd.md bagian 3.2
 */
final class ContainerTypeSeeder extends Seeder
{
    public function run(): void
    {
        $locale = Locale::default();

        $types = [
            [
                'code' => '20FT_DV',
                'name' => '20FT Dry Van',
                'max_payload_kg' => 18000.00,
                'max_cbm' => 28.00,
                'internal_length_m' => 5.90,
                'internal_width_m' => 2.35,
                'internal_height_m' => 2.39,
            ],
            [
                'code' => '40FT_HC',
                'name' => '40FT High Cube',
                'max_payload_kg' => 26000.00,
                'max_cbm' => 68.00,
                'internal_length_m' => 12.03,
                'internal_width_m' => 2.35,
                'internal_height_m' => 2.69,
            ],
        ];

        foreach ($types as $position => $type) {
            ContainerType::query()->firstOrCreate(
                ['code' => $type['code']],
                [
                    'name' => [$locale => $type['name']],
                    'max_payload_kg' => $type['max_payload_kg'],
                    'max_cbm' => $type['max_cbm'],
                    'internal_length_m' => $type['internal_length_m'],
                    'internal_width_m' => $type['internal_width_m'],
                    'internal_height_m' => $type['internal_height_m'],
                    'sort_order' => $position,
                    'is_active' => true,
                ],
            );
        }
    }
}
