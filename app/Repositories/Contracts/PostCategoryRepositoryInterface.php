<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\PostCategory;

/**
 * @extends SortableContentRepositoryInterface<PostCategory>
 */
interface PostCategoryRepositoryInterface extends SortableContentRepositoryInterface
{
    public function slugExists(string $slug, ?int $exceptId = null): bool;

    public function countPosts(int $postCategoryId): int;
}
