<?php

declare(strict_types=1);

namespace App\Actions\Product;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;

final readonly class DeleteProductAction
{
    public function __construct(
        private ProductRepositoryInterface $products,
    ) {}

    /**
     * Soft delete: media sengaja tidak ikut dihapus supaya produk yang
     * dipulihkan dari arsip tetap membawa gambarnya.
     */
    public function handle(Product $product): bool
    {
        return $this->products->delete($product);
    }
}
