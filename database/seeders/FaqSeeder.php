<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Faq;
use App\Models\FaqCategory;
use App\Support\Locale;
use Illuminate\Database\Seeder;

/**
 * Pertanyaan yang paling sering diajukan buyer briket arang.
 *
 * Angka MOQ dan termin di bawah ini masih contoh sampai klien memastikan
 * kebijakan resminya.
 */
final class FaqSeeder extends Seeder
{
    public function run(): void
    {
        $locale = Locale::default();

        $groups = [
            'Pemesanan' => [
                ['Berapa minimum order untuk pengiriman pertama?', 'MOQ standar adalah satu kontainer 20FT. Untuk sampel, kami melayani pengiriman kecil lewat kurir dengan biaya ditanggung buyer.'],
                ['Apakah bisa memesan kemasan dengan merek sendiri?', 'Bisa. Desain kemasan, ukuran inner box, dan cetakan merek mengikuti permintaan buyer.'],
            ],
            'Pengiriman' => [
                ['Berapa lama waktu produksi sampai siap kirim?', 'Rata-rata 14 sampai 21 hari kerja setelah pembayaran uang muka, tergantung volume pesanan.'],
                ['Incoterms apa saja yang dilayani?', 'FOB, CIF, dan CFR. Untuk term lain silakan tanyakan lebih dulu agar kami hitung ulang biayanya.'],
            ],
            'Pembayaran' => [
                ['Bagaimana termin pembayarannya?', 'Umumnya 30% uang muka dan 70% sebelum dokumen dikirim. Termin lain bisa dibicarakan untuk pesanan berulang.'],
            ],
            'Kualitas' => [
                ['Apakah tersedia dokumen COA dan MSDS?', 'Tersedia untuk setiap batch. Dokumen dikirim bersama shipping documents dan bisa diunduh di halaman produk.'],
                ['Bagaimana kalau kualitas tidak sesuai spesifikasi?', 'Sertakan foto dan hasil uji pihak ketiga. Bila terbukti menyimpang dari spesifikasi, kami ganti pada pengiriman berikutnya.'],
            ],
        ];

        $categoryPosition = 0;

        foreach ($groups as $name => $questions) {
            $category = FaqCategory::query()->firstOrCreate(
                ["name->{$locale}" => $name],
                [
                    'name' => [$locale => $name],
                    'sort_order' => $categoryPosition,
                    'is_active' => true,
                ],
            );

            foreach ($questions as $faqPosition => [$question, $answer]) {
                Faq::query()->firstOrCreate(
                    ["question->{$locale}" => $question],
                    [
                        'faq_category_id' => $category->id,
                        'question' => [$locale => $question],
                        'answer' => [$locale => $answer],
                        'sort_order' => $faqPosition,
                        'is_active' => true,
                    ],
                );
            }

            $categoryPosition++;
        }
    }
}
