<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\DocumentType;
use App\Models\ContainerType;
use App\Models\PackagingOption;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductDocument;
use App\Models\ProductSpecification;
use App\Support\Demo\DemoAssetFetcher;
use App\Support\Demo\DemoPdf;
use App\Support\Locale;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Katalog demo: kategori, produk beserta foto dan dokumennya, opsi kemasan,
 * dan tipe kontainer.
 *
 * Angka master carton di sini tetap **data sementara** seperti di
 * `ProductSeeder` — bukan spesifikasi resmi klien.
 */
final class DemoCatalogSeeder extends Seeder
{
    private const TARGET = 30;

    public function __construct(
        private readonly DemoAssetFetcher $assets = new DemoAssetFetcher,
        private readonly DemoPdf $pdf = new DemoPdf,
    ) {}

    public function run(): void
    {
        $this->categories();
        $this->packagingOptions();
        $this->containerTypes();
        $this->products();
    }

    private function categories(): void
    {
        $names = [
            'Arang Shisha Cube', 'Arang Shisha Hexagonal', 'Arang Shisha Finger',
            'Arang Shisha Flat', 'Arang Shisha Silver', 'Arang Shisha Premium',
            'Arang BBQ Pillow', 'Arang BBQ Hexagonal', 'Arang BBQ Cube',
            'Arang BBQ Lump', 'Arang BBQ Restoran', 'Arang BBQ Retail',
            'Arang Batok Kelapa', 'Arang Kayu Keras', 'Arang Kayu Mahoni',
            'Arang Kayu Halaban', 'Arang Serbuk Gergaji', 'Arang Sekam',
            'Arang Aktif Granular', 'Arang Aktif Powder', 'Arang Aktif Pellet',
            'Arang Industri Peleburan', 'Arang Industri Filter', 'Arang Kompor Rumah Tangga',
            'Arang Yakiniku', 'Arang Binchotan', 'Arang Pertanian Biochar',
            'Arang Peternakan', 'Charcoal Fines', 'Arang OEM Custom',
        ];

        $locale = Locale::default();

        foreach ($names as $position => $name) {
            ProductCategory::query()->updateOrCreate(
                ['slug->'.$locale => Str::slug($name)],
                [
                    'name' => [$locale => $name],
                    'slug' => [$locale => Str::slug($name)],
                    'description' => [$locale => "Kelompok produk {$name} untuk pasar ekspor."],
                    'icon' => 'package',
                    'sort_order' => $position,
                    'is_active' => true,
                ],
            );
        }
    }

    private function packagingOptions(): void
    {
        $options = [
            ['Inner Box 0,5 kg', 0.5], ['Inner Box 1 kg', 1.0], ['Inner Box 1,5 kg', 1.5],
            ['Inner Box 2 kg', 2.0], ['Inner Box 3 kg', 3.0], ['Inner Box 5 kg', 5.0],
            ['Inner Box 10 kg', 10.0], ['Master Carton 10 kg', 10.0], ['Master Carton 15 kg', 15.0],
            ['Master Carton 18 kg', 18.0], ['Master Carton 20 kg', 20.0], ['Master Carton 25 kg', 25.0],
            ['Master Carton 30 kg', 30.0], ['Karung Woven 20 kg', 20.0], ['Karung Woven 25 kg', 25.0],
            ['Karung Woven 50 kg', 50.0], ['Jumbo Bag 500 kg', 500.0], ['Jumbo Bag 1000 kg', 1000.0],
            ['Kraft Paper Bag 3 kg', 3.0], ['Kraft Paper Bag 5 kg', 5.0], ['Kraft Paper Bag 10 kg', 10.0],
            ['Vacuum Pack 1 kg', 1.0], ['Vacuum Pack 2 kg', 2.0], ['Shrink Wrap Pallet', null],
            ['Display Box Retail 24 pcs', null], ['Tin Can 1 kg', 1.0], ['Tin Can 2 kg', 2.0],
            ['Bucket Plastik 5 kg', 5.0], ['Kemasan Label Buyer', null], ['Kemasan Custom Buyer', null],
        ];

        $locale = Locale::default();

        foreach ($options as $position => [$name, $weight]) {
            $option = PackagingOption::query()->updateOrCreate(
                ['name->'.$locale => $name],
                [
                    'name' => [$locale => $name],
                    'description' => [$locale => "Opsi kemasan {$name} sesuai permintaan buyer."],
                    'net_weight_kg' => $weight,
                    'sort_order' => $position,
                    'is_active' => true,
                ],
            );

            if ($option->image_path === null) {
                $option->forceFill([
                    'image_path' => $this->assets->image('packaging', $position, 'packaging-options', Str::slug($name)),
                ])->save();
            }
        }
    }

