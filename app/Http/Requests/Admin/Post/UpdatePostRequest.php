<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Post;

use App\Models\Post;

final class UpdatePostRequest extends StorePostRequest
{
    protected function currentPostId(): ?int
    {
        $post = $this->route('post');

        return $post instanceof Post ? $post->id : null;
    }
}
