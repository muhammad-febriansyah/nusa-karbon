<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Models\ProductCategory;
use App\Support\Locale;
use RuntimeException;

/**
 * Dilempar saat kategori masih dipakai produk. Pesannya menyebut jumlahnya
 * agar admin tahu apa yang harus dibereskan lebih dulu.
 *
 * @see docs/05-design-system.md bagian 4.4
 */
final class CategoryInUseException extends RuntimeException
{
    public function __construct(
        public readonly ProductCategory $category,
        public readonly int $productCount,
    ) {
        parent::__construct(sprintf(
            'Kategori "%s" masih dipakai oleh %d produk. Pindahkan produk tersebut ke kategori lain terlebih dahulu.',
            $category->getTranslation('name', Locale::default()),
            $productCount,
        ));
    }
}
