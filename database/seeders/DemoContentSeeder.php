<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\GalleryItemType;
use App\Enums\PostStatus;
use App\Models\Faq;
use App\Models\FaqCategory;
use App\Models\Gallery;
use App\Models\GalleryItem;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Tag;
use App\Models\User;
use App\Support\Demo\DemoAssetFetcher;
use App\Support\Locale;
use App\Support\PostContent;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Isi situs demo: kategori artikel, tag, artikel, FAQ, dan galeri.
 */
final class DemoContentSeeder extends Seeder
{
    public function __construct(
        private readonly DemoAssetFetcher $assets = new DemoAssetFetcher,
    ) {}

    public function run(): void
    {
        $this->postCategories();
        $this->tags();
        $this->posts();
        $this->faqs();
        $this->galleries();
    }

    private function postCategories(): void
    {
        $names = [
            'Panduan Ekspor', 'Spesifikasi Produk', 'Kualitas & QC', 'Proses Produksi',
            'Kemasan', 'Logistik & Pengapalan', 'Dokumen Ekspor', 'Incoterms',
            'Pasar Timur Tengah', 'Pasar Eropa', 'Pasar Asia', 'Pasar Amerika',
            'Tips Buyer Baru', 'Harga & Penawaran', 'Kontrak Dagang', 'Pembayaran Ekspor',
            'Container Calculator', 'Bahan Baku', 'Keberlanjutan', 'Sertifikasi',
            'Regulasi Impor', 'Studi Kasus Buyer', 'Berita Perusahaan', 'Pameran Dagang',
            'Shisha & Hookah', 'Barbecue & Grill', 'Arang Industri', 'Arang Aktif',
            'Troubleshooting Mutu', 'Glosarium Istilah',
        ];

        $this->translatedList(PostCategory::class, $names, withSlug: true);
    }

    private function tags(): void
    {
        $names = [
            'briket', 'batok kelapa', 'shisha', 'hookah', 'barbecue', 'grill',
            'ekspor', 'impor', 'kontainer', 'fob', 'cif', 'incoterms',
            'coa', 'msds', 'packing list', 'phytosanitary', 'fumigasi', 'moq',
            'kadar abu', 'kadar air', 'fixed carbon', 'nilai kalor', 'lama bakar',
            'oem', 'private label', 'timur tengah', 'eropa', 'asia', 'qc', 'sertifikasi',
        ];

        $locale = Locale::default();

        foreach ($names as $name) {
            Tag::query()->updateOrCreate(
                ['slug->'.$locale => Str::slug($name)],
                ['name' => [$locale => Str::title($name)], 'slug' => [$locale => Str::slug($name)]],
            );
        }
    }

