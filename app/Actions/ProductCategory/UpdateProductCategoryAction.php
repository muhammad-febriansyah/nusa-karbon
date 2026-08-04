<?php

declare(strict_types=1);

namespace App\Actions\ProductCategory;

use App\Models\ProductCategory;
use App\Repositories\Contracts\ProductCategoryRepositoryInterface;

final readonly class UpdateProductCategoryAction
{
    public function __construct(
        private ProductCategoryRepositoryInterface $categories,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(ProductCategory $category, array $data): ProductCategory
    {
        return $this->categories->update($category, $data);
    }
}
