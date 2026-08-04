<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Faq;
use Illuminate\Database\Eloquent\Collection;

/**
 * @extends SortableContentRepositoryInterface<Faq>
 */
interface FaqRepositoryInterface extends SortableContentRepositoryInterface
{
    /**
     * Seluruh FAQ beserta kategorinya, diurutkan per kategori lalu per urutan
     * di dalam kategori.
     *
     * @return Collection<int, Faq>
     */
    public function orderedWithCategory(): Collection;

    /**
     * FAQ aktif beserta kategorinya, urut kategori lalu urutan tampil.
     *
     * Terpisah dari `orderedWithCategory()` yang dipakai admin: daftar admin
     * sengaja memuat yang nonaktif supaya bisa diaktifkan kembali, sedangkan
     * halaman publik tidak boleh menampilkannya sama sekali.
     *
     * @return Collection<int, Faq>
     */
    public function activeWithCategory(): Collection;

    /**
     * Nomor urut berikutnya di dalam satu kategori. FAQ tanpa kategori
     * memakai urutannya sendiri.
     */
    public function nextSortOrderInCategory(?int $faqCategoryId): int;
}
