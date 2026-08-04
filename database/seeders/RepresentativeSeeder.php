<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Representative;
use App\Support\Locale;
use Illuminate\Database\Seeder;

/**
 * Perwakilan resmi contoh untuk pengembangan.
 *
 * Nama, email, dan nomor di sini masih data sementara sampai klien
 * mengirimkan daftar perwakilan resminya. Halaman publik yang memakai data
 * ini adalah alat verifikasi anti-penipuan, jadi jangan pernah dipublikasikan
 * dengan isian contoh.
 */
final class RepresentativeSeeder extends Seeder
{
    public function run(): void
    {
        $locale = Locale::default();

        $representatives = [
            [
                'name' => 'Adi Wicaksono',
                'position' => 'Export Sales Manager',
                'region' => 'MENA',
                'email' => 'adi@nusacarbon.com',
                'phone' => '+622112345678',
                'whatsapp' => '+6281234567890',
            ],
            [
                'name' => 'Rina Kusuma',
                'position' => 'Business Development',
                'region' => 'Europe',
                'email' => 'rina@nusacarbon.com',
                'phone' => '+622112345679',
                'whatsapp' => '+6281234567891',
            ],
        ];

        foreach ($representatives as $position => $representative) {
            Representative::query()->firstOrCreate(
                ['email' => $representative['email']],
                [
                    'name' => $representative['name'],
                    'position' => [$locale => $representative['position']],
                    'region' => $representative['region'],
                    'phone' => $representative['phone'],
                    'whatsapp' => $representative['whatsapp'],
                    'started_at' => now()->subYears(2)->startOfYear()->toDateString(),
                    'sort_order' => $position,
                    'is_active' => true,
                ],
            );
        }
    }
}
