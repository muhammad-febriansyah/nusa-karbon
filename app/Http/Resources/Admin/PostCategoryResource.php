<?php

declare(strict_types=1);

namespace App\Http\Resources\Admin;

use App\Models\PostCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin PostCategory
 */
final class PostCategoryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->getTranslations('name'),
            'slug' => $this->getTranslations('slug'),
            'description' => $this->getTranslations('description'),
            'posts_count' => $this->whenCounted('posts'),
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at?->format('d M Y, H:i'),
        ];
    }
}
