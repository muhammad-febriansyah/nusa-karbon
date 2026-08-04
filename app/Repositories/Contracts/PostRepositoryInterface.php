<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Post;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * @extends BaseRepositoryInterface<Post>
 */
interface PostRepositoryInterface extends BaseRepositoryInterface
{
    public function slugExists(string $slug, ?int $exceptId = null): bool;

    /**
     * Artikel terjadwal yang waktu tayangnya sudah lewat.
     *
     * @return Collection<int, Post>
     */
    public function dueForPublishing(): Collection;

    /**
     * Artikel tayang terbaru untuk beranda dan blok artikel terkait.
     *
     * @return Collection<int, Post>
     */
    public function latestPublished(int $limit): Collection;

    public function countPublished(): int;

    /**
     * Artikel terbit untuk halaman blog, boleh disaring per kategori.
     *
     * Berbeda dari `latestPublished()` yang dibatasi jumlah untuk beranda:
     * halaman blog memang tempat pengunjung menelusuri seluruh arsip.
     *
     * @return Collection<int, Post>
     */
    public function publishedForPublic(?int $categoryId = null): Collection;

    /**
     * Artikel terbit per halaman untuk arsip blog.
     *
     * Dipaginasi di basis data, bukan setelah data diambil seperti katalog
     * produk. Urutannya `published_at` — kolom tanggal yang bisa diurutkan
     * langsung oleh mesin basis data, jadi tidak ada alasan menarik seluruh
     * arsip ke memori lebih dulu.
     *
     * @return LengthAwarePaginator<int, Post>
     */
    public function publishedPaginated(
        ?int $categoryId,
        int $perPage,
    ): LengthAwarePaginator;

    /**
     * Artikel terbit berdasarkan slug, atau `null` bila tidak ada.
     *
     * Menyaring status terbit di query, bukan setelahnya: draf dan artikel
     * terjadwal punya slug juga, dan tanpa saringan ini siapa pun yang menebak
     * alamatnya bisa membaca tulisan yang belum dirilis.
     */
    public function findPublishedBySlug(string $slug, string $locale): ?Post;
}
