<?php

declare(strict_types=1);

namespace App\Actions\ProductCategory;

use App\Exceptions\CategoryInUseException;
use App\Models\ProductCategory;
use App\Repositories\Contracts\ProductCategoryRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;

final readonly class DeleteProductCategoryAction
{
    public function __construct(
        private ProductCategoryRepositoryInterface $categories,
        private ProductRepositoryInterface $products,
    ) {}

    /**
     * Kategori yang masih dipakai produk tidak boleh dihapus — admin harus
     * memindahkan produknya lebih dulu.
     *
     * @throws CategoryInUseException
     */
    public function handle(ProductCategory $category): bool
    {
        $inUse = $this->products->countByCategory($category->id);

        if ($inUse > 0) {
            throw new CategoryInUseException($category, $inUse);
        }

        return $this->categories->delete($category);
    }
}
