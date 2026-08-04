<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\PageBlockType;
use App\Models\PageBlock;
use App\Support\Locale;
use Illuminate\Database\Seeder;

/**
 * Seluruh kunci blok halaman statis. Halaman publik memanggil blok lewat
 * pasangan `page_key` + `block_key`, jadi daftar ini adalah kontraknya.
 *
 * Menambah blok baru berarti menambah entri di sini, bukan lewat UI admin.
 *
 * @see docs/04-erd.md bagian 3.5
 */
final class PageBlockSeeder extends Seeder
{
    public function run(): void
    {
        $locale = Locale::default();

        $blocks = [
            // Beranda
            ['home', 'hero', PageBlockType::TextImage, 'Briket Arang Batok Kelapa Kualitas Ekspor', 'Pasokan stabil, dokumen lengkap, dan kualitas yang bisa diverifikasi.'],
            ['home', 'intro', PageBlockType::RichText, 'Mitra Ekspor Anda dari Indonesia', 'Kami menghubungkan pabrik terpilih dengan buyer di lebih dari 20 negara.'],
            ['home', 'cta', PageBlockType::RichText, 'Butuh penawaran?', 'Kirim kebutuhan Anda dan kami balas dengan harga serta jadwal kirim.'],

            // Tentang kami
            ['about', 'company_profile', PageBlockType::RichText, 'Profil Perusahaan', 'Nusacarbon adalah trader dan mitra rantai pasok briket arang batok kelapa asal Indonesia.'],
            ['about', 'supply_chain_position', PageBlockType::TextImage, 'Posisi Kami di Rantai Pasok', 'Kami bekerja bersama pabrik mitra, mengendalikan mutu, dan mengurus seluruh dokumen ekspor.'],
            ['about', 'why_trader', PageBlockType::ListItems, 'Kenapa Lewat Trader', 'Satu pintu untuk banyak pabrik, kualitas terjaga, dan risiko pengiriman ditangani.'],
            ['about', 'commitment', PageBlockType::RichText, 'Komitmen Kami', 'Transparansi harga, kejujuran spesifikasi, dan jadwal kirim yang ditepati.'],

            // Informasi ekspor
            ['export_info', 'intro', PageBlockType::RichText, 'Informasi Ekspor', 'Semua yang perlu diketahui sebelum memesan: MOQ, termin bayar, dan proses pengiriman.'],
            ['export_info', 'moq', PageBlockType::Stat, 'Minimum Order', 'Satu kontainer 20FT untuk pesanan reguler.'],
            ['export_info', 'payment_terms', PageBlockType::ListItems, 'Termin Pembayaran', '30% uang muka, 70% sebelum dokumen dikirim.'],
            ['export_info', 'shipping_terms', PageBlockType::ListItems, 'Termin Pengiriman', 'FOB, CIF, dan CFR dari pelabuhan Surabaya atau Jakarta.'],
            ['export_info', 'documents', PageBlockType::ListItems, 'Dokumen yang Disiapkan', 'COA, MSDS, Packing List, Certificate of Origin, dan dokumen karantina.'],
        ];

        foreach ($blocks as $position => [$pageKey, $blockKey, $type, $title, $content]) {
            PageBlock::query()->firstOrCreate(
                ['page_key' => $pageKey, 'block_key' => $blockKey],
                [
                    'type' => $type,
                    'title' => [$locale => $title],
                    'content' => [$locale => $content],
                    'sort_order' => $position,
                    'is_active' => true,
                ],
            );
        }
    }
}