    /**
     * Tipe kontainer ISO yang benar-benar dipakai di angkutan laut. Jumlahnya
     * berhenti di daftar nyata; menambah kode karangan hanya membuat kalkulator
     * memberi angka yang tidak bisa dipesan.
     */
    private function containerTypes(): void
    {
        $types = [
            ['20FT_DV', '20FT Dry Van', 28200, 33.2, 5.90, 2.35, 2.39],
            ['40FT_DV', '40FT Dry Van', 26680, 67.7, 12.03, 2.35, 2.39],
            ['40FT_HC', '40FT High Cube', 26460, 76.4, 12.03, 2.35, 2.69],
            ['45FT_HC', '45FT High Cube', 27600, 86.0, 13.56, 2.35, 2.69],
            ['40FT_HC_PW', '40FT High Cube Pallet Wide', 26000, 78.0, 12.03, 2.44, 2.69],
            ['20FT_OT', '20FT Open Top', 28130, 32.5, 5.89, 2.35, 2.35],
            ['40FT_OT', '40FT Open Top', 26490, 66.7, 12.03, 2.35, 2.35],
            ['20FT_FR', '20FT Flat Rack', 31260, 0.0, 5.94, 2.40, 2.23],
            ['40FT_FR', '40FT Flat Rack', 39340, 0.0, 12.13, 2.40, 2.14],
            ['20FT_RF', '20FT Reefer', 27400, 28.3, 5.44, 2.29, 2.27],
            ['40FT_RH', '40FT Reefer High Cube', 29520, 67.3, 11.56, 2.29, 2.50],
            ['20FT_VT', '20FT Ventilated', 28100, 32.8, 5.90, 2.35, 2.39],
            ['20FT_HH', '20FT Half Height', 30000, 16.0, 5.89, 2.35, 1.15],
            ['20FT_BU', '20FT Bulk', 27800, 31.6, 5.89, 2.35, 2.35],
            ['20FT_OS', '20FT Open Side', 27500, 32.0, 5.89, 2.35, 2.35],
            ['20FT_TK', '20FT Tank', 26000, 24.0, null, null, null],
            ['10FT_DV', '10FT Dry Van', 8000, 16.0, 2.83, 2.35, 2.39],
            ['30FT_DV', '30FT Dry Van', 26000, 50.0, 8.93, 2.35, 2.39],
            ['48FT_HC', '48FT High Cube', 20000, 92.0, 14.63, 2.44, 2.69],
            ['53FT_HC', '53FT High Cube', 20000, 108.0, 16.15, 2.60, 2.69],
        ];

        $locale = Locale::default();

        foreach ($types as $position => [$code, $name, $payload, $cbm, $length, $width, $height]) {
            ContainerType::query()->updateOrCreate(
                ['code' => $code],
                [
                    'name' => [$locale => $name],
                    'max_payload_kg' => $payload,
                    'max_cbm' => $cbm,
                    'internal_length_m' => $length,
                    'internal_width_m' => $width,
                    'internal_height_m' => $height,
                    'sort_order' => $position,
                    // Flat rack tidak punya volume tertutup, jadi tidak dipakai
                    // Container Calculator.
                    'is_active' => $cbm > 0,
                ],
            );
        }
    }

