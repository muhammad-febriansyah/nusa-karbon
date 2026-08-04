<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\ProductCategory;

use App\Models\ProductCategory;

final class UpdateProductCategoryRequest extends StoreProductCategoryRequest
{
    /**
     * Kategori yang sedang diubah dikecualikan dari pemeriksaan slug unik.
     */
    protected function currentCategoryId(): ?int
    {
        $category = $this->route('product_category');

        return $category instanceof ProductCategory ? $category->id : null;
    }
}