    private function posts(): void
    {
        $titles = [
            'Cara Memilih Briket Shisha untuk Pasar Timur Tengah',
            'Perbedaan Kadar Abu 2 Persen dan 3 Persen pada Briket',
            'Menghitung Muatan Kontainer 20FT untuk Briket Batok Kelapa',
            'Panduan Membaca Certificate of Analysis Briket',
            'Kenapa Kadar Air Menentukan Lama Bakar Briket',
            'FOB atau CIF: Mana yang Cocok untuk Buyer Baru',
            'Dokumen Ekspor yang Wajib Disiapkan Sebelum Kapal Berangkat',
            'Mengenal Bentuk Briket: Cube, Hexagonal, Finger, dan Flat',
            'Standar Kemasan Inner Box untuk Pasar Retail Eropa',
            'Proses Karbonisasi Batok Kelapa dari Awal sampai Arang Siap Giling',
            'Cara Kami Menjaga Konsistensi Mutu Antar Batch',
            'Apa Itu Fixed Carbon dan Mengapa Buyer Selalu Menanyakannya',
            'Menyiapkan Trial Order Satu Kontainer untuk Buyer Pertama',
            'Fumigasi Pallet dan Aturan ISPM 15 untuk Ekspor',
            'Perbedaan Briket Shisha dan Briket Barbecue',
            'Menentukan MOQ yang Masuk Akal untuk Kontrak Berulang',
            'Cara Menghindari Briket Retak Selama Perjalanan Laut',
            'Peluang Pasar Briket di Uni Emirat Arab',
            'Tren Permintaan Briket Barbecue di Eropa',
            'Regulasi Impor Arang di Korea Selatan dan Jepang',
            'Checklist Sebelum Menandatangani Kontrak Ekspor Briket',
            'Menghitung Harga FOB dari Biaya Produksi',
            'Mengelola Pembayaran L/C at Sight untuk Ekspor Briket',
            'Kesalahan Umum Buyer Baru Saat Memesan Briket',
            'Cara Memverifikasi Perwakilan Resmi Nusacarbon',
            'Studi Kasus: Kontrak Berulang Enam Bulan ke Jeddah',
            'Bagaimana Kami Menguji Lama Bakar di Laboratorium Internal',
            'Arang Aktif: Produk Turunan Batok Kelapa yang Sering Ditanya',
            'Mengurangi Debu pada Kemasan Briket Shisha',
            'Glosarium Istilah Perdagangan Briket untuk Buyer Baru',
        ];

        $locale = Locale::default();
        $categoryIds = PostCategory::query()->orderBy('sort_order')->pluck('id')->all();
        $tagIds = Tag::query()->pluck('id')->all();
        $authorId = User::query()->value('id');

        foreach ($titles as $position => $title) {
            $content = $this->postBody($title);

            $status = match (true) {
                $position >= 27 => PostStatus::Draft,
                $position === 26 => PostStatus::Scheduled,
                default => PostStatus::Published,
            };

            $publishedAt = match ($status) {
                PostStatus::Published => Carbon::now()->subDays(($position + 1) * 4),
                PostStatus::Scheduled => Carbon::now()->addDays(5),
                default => null,
            };

            $post = Post::query()->updateOrCreate(
                ['slug->'.$locale => Str::slug($title)],
                [
                    'post_category_id' => $categoryIds[$position % count($categoryIds)] ?? null,
                    'user_id' => $authorId,
                    'title' => [$locale => $title],
                    'slug' => [$locale => Str::slug($title)],
                    'excerpt' => [$locale => Str::limit(strip_tags($content), 180)],
                    'content' => PostContent::sanitiseTranslations([$locale => $content]),
                    'meta_title' => [$locale => $title.' — Nusacarbon'],
                    'meta_description' => [$locale => Str::limit(strip_tags($content), 150)],
                    'status' => $status,
                    'published_at' => $publishedAt,
                    'view_count' => ($position * 37) % 900,
                    'reading_minutes' => PostContent::readingMinutes([$locale => $content]),
                ],
            );

            $post->tags()->syncWithoutDetaching([
                $tagIds[$position % count($tagIds)],
                $tagIds[($position + 9) % count($tagIds)],
                $tagIds[($position + 17) % count($tagIds)],
            ]);

            if ($post->cover_path === null) {
                $topics = ['charcoal', 'shisha', 'barbecue', 'logistics', 'factory', 'packaging'];

                $post->forceFill([
                    'cover_path' => $this->assets->image(
                        $topics[$position % count($topics)],
                        $position,
                        'posts',
                        Str::slug($title),
                    ),
                ])->save();
            }
        }
    }

    private function postBody(string $title): string
    {
        return implode('', [
            '<p>'.$title.' adalah pertanyaan yang hampir selalu muncul saat buyer baru mulai membandingkan pemasok briket batok kelapa dari Indonesia. Artikel ini merangkum jawabannya dalam bahasa yang bisa langsung dipakai saat negosiasi.</p>',
            '<h2>Yang perlu diperiksa lebih dulu</h2>',
            '<p>Angka pada spesifikasi hanya berarti kalau metode ujinya jelas. Minta hasil uji per batch, bukan hasil uji satu kali yang dipakai berulang untuk semua pengiriman.</p>',
            '<ul><li>Kadar abu dan kadar air diuji per batch produksi.</li><li>Nilai kalor diukur dengan bomb calorimeter.</li><li>Lama bakar diuji pada kondisi ruang yang sama.</li></ul>',
            '<h2>Kesalahan yang sering terjadi</h2>',
            '<p>Membandingkan harga tanpa menyamakan berat bersih master carton membuat penawaran terlihat murah padahal isinya lebih sedikit. Samakan dulu satuan sebelum membandingkan angka.</p>',
            '<blockquote>Harga per kontainer baru bisa dibandingkan setelah jumlah karton dan berat bersihnya sama.</blockquote>',
            '<h2>Langkah berikutnya</h2>',
            '<p>Kirimkan permintaan penawaran dengan menyebut bentuk, ukuran, target kadar abu, dan pelabuhan tujuan. Semakin lengkap datanya, semakin cepat penawaran bisa dikunci.</p>',
        ]);
    }

