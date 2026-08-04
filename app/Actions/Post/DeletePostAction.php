<?php

declare(strict_types=1);

namespace App\Actions\Post;

use App\Models\Post;
use App\Repositories\Contracts\PostRepositoryInterface;

final readonly class DeletePostAction
{
    public function __construct(private PostRepositoryInterface $posts) {}

    /**
     * Soft delete: tautan artikel yang sudah tersebar tetap bisa dipulihkan
     * bila penghapusan ternyata keliru.
     */
    public function handle(Post $post): bool
    {
        return $this->posts->delete($post);
    }
}
