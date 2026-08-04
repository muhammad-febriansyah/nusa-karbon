<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

/**
 * @extends BaseRepositoryInterface<Product>
 */
interface ProductRepositoryInterface extends BaseRepositoryInterface
{
    public function findBySlugOrFail(string $slug, string $locale): Product;

    /**
     * Produk aktif untuk katalog publik, boleh disaring per kategori.
     *
     * Terpisah dari `featuredForPublic()` yang mendahulukan unggulan: katalog
     * mengikuti urutan yang disusun admin apa adanya, karena pengunjung sedang
     * menelusuri seluruh pilihan, bukan melihat etalase.
     *
     * @return Collection<int, Product>
     */
    public function activeForPublic(
        ?int $categoryId = null,
        ?string $sort = null,
    ): Collection;

    /**
     * Jumlah produk aktif per kategori.
     *
     * Dipakai penyaring katalog untuk menuliskan jumlahnya di tiap pilihan —
     * pembeli jadi tahu apa yang akan didapat sebelum menekan, alih-alih
     * mencoba satu per satu dan menemukan sebagian besar hanya berisi satu
     * produk.
     *
     * @return array<int, int> dipetakan `product_category_id` ke jumlahnya
     */
    public function activeCountByCategory(): array;

    /**
     * Produk aktif berdasarkan slug, atau `null` bila tidak ada.
     *
     * `findBySlugOrFail()` tidak menyaring status aktif — memakainya di rute
     * publik akan menampilkan produk yang sudah dinonaktifkan admin kepada
     * siapa pun yang menyimpan tautannya.
     */
    public function findActiveBySlug(string $slug, string $locale): ?Product;

    /**
     * Produk yang layak masuk Container Calculator.
     *
     * @return Collection<int, Product>
     */
    public function calculable(): Collection;

    /**
     * Etalase produk untuk halaman publik: hanya yang aktif, unggulan lebih
     * dulu, sisanya mengikuti urutan manual admin.
     *
     * @return Collection<int, Product>
     */
    public function featuredForPublic(int $limit): Collection;

    public function nextSortOrder(): int;

    public function codeExists(string $code, ?int $exceptId = null): bool;

    public function slugExists(string $locale, string $slug, ?int $exceptId = null): bool;

    /**
     * @param  array<int, int>  $ids
     */
    public function setActiveStatus(array $ids, bool $isActive): int;

    /**
     * @param  array<int, int>  $ids
     */
    public function deleteMany(array $ids): int;

    public function countByCategory(int $categoryId): int;
}