    private function faqs(): void
    {
        $categories = [
            'Produk', 'Spesifikasi', 'Kualitas', 'Kemasan', 'Pemesanan', 'Harga',
            'Pembayaran', 'Pengiriman', 'Dokumen Ekspor', 'Incoterms', 'MOQ',
            'Sampel', 'Container Calculator', 'Sertifikasi', 'Bahan Baku',
            'Produksi', 'Lead Time', 'Kontrak', 'OEM & Private Label', 'Retur & Klaim',
            'Perwakilan Resmi', 'Keamanan Transaksi', 'Pasar Timur Tengah', 'Pasar Eropa',
            'Pasar Asia', 'Regulasi Impor', 'Penyimpanan', 'Keselamatan', 'Lingkungan', 'Lain-lain',
        ];

        $this->translatedList(FaqCategory::class, $categories);

        $questions = [
            ['Apa bahan baku briket Nusacarbon?', 'Seluruh briket kami dibuat dari batok kelapa tua, bukan campuran kayu atau serbuk gergaji.'],
            ['Berapa kadar abu briket shisha Anda?', 'Kelas premium berada di bawah 2,5 persen; angka pasti per batch tercantum di COA.'],
            ['Apakah briket berbau saat dibakar?', 'Tidak. Perekat yang dipakai food grade dan tidak meninggalkan aroma pada tembakau.'],
            ['Berapa lama briket cube 26 mm bertahan?', 'Rata-rata di atas 120 menit pada kondisi ruang normal tanpa kipas.'],
            ['Ukuran apa saja yang tersedia?', 'Cube 22–30 mm, hexagonal 22–28 mm, finger 20–24 mm, dan pillow 50–60 mm.'],
            ['Bisakah memesan ukuran khusus?', 'Bisa. Ukuran khusus dihitung ulang berat karton dan volumenya sebelum penawaran keluar.'],
            ['Berapa MOQ untuk buyer baru?', 'Satu kontainer 20FT untuk trial order, lalu MOQ berikutnya menyesuaikan kontrak.'],
            ['Apakah tersedia sampel?', 'Ya. Produk sampel gratis, ongkos kirim ditanggung buyer.'],
            ['Berapa lama waktu produksi?', 'Umumnya 18–25 hari kerja setelah uang muka diterima, tergantung antrean produksi.'],
            ['Incoterms apa yang tersedia?', 'FOB Surabaya atau Jakarta, dan CIF ke pelabuhan tujuan yang diminta.'],
            ['Metode pembayaran apa yang diterima?', 'T/T dengan uang muka 30 persen, atau L/C at sight melalui bank devisa.'],
            ['Apakah harga bisa dikunci untuk kontrak panjang?', 'Bisa, untuk kontrak enam sampai dua belas bulan dengan jadwal pengiriman tetap.'],
            ['Dokumen apa yang disertakan tiap pengiriman?', 'Invoice, packing list, bill of lading, COO, phytosanitary, dan COA batch.'],
            ['Apakah pallet difumigasi?', 'Ya, seluruh pallet kayu difumigasi sesuai ISPM 15 dan disertai sertifikat.'],
            ['Bagaimana cara menghitung muatan kontainer?', 'Gunakan Container Calculator di situs kami; angkanya memakai berat dan volume master carton resmi.'],
            ['Apakah briket bisa dikemas dengan merek kami?', 'Bisa. Desain inner box dan master carton dicetak sesuai file buyer.'],
            ['Berapa minimum order untuk kemasan OEM?', 'Dua kontainer 40HC agar biaya cetak kemasan masuk akal.'],
            ['Bagaimana jika mutu tidak sesuai COA?', 'Kirim foto dan nomor batch; kami investigasi batch tersebut dan menyepakati kompensasi.'],
            ['Apakah Nusacarbon punya perwakilan di negara saya?', 'Cek halaman Verify Our Representative sebelum melakukan pembayaran apa pun.'],
            ['Bagaimana menghindari penipuan mengatasnamakan Nusacarbon?', 'Pembayaran hanya ke rekening perusahaan; nama perwakilan wajib ada di halaman verifikasi.'],
            ['Sertifikat apa yang dimiliki pabrik?', 'ISO 9001, ISO 14001, ISO 45001, serta sertifikat produk dan legalitas ekspor.'],
            ['Apakah produk lolos uji logam berat?', 'Ya, diuji laboratorium terakreditasi setiap semester.'],
            ['Bagaimana cara menyimpan briket?', 'Simpan di tempat kering, jauh dari lantai basah, dan hindari sinar matahari langsung.'],
            ['Berapa lama masa simpan briket?', 'Dua tahun bila kemasan utuh dan gudang kering.'],
            ['Apakah briket aman untuk makanan?', 'Aman. Perekat food grade dan tidak memakai bahan pemercepat nyala.'],
            ['Pelabuhan mana yang biasa dipakai?', 'Tanjung Perak Surabaya dan Tanjung Priok Jakarta.'],
            ['Berapa lama pengiriman ke Timur Tengah?', 'Sekitar 18–24 hari berlayar ke Jebel Ali, tergantung jadwal kapal.'],
            ['Apakah bisa kirim ke pelabuhan kecil?', 'Bisa lewat transshipment; biaya tambahannya dihitung di penawaran CIF.'],
            ['Apakah tersedia arang aktif?', 'Tersedia dalam bentuk granular, powder, dan pellet dengan spesifikasi terpisah.'],
            ['Bagaimana cara meminta penawaran?', 'Isi form RFQ dengan bentuk, ukuran, volume bulanan, dan pelabuhan tujuan.'],
        ];

        $locale = Locale::default();
        $categoryIds = FaqCategory::query()->orderBy('sort_order')->pluck('id')->all();

        foreach ($questions as $position => [$question, $answer]) {
            Faq::query()->updateOrCreate(
                ['question->'.$locale => $question],
                [
                    'faq_category_id' => $categoryIds[$position % count($categoryIds)] ?? null,
                    'question' => [$locale => $question],
                    'answer' => [$locale => $answer],
                    'sort_order' => $position,
                    'is_active' => true,
                ],
            );
        }
    }