    private function products(): void
    {
        $locale = Locale::default();

        /** @var array<string, int> $categoryIds */
        $categoryIds = ProductCategory::query()
            ->get(['id', 'name'])
            ->mapWithKeys(fn (ProductCategory $category): array => [
                $category->getTranslation('name', $locale) => $category->id,
            ])
            ->all();
        $packagingIds = PackagingOption::query()->orderBy('sort_order')->pluck('id')->all();

        $blueprints = $this->productBlueprints();

        foreach ($blueprints as $position => $blueprint) {
            // Dikunci pada slug, bukan kode: `ProductSeeder` sudah membuat
            // beberapa produk dengan nama yang sama tapi kode berbeda, dan dua
            // baris bernama persis sama membingungkan saat demo.
            $product = Product::query()->updateOrCreate(
                ['slug->'.$locale => Str::slug($blueprint['name'])],
                [
                    'code' => $blueprint['code'],
                    'product_category_id' => $categoryIds[$blueprint['category']] ?? null,
                    'name' => [$locale => $blueprint['name']],
                    'slug' => [$locale => Str::slug($blueprint['name'])],
                    'short_description' => [$locale => $blueprint['short']],
                    'description' => [$locale => $blueprint['description']],
                    'shape' => $blueprint['shape'],
                    'size_mm' => $blueprint['size'],
                    'master_carton_weight_kg' => $blueprint['weight'],
                    'master_carton_tare_kg' => $blueprint['tare'],
                    'master_carton_cbm' => $blueprint['cbm'],
                    'master_carton_length_cm' => $blueprint['length'],
                    'master_carton_width_cm' => $blueprint['width'],
                    'master_carton_height_cm' => $blueprint['height'],
                    'inner_box_weight_kg' => $blueprint['inner_weight'],
                    'inner_boxes_per_carton' => $blueprint['inner_count'],
                    'moq_kg' => $blueprint['moq'],
                    'meta_title' => [$locale => $blueprint['name'].' — Nusacarbon'],
                    'meta_description' => [$locale => $blueprint['short']],
                    'sort_order' => $position,
                    'is_active' => true,
                    'is_featured' => $position < 6,
                ],
            );

            $this->specifications($product, $blueprint);
            $this->media($product, $position, $blueprint);
            $this->documents($product, $blueprint);

            $product->packagingOptions()->syncWithoutDetaching([
                $packagingIds[$position % count($packagingIds)],
                $packagingIds[($position + 7) % count($packagingIds)],
            ]);
        }

        $this->fillMissingCovers();
    }

