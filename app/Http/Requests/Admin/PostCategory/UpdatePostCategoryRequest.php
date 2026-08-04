<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\PostCategory;

use App\Models\PostCategory;

final class UpdatePostCategoryRequest extends StorePostCategoryRequest
{
    protected function currentPostCategoryId(): ?int
    {
        $category = $this->route('post_category');

        return $category instanceof PostCategory ? $category->id : null;
    }
}