    private function galleries(): void
    {
        $albums = [
            ['Pabrik Briket Nusacarbon', 'factory'], ['Gudang Bahan Baku', 'coconut'],
            ['Proses Karbonisasi', 'factory'], ['Mesin Penggilingan', 'factory'],
            ['Pencetakan Briket', 'factory'], ['Oven Pengeringan', 'factory'],
            ['Laboratorium QC', 'laboratory'], ['Uji Kadar Air', 'laboratory'],
            ['Uji Nilai Kalor', 'laboratory'], ['Uji Lama Bakar', 'laboratory'],
            ['Sortir Visual', 'factory'], ['Pengemasan Inner Box', 'packaging'],
            ['Pengemasan Master Carton', 'packaging'], ['Penandaan Karton', 'packaging'],
            ['Palletisasi', 'warehouse'], ['Shrink Wrapping', 'warehouse'],
            ['Gudang Barang Jadi', 'warehouse'], ['Inspeksi Pra-Muat', 'warehouse'],
            ['Stuffing Kontainer', 'logistics'], ['Penyegelan Kontainer', 'logistics'],
            ['Pengangkutan ke Pelabuhan', 'logistics'], ['Terminal Peti Kemas', 'logistics'],
            ['Kapal Berangkat', 'logistics'], ['Produk Briket Shisha', 'shisha'],
            ['Produk Briket Barbecue', 'barbecue'], ['Produk Arang Lump', 'charcoal'],
            ['Kemasan Retail Buyer', 'packaging'], ['Pameran Dagang Dubai', 'shisha'],
            ['Kunjungan Buyer ke Pabrik', 'factory'], ['Tim Produksi', 'factory'],
        ];

        $locale = Locale::default();

        foreach ($albums as $position => [$title, $topic]) {
            $gallery = Gallery::query()->updateOrCreate(
                ['slug->'.$locale => Str::slug($title)],
                [
                    'title' => [$locale => $title],
                    'slug' => [$locale => Str::slug($title)],
                    'description' => [$locale => "Dokumentasi {$title} di fasilitas Nusacarbon."],
                    'sort_order' => $position,
                    'is_active' => true,
                ],
            );

            if ($gallery->cover_path === null) {
                $gallery->forceFill([
                    'cover_path' => $this->assets->image($topic, $position, 'galleries', Str::slug($title).'-cover'),
                ])->save();
            }

            if ($gallery->items()->exists()) {
                continue;
            }

            for ($index = 0; $index < 4; $index++) {
                $path = $this->assets->image(
                    $topic,
                    $position + $index + 1,
                    'galleries/'.Str::slug($title),
                    'foto',
                );

                if ($path === null) {
                    continue;
                }

                GalleryItem::query()->create([
                    'gallery_id' => $gallery->id,
                    'type' => GalleryItemType::Image,
                    'file_path' => $path,
                    'caption' => [$locale => $title.' — dokumentasi '.($index + 1)],
                    'sort_order' => $index,
                ]);
            }
        }
    }

    /**
     * Daftar bernama translatable dengan urutan tetap.
     *
     * @param  class-string<PostCategory|FaqCategory>  $model
     * @param  array<int, string>  $names
     */
    private function translatedList(string $model, array $names, bool $withSlug = false): void
    {
        $locale = Locale::default();

        foreach ($names as $position => $name) {
            $attributes = [
                'name' => [$locale => $name],
                'sort_order' => $position,
                'is_active' => true,
            ];

            $key = ['name->'.$locale => $name];

            if ($withSlug) {
                $attributes['slug'] = [$locale => Str::slug($name)];
                $attributes['description'] = [$locale => "Artikel seputar {$name}."];
                $key = ['slug->'.$locale => Str::slug($name)];
            }

            $model::query()->updateOrCreate($key, $attributes);
        }
    }
}
