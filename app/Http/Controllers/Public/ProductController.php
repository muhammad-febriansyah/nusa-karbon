<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\Public\ProductCardResource;
use App\Http\Resources\Public\ProductDetailResource;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Repositories\Contracts\ProductCategoryRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Services\ContainerLoadService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Katalog produk publik.
 *
 * Daftar dan detail dipegang satu controller karena keduanya membaca sumber
 * yang sama dan selalu berubah bersamaan.
 */
final class ProductController extends Controller
{
    /** Jumlah produk serupa di bawah halaman detail. */
    private const RELATED_LIMIT = 3;

    public function __construct(
        private readonly ProductRepositoryInterface $products,
        private readonly ProductCategoryRepositoryInterface $categories,
        private readonly ContainerLoadService $containerLoads,
    ) {}

    /**
     * Urutan yang boleh diminta lewat query.
     *
     * Daftar putih, bukan nilai bebas: `urut` masuk langsung ke query builder,
     * dan menerima apa pun dari URL berarti membuka pintu ke kolom yang tidak
     * dimaksudkan.
     */
    /**
     * Produk per halaman katalog.
     *
     * Dua belas mengisi empat baris penuh pada kisi tiga kolom, dan enam baris
     * di tablet — pembeli melihat cukup banyak untuk membandingkan tanpa harus
     * menggulir sebelas baris sampai habis.
     */
    private const PER_PAGE = 12;

    private const SORTS = [
        'urutan' => 'Urutan katalog',
        'ukuran' => 'Ukuran terkecil dulu',
        'moq' => 'MOQ terkecil dulu',
    ];

    public function index(Request $request): Response
    {
        $categories = $this->publicCategories();
        $slug = $request->string('kategori')->toString();
        $sort = $request->string('urut')->toString();
        $sort = array_key_exists($sort, self::SORTS) ? $sort : 'urutan';

        /*
         * Kategori yang tidak dikenal diperlakukan sebagai "tanpa saringan",
         * bukan sebagai halaman kosong. Tautan lama atau salah ketik lebih baik
         * berakhir di katalog penuh daripada di daftar nol produk yang terlihat
         * seperti stok habis.
         */
        $active = $categories->firstWhere(
            fn (ProductCategory $category): bool => $category->getTranslation('slug', app()->getLocale()) === $slug,
        );

        $counts = $this->products->activeCountByCategory();
        $page = $this->paginate(
            $this->products->activeForPublic($active?->id, $sort),
            $request,
        );

        return Inertia::render('public/products/index', [
            'products' => [
                'data' => array_values(
                    ProductCardResource::collection($page->items())->resolve(),
                ),
                'meta' => [
                    'current_page' => $page->currentPage(),
                    'last_page' => $page->lastPage(),
                    'per_page' => $page->perPage(),
                    'from' => $page->firstItem(),
                    'to' => $page->lastItem(),
                    'total' => $page->total(),
                ],
            ],
            'categories' => $categories
                ->map(fn (ProductCategory $category): array => [
                    'id' => $category->id,
                    'name' => $category->getTranslation('name', app()->getLocale()),
                    'slug' => $category->getTranslation('slug', app()->getLocale()),
                    // Jumlahnya ikut supaya pembeli tahu isi tiap pilihan
                    // sebelum menekannya.
                    'total' => $counts[$category->id] ?? 0,
                ])
                ->values()
                ->all(),
            'activeCategory' => $active === null
                ? null
                : $active->getTranslation('slug', app()->getLocale()),
            'activeSort' => $sort,
            'sorts' => array_map(
                fn (string $label, string $value): array => [
                    'value' => $value,
                    'label' => $label,
                ],
                array_values(self::SORTS),
                array_keys(self::SORTS),
            ),
            'totalActive' => array_sum($counts),
        ]);
    }

    public function show(string $slug): Response
    {
        $product = $this->products->findActiveBySlug($slug, app()->getLocale());

        if ($product === null) {
            throw new NotFoundHttpException;
        }

        $product->load([
            'category:id,name,slug',
            'specifications',
            'documents',
            'packagingOptions',
            'media',
        ]);

        return Inertia::render('public/products/show', [
            'product' => ProductDetailResource::make($product)->resolve(),
            /*
             * Muatan tiap jenis kontainer, bukan hanya yang pertama seperti di
             * beranda. Di halaman detail pembeli sedang menyusun pesanan, dan
             * pilihan kontainer justru salah satu hal yang sedang ditimbang.
             */
            'containerLoads' => $this->containerLoads->forProduct($product),
            'related' => ProductCardResource::collection(
                $this->related($product),
            )->resolve(),
        ]);
    }

    /**
     * Memotong koleksi yang sudah diurutkan jadi satu halaman.
     *
     * Dipaginasi setelah pengurutan, bukan di kueri. `size_mm` disimpan sebagai
     * teks bebas sehingga urutannya dihitung di PHP — memotongnya lebih dulu di
     * basis data berarti yang terurut hanya isi halaman itu saja, dan urutan
     * antar halaman jadi acak.
     *
     * Cocok selama katalognya berjumlah puluhan seperti sekarang. Bila kelak
     * mencapai ribuan, ukurannya perlu jadi kolom angka tersendiri supaya
     * pengurutan bisa kembali ke basis data.
     *
     * @param  Collection<int, Product>  $products
     * @return LengthAwarePaginator<int, Product>
     */
    private function paginate(Collection $products, Request $request): LengthAwarePaginator
    {
        $total = $products->count();
        $lastPage = max(1, (int) ceil($total / self::PER_PAGE));

        /*
         * Nomor halaman dijepit ke rentang yang ada. Tautan lama atau angka
         * yang diketik sendiri lebih baik mendarat di halaman terakhir daripada
         * di kisi kosong yang terbaca seperti katalog habis.
         */
        $current = min(max(1, $request->integer('page', 1)), $lastPage);

        return new LengthAwarePaginator(
            $products->forPage($current, self::PER_PAGE)->values(),
            $total,
            self::PER_PAGE,
            $current,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ],
        );
    }

    /**
     * Produk lain dari kategori yang sama.
     *
     * @return Collection<int, Product>
     */
    private function related(Product $product): Collection
    {
        return $this->products
            ->activeForPublic($product->product_category_id)
            ->reject(fn (Product $item): bool => $item->is($product))
            ->take(self::RELATED_LIMIT)
            ->values();
    }

    /**
     * Kategori yang benar-benar punya produk aktif.
     *
     * Basis data memuat lebih banyak kategori daripada produk. Menampilkan
     * semuanya sebagai saringan berarti sebagian besar tombol menghasilkan
     * daftar kosong, dan pengunjung menyimpulkan barangnya habis.
     *
     * @return Collection<int, ProductCategory>
     */
    private function publicCategories(): Collection
    {
        $used = $this->products->activeForPublic()
            ->pluck('product_category_id')
            ->filter()
            ->unique();

        return $this->categories->ordered()
            ->filter(fn (ProductCategory $category): bool => $used->contains($category->id))
            ->values();
    }
}
