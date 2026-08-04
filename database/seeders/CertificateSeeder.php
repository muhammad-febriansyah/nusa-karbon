<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Certificate;
use App\Support\Locale;
use Illuminate\Database\Seeder;

/**
 * Sertifikat yang lazim dipegang eksportir briket arang.
 *
 * Logo penerbit dan berkas scan diunggah admin belakangan; nomor dan tanggal
 * di sini masih contoh sampai dokumen resmi klien diterima.
 */
final class CertificateSeeder extends Seeder
{
    public function run(): void
    {
        $locale = Locale::default();

        $certificates = [
            [
                'name' => 'ISO 9001:2015',
                'issuer' => 'TUV Rheinland',
                'description' => 'Sistem manajemen mutu untuk proses produksi dan pengendalian kualitas.',
                'show_in_trust_bar' => true,
            ],
            [
                'name' => 'SGS Quality Inspection',
                'issuer' => 'SGS Indonesia',
                'description' => 'Inspeksi kualitas pihak ketiga untuk setiap batch ekspor.',
                'show_in_trust_bar' => true,
            ],
            [
                'name' => 'Phytosanitary Certificate',
                'issuer' => 'Badan Karantina Indonesia',
                'description' => 'Sertifikat karantina tumbuhan untuk pengiriman ke luar negeri.',
                'show_in_trust_bar' => false,
            ],
            [
                'name' => 'Certificate of Origin',
                'issuer' => 'Kementerian Perdagangan',
                'description' => 'Surat keterangan asal barang untuk keperluan bea masuk negara tujuan.',
                'show_in_trust_bar' => false,
            ],
        ];

        foreach ($certificates as $position => $certificate) {
            Certificate::query()->firstOrCreate(
                ["name->{$locale}" => $certificate['name']],
                [
                    'name' => [$locale => $certificate['name']],
                    'issuer' => $certificate['issuer'],
                    'description' => [$locale => $certificate['description']],
                    'issued_at' => now()->subYear()->startOfYear()->toDateString(),
                    'expires_at' => now()->addYears(2)->endOfYear()->toDateString(),
                    'show_in_trust_bar' => $certificate['show_in_trust_bar'],
                    'sort_order' => $position,
                    'is_active' => true,
                ],
            );
        }
    }
}
