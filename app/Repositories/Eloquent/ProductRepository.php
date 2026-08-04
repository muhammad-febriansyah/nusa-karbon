<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Support\Locale;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * @extends BaseRepository<Product>
 */
final class ProductRepository extends BaseRepository implements ProductRepositoryInterface
{
    /**
     * Ditetapkan di konstruktor agar mengikuti `config/locales.php`.
     *
     * @var array<int, string>
     */
    protected array $searchable = [];

    public function __construct()
    {
        $this->searchable = ['code', 'name->'.Locale::default()];
    }

    /**
     * @var array<int, string>
     */
    protected array $sortable = ['sort_order', 'code', 'created_at', 'is_active'];

    protected string $defaultSortBy = 'sort_order';

    /**
     * @var 'asc'|'desc'
     */
    protected string $defaultSortDir = 'asc';

    /**
     * Eager load eksplisit: kategori dan sampul dipakai di setiap baris tabel.
     *
     * @return Builder<Product>
     */
    protected function query(): Builder
    {
        return Product::query()->with(['category:id,name', 'media']);
    }

    /**
     * @param  Builder<Product>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<Product>
     */
    protected function applyFilters(Builder $query, array $filters): Builder
    {
        $query = parent::applyFilters($query, $filters);

        $category = $filters['product_category_id'] ?? null;

        if ($category !== null && $category !== '') {
            $query->where('product_category_id', (int) $category);
        }

        $status = $filters['is_active'] ?? null;

        if ($status !== null && $status !== '') {
            $query->where('is_active', filter_var($status, FILTER_VALIDATE_BOOLEAN));
        }

        // Menyaring produk yang belum bisa masuk Container Calculator.
        if (($filters['missing_carton'] ?? null) === '1') {
            $query->where(function (Builder $query): void {
                $query->whereNull('master_carton_weight_kg')
                    ->orWhereNull('master_carton_cbm');
            });
        }

        return $query;
    }

    public function findBySlugOrFail(string $slug, string $locale): Product
    {
        return Product::query()
            ->where("slug->{$locale}", $slug)
            ->firstOrFail();
    }

    /**
     * @return Collection<int, Product>
     */
    public function activeForPublic(
        ?int $categoryId = null,
        ?string $sort = null,
    ): Collection {
        $products = Product::query()
            ->active()
            ->with(['category:id,name,slug', 'media'])
            ->when(
                $categoryId !== null,
                fn (Builder $query): Builder => $query->where('product_category_id', $categoryId),
            )
            ->orderBy('sort_order')
            ->get();

        return $this->applyPublicSort($products, $sort);
    }

    /**
     * @return array<int, int>
     */
    public function activeCountByCategory(): array
    {
        /** @var array<int, int> $counts */
        $counts = Product::query()
            ->active()
            ->whereNotNull('product_category_id')
            ->selectRaw('product_category_id, COUNT(*) as total')
            ->groupBy('product_category_id')
            ->pluck('total', 'product_category_id')
            ->map(fn (mixed $total): int => (int) $total)
            ->all();

        return $counts;
    }

    /**
     * Urutan katalog publik, dikerjakan setelah data diambil.
     *
     * Bukan di SQL. `size_mm` tersimpan sebagai teks bebas ("22 x 22 x 22"),
     * dan mengurutkannya butuh fungsi pemotong string yang namanya berbeda di
     * tiap mesin basis data — versi MySQL-nya membuat fitur ini mustahil diuji
     * di SQLite. Katalog publik memuat seluruh produk aktif sekaligus tanpa
     * paginasi, jadi mengurutkannya di PHP tidak menambah kueri apa pun.
     *
     * Produk tanpa nilai selalu didorong ke belakang, bukan dianggap nol,
     * supaya baris kosong tidak memenuhi layar pertama.
     *
     * @param  Collection<int, Product>  $products
     * @return Collection<int, Product>
     */
    private function applyPublicSort(Collection $products, ?string $sort): Collection
    {
        $sorted = match ($sort) {
            'ukuran' => $products->sortBy(
                fn (Product $product): float => self::leadingNumber($product->size_mm),
            ),
            'moq' => $products->sortBy(
                fn (Product $product): float => $product->moq_kg === null
                    ? INF
                    : (float) $product->moq_kg,
            ),
            default => $products,
        };

        return $sorted->values();
    }

    /**
     * Angka pertama pada teks ukuran, atau tak hingga bila tidak ada.
     *
     * "22 x 22 x 22" menghasilkan 22. Nilai tak hingga membuat produk tanpa
     * ukuran selalu berada di urutan paling belakang.
     */
    private static function leadingNumber(?string $value): float
    {
        if ($value === null) {
            return INF;
        }

        preg_match('/\d+(?:[.,]\d+)?/', $value, $matches);

        return $matches === []
            ? INF
            : (float) str_replace(',', '.', $matches[0]);
    }

    public function findActiveBySlug(string $slug, string $locale): ?Product
    {
        return Product::query()
            ->active()
            ->where("slug->{$locale}", $slug)
            ->first();
    }

    /**
     * @return Collection<int, Product>
     */
    public function calculable(): Collection
    {
        return Product::query()
            ->calculable()
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * @return Collection<int, Product>
     */
    public function featuredForPublic(int $limit): Collection
    {
        return Product::query()
            ->active()
            ->with(['category:id,name', 'media'])
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->limit($limit)
            ->get();
    }

    public function nextSortOrder(): int
    {
        return (int) Product::query()->max('sort_order') + 1;
    }

    public function codeExists(string $code, ?int $exceptId = null): bool
    {
        return Product::query()
            ->where('code', $code)
            ->when($exceptId !== null, fn (Builder $query) => $query->whereKeyNot($exceptId))
            ->exists();
    }

    public function slugExists(string $locale, string $slug, ?int $exceptId = null): bool
    {
        return Product::query()
            ->where("slug->{$locale}", $slug)
            ->when($exceptId !== null, fn (Builder $query) => $query->whereKeyNot($exceptId))
            ->exists();
    }

    /**
     * @param  array<int, int>  $ids
     */
    public function setActiveStatus(array $ids, bool $isActive): int
    {
        return Product::query()
            ->whereIn('id', $ids)
            ->update(['is_active' => $isActive]);
    }

    /**
     * @param  array<int, int>  $ids
     */
    public function deleteMany(array $ids): int
    {
        return Product::query()->whereIn('id', $ids)->delete();
    }

    public function countByCategory(int $categoryId): int
    {
        return Product::query()
            ->where('product_category_id', $categoryId)
            ->count();
    }
}
