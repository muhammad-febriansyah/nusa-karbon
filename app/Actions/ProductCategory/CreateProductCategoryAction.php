<?php

declare(strict_types=1);

namespace App\Actions\ProductCategory;

use App\Models\ProductCategory;
use App\Repositories\Contracts\ProductCategoryRepositoryInterface;

final readonly class CreateProductCategoryAction
{
    public function __construct(
        private ProductCategoryRepositoryInterface $categories,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data): ProductCategory
    {
        $data['sort_order'] = $this->categories->nextSortOrder();

        return $this->categories->create($data);
    }
}
