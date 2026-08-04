<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\PostStatus;
use App\Models\Post;
use App\Models\PostCategory;
use App\Support\Locale;
use App\Support\PostContent;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Kategori dan artikel awal untuk pengembangan.
 *
 * Isinya contoh; tulisan resmi menyusul dari klien.
 */
final class PostSeeder extends Seeder
{
    public function run(): void
    {
        $locale = Locale::default();

        $categories = [
            'Panduan Ekspor' => 'Cara kerja ekspor briket dari Indonesia.',
            'Pengetahuan Produk' => 'Spesifikasi, mutu, dan cara memilih briket.',
            'Kabar Perusahaan' => 'Pembaruan operasional dan kemitraan.',
        ];

        $created = [];
        $position = 0;

        foreach ($categories as $name => $description) {
            $created[$name] = PostCategory::query()->firstOrCreate(
                ["slug->{$locale}" => Str::slug($name)],
                [
                    'name' => [$locale => $name],
                    'slug' => [$locale => Str::slug($name)],
                    'description' => [$locale => $description],
                    'sort_order' => $position,
                    'is_active' => true,
                ],
            );

            $position++;
        }

        $posts = [
            [
                'Panduan Ekspor',
                'Memahami Incoterms FOB, CIF, dan CFR untuk Briket',
                'Tiga term paling sering dipakai buyer briket beserta tanggung jawab masing-masing pihak.',
                '<p>FOB berarti tanggung jawab penjual berakhir saat barang naik ke kapal. CIF menambah biaya angkut dan asuransi sampai pelabuhan tujuan, sedangkan CFR sama dengan CIF tanpa asuransi.</p><p>Pilih term yang sesuai kemampuan buyer mengurus pengiriman di negaranya.</p>',
                PostStatus::Published,
            ],
            [
                'Pengetahuan Produk',
                'Cara Membaca Spesifikasi Briket Arang Batok Kelapa',
                'Fixed carbon, kadar abu, kadar air, dan waktu bakar: angka mana yang benar-benar penting.',
                '<p>Fixed carbon menentukan panas, kadar abu menentukan sisa pembakaran, dan kadar air memengaruhi asap. Angka-angka ini harus datang dari hasil uji laboratorium, bukan klaim penjual.</p>',
                PostStatus::Published,
            ],
            [
                'Kabar Perusahaan',
                'Jadwal Produksi Menjelang Akhir Tahun',
                'Slot kontainer akhir tahun cepat penuh; pesan lebih awal agar jadwal kirim terjaga.',
                '<p>Permintaan naik menjelang musim dingin di Eropa dan Timur Tengah. Pemesanan sebaiknya masuk enam minggu sebelum tanggal kirim yang diinginkan.</p>',
                PostStatus::Draft,
            ],
        ];

        foreach ($posts as [$categoryName, $title, $excerpt, $content, $status]) {
            $translations = [$locale => $content];

            Post::query()->firstOrCreate(
                ["slug->{$locale}" => Str::slug($title)],
                [
                    'post_category_id' => $created[$categoryName]->id,
                    'title' => [$locale => $title],
                    'slug' => [$locale => Str::slug($title)],
                    'excerpt' => [$locale => $excerpt],
                    'content' => $translations,
                    'status' => $status,
                    'published_at' => $status === PostStatus::Published ? now()->subDays(3) : null,
                    'reading_minutes' => PostContent::readingMinutes($translations),
                ],
            );
        }
    }
}