    /**
     * Produk lama dari `ProductSeeder` belum punya gambar. Tanpa ini daftar
     * produk memperlihatkan campuran baris bergambar dan berkotak kosong.
     */
    private function fillMissingCovers(): void
    {
        $topics = ['charcoal', 'shisha', 'barbecue', 'coconut'];

        Product::query()->get()->each(function (Product $product, int $index) use ($topics): void {
            if ($product->getMedia(Product::COLLECTION_COVER)->isNotEmpty()) {
                return;
            }

            $this->attach($product, Product::COLLECTION_COVER, $topics[$index % count($topics)], $index + 41);
            $this->attach($product, Product::COLLECTION_GALLERY, 'packaging', $index + 42);
            $this->attach($product, Product::COLLECTION_GALLERY, 'factory', $index + 43);

            $blueprint = [
                'name' => $product->getTranslation('name', Locale::default()),
                'carbon' => 78,
                'ash' => 2.5,
                'moisture' => 5.0,
                'calorie' => 7000,
                'burn' => 120,
                'weight' => $product->master_carton_weight_kg ?? 20.0,
                'tare' => $product->master_carton_tare_kg ?? 1.2,
                'cbm' => $product->master_carton_cbm ?? 0.042,
                'inner_count' => $product->inner_boxes_per_carton ?? 20,
                'moq' => $product->moq_kg ?? 1000.0,
            ];

            $this->specifications($product, $blueprint);
            $this->documents($product, $blueprint);
        });
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function productBlueprints(): array
    {
        // Kolom terakhir adalah kategori tujuan; tanpa itu produk shisha bisa
        // mendarat di kategori BBQ dan langsung terlihat salah saat demo.
        $families = [
            ['Briket Shisha Cube', 'Cube', 'shisha', [22, 25, 26, 28, 30], 'Arang Shisha Cube'],
            ['Briket Shisha Hexagonal', 'Hexagonal', 'shisha', [22, 26, 28], 'Arang Shisha Hexagonal'],
            ['Briket Shisha Finger', 'Finger', 'shisha', [20, 24], 'Arang Shisha Finger'],
            ['Briket Shisha Flat', 'Flat', 'shisha', [26], 'Arang Shisha Flat'],
            ['Briket BBQ Pillow', 'Pillow', 'barbecue', [50, 60], 'Arang BBQ Pillow'],
            ['Briket BBQ Hexagonal', 'Hexagonal', 'barbecue', [40, 50], 'Arang BBQ Hexagonal'],
            ['Briket BBQ Cube', 'Cube', 'barbecue', [40, 50], 'Arang BBQ Cube'],
            ['Arang Kayu Lump', null, 'charcoal', [0], 'Arang BBQ Lump'],
            ['Arang Batok Kelapa Granular', null, 'coconut', [0], 'Arang Batok Kelapa'],
            ['Arang Halus Industri', null, 'factory', [0], 'Arang Industri Peleburan'],
            ['Arang Aktif Granular', null, 'laboratory', [0], 'Arang Aktif Granular'],
            ['Briket Yakiniku', 'Cylinder', 'barbecue', [30], 'Arang Yakiniku'],
            ['Briket Binchotan Substitute', 'Stick', 'charcoal', [25], 'Arang Binchotan'],
            ['Arang Aktif Powder', null, 'laboratory', [0], 'Arang Aktif Powder'],
            ['Arang Aktif Pellet', null, 'laboratory', [0], 'Arang Aktif Pellet'],
            ['Biochar Pertanian', null, 'coconut', [0], 'Arang Pertanian Biochar'],
            ['Arang Sekam Padi', null, 'coconut', [0], 'Arang Sekam'],
            ['Briket Kompor Rumah Tangga', 'Cube', 'charcoal', [35], 'Arang Kompor Rumah Tangga'],
            ['Briket OEM Custom', null, 'packaging', [0], 'Arang OEM Custom'],
        ];

        $blueprints = [];
        $index = 0;

        foreach ($families as [$family, $shape, $topic, $sizes, $category]) {
            foreach ($sizes as $size) {
                if (count($blueprints) >= self::TARGET) {
                    break 2;
                }

                $name = $size > 0 ? "{$family} {$size} mm" : $family;
                $weight = 15.0 + (($index % 4) * 5.0);
                $cbm = round(0.030 + (($index % 5) * 0.006), 4);

                $blueprints[] = [
                    'code' => Str::upper(Str::slug($name, '-')),
                    'name' => $name,
                    'shape' => $shape,
                    'size' => $size > 0 ? "{$size} x {$size} x {$size}" : null,
                    'topic' => $topic,
                    'category' => $category,
                    'short' => "{$name} dari batok kelapa pilihan, kadar abu rendah dan bakar bersih.",
                    'description' => implode("\n\n", [
                        "{$name} diproduksi dari batok kelapa tua yang dikarbonisasi pada suhu terkendali, lalu dipres dan dikeringkan sampai kadar air stabil.",
                        'Setiap batch melewati uji kadar abu, kadar air, dan nilai kalor sebelum dikemas. Hasil ujinya disertakan dalam COA per pengiriman.',
                        'Kemasan mengikuti permintaan buyer: inner box berlabel, master carton polos, atau desain penuh milik buyer.',
                    ]),
                    'weight' => $weight,
                    'tare' => round(0.9 + (($index % 3) * 0.2), 3),
                    'cbm' => $cbm,
                    'length' => 40.0 + (($index % 3) * 5),
                    'width' => 30.0 + (($index % 2) * 5),
                    'height' => 25.0 + (($index % 4) * 5),
                    'inner_weight' => 1.0 + ($index % 3),
                    'inner_count' => 10 + (($index % 4) * 5),
                    'moq' => 1000.0 * (1 + ($index % 3)),
                    'carbon' => 78 + ($index % 6),
                    'ash' => round(2.0 + (($index % 5) * 0.3), 1),
                    'moisture' => round(4.0 + (($index % 4) * 0.5), 1),
                    'calorie' => 6800 + (($index % 6) * 120),
                    'burn' => 90 + (($index % 7) * 15),
                ];

                $index++;
            }
        }

        return $blueprints;
    }

    /**
     * @param  array<string, mixed>  $blueprint
     */
    private function specifications(Product $product, array $blueprint): void
    {
        $locale = Locale::default();

        $rows = [
            ['Fixed Carbon', (string) $blueprint['carbon'], '% min', true],
            ['Ash Content', str_replace('.', ',', (string) $blueprint['ash']), '% max', true],
            ['Moisture Content', str_replace('.', ',', (string) $blueprint['moisture']), '% max', false],
            ['Volatile Matter', '15', '% max', false],
            ['Calorific Value', number_format((float) $blueprint['calorie'], 0, ',', '.'), 'kcal/kg', true],
            ['Burning Time', (string) $blueprint['burn'], 'menit', false],
            ['Density', '1,05', 'g/cm3', false],
            ['Sulphur Content', '0,1', '% max', false],
            ['Odour', 'Tanpa aroma', null, false],
        ];

        foreach ($rows as $position => [$parameter, $value, $unit, $highlighted]) {
            ProductSpecification::query()->updateOrCreate(
                ['product_id' => $product->id, 'parameter->'.$locale => $parameter],
                [
                    'parameter' => [$locale => $parameter],
                    'value' => $value,
                    'unit' => $unit,
                    'is_highlighted' => $highlighted,
                    'sort_order' => $position,
                ],
            );
        }
    }

    /**
     * @param  array<string, mixed>  $blueprint
     */
    private function media(Product $product, int $position, array $blueprint): void
    {
        /** @var string $topic */
        $topic = $blueprint['topic'];

        if ($product->getMedia(Product::COLLECTION_COVER)->isEmpty()) {
            $this->attach($product, Product::COLLECTION_COVER, $topic, $position);
        }

        if ($product->getMedia(Product::COLLECTION_GALLERY)->count() < 3) {
            foreach (['packaging', 'factory', $topic] as $offset => $galleryTopic) {
                $this->attach($product, Product::COLLECTION_GALLERY, $galleryTopic, $position + $offset + 1);
            }
        }
    }

    private function attach(Product $product, string $collection, string $topic, int $index): void
    {
        $path = $this->assets->image($topic, $index, 'demo-source', $product->code.'-'.$collection);

        if ($path === null) {
            return;
        }

        // Lewat disk, bukan path absolut, supaya jalan juga saat disk dipalsukan
        // di test. Medialibrary memindahkan berkasnya, jadi `demo-source` kosong
        // kembali setelah seeder selesai.
        $product->addMediaFromDisk($path, 'public')->toMediaCollection($collection);
    }

    /**
     * @param  array<string, mixed>  $blueprint
     */
    private function documents(Product $product, array $blueprint): void
    {
        if ($product->documents()->exists()) {
            return;
        }

        $locale = Locale::default();
        $batch = 'SP-'.now()->format('y').str_pad((string) ($product->id % 99), 2, '0', STR_PAD_LEFT);

        $documents = [
            [
                DocumentType::Coa,
                'Certificate of Analysis',
                'CERTIFICATE OF ANALYSIS',
                [
                    ['Product', (string) $blueprint['name']],
                    ['Batch Reference', $batch],
                    ['Fixed Carbon', $blueprint['carbon'].' % min'],
                    ['Ash Content', $blueprint['ash'].' % max'],
                    ['Moisture Content', $blueprint['moisture'].' % max'],
                    ['Calorific Value', $blueprint['calorie'].' kcal/kg'],
                    ['Burning Time', $blueprint['burn'].' minutes'],
                    ['Tested By', 'Nusacarbon QC Laboratory'],
                ],
            ],
            [
                DocumentType::Msds,
                'Material Safety Data Sheet',
                'MATERIAL SAFETY DATA SHEET',
                [
                    ['Product', (string) $blueprint['name']],
                    ['Chemical Family', 'Carbonised coconut shell'],
                    ['Appearance', 'Solid black briquette, odourless'],
                    ['Flash Point', 'Not applicable (solid fuel)'],
                    ['Storage', 'Keep dry, away from open flame'],
                    ['Handling', 'Use gloves and dust mask when handling fines'],
                    ['Transport Class', 'Non-hazardous for sea freight'],
                ],
            ],
            [
                DocumentType::PackingList,
                'Packing List',
                'PACKING LIST',
                [
                    ['Product', (string) $blueprint['name']],
                    ['Batch Reference', $batch],
                    ['Master Carton Net', $blueprint['weight'].' kg'],
                    ['Master Carton Gross', ($blueprint['weight'] + $blueprint['tare']).' kg'],
                    ['Carton Volume', $blueprint['cbm'].' CBM'],
                    ['Inner Boxes / Carton', (string) $blueprint['inner_count']],
                    ['Minimum Order', $blueprint['moq'].' kg'],
                ],
            ],
        ];

        foreach ($documents as [$type, $title, $heading, $rows]) {
            $fileName = Str::slug($product->code.'-'.$title).'.pdf';
            $path = 'product-documents/'.$fileName;

            $size = $this->pdf->write(
                $path,
                $heading,
                $blueprint['name'].' — batch '.$batch,
                $rows,
            );

            ProductDocument::query()->create([
                'product_id' => $product->id,
                'type' => $type,
                'title' => [$locale => $title],
                'file_path' => $path,
                'file_name' => $fileName,
                'file_size' => $size,
                'mime_type' => 'application/pdf',
                'batch_reference' => $batch,
                'issued_at' => Carbon::now()->subDays($product->id % 90),
                'download_count' => ($product->id * 7) % 40,
                'is_active' => true,
            ]);
        }
    }
}
