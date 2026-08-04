<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Product;

use App\Models\Product;

final class UpdateProductRequest extends StoreProductRequest
{
    protected function currentProductId(): ?int
    {
        $product = $this->route('product');

        return $product instanceof Product ? $product->id : null;
    }
}
