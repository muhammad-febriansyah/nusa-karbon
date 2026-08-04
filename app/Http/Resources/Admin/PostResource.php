<?php

declare(strict_types=1);

namespace App\Http\Resources\Admin;

use App\Models\Post;
use App\Support\Locale;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Post
 */
final class PostResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'post_category_id' => $this->post_category_id,
            'category_name' => $this->whenLoaded(
                'category',
                fn () => $this->category?->getTranslations('name'),
            ),
            'author_name' => $this->whenLoaded('author', fn () => $this->author?->name),
            'title' => $this->getTranslations('title'),
            'slug' => $this->getTranslations('slug'),
            'excerpt' => $this->getTranslations('excerpt'),
            'content' => $this->getTranslations('content'),
            'meta_title' => $this->getTranslations('meta_title'),
            'meta_description' => $this->getTranslations('meta_description'),
            'cover_url' => $this->coverUrl(),
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'status_color' => $this->status->color(),
            'published_at' => $this->published_at?->toDateTimeString(),
            'published_at_display' => $this->published_at?->timezone('Asia/Jakarta')
                ->format('d M Y, H:i').($this->published_at !== null ? ' WIB' : ''),
            'view_count' => $this->view_count,
            'reading_minutes' => $this->reading_minutes,
            'tags' => $this->whenLoaded(
                'tags',
                fn () => $this->tags
                    ->map(fn ($tag): string => $tag->getTranslation('name', Locale::default()))
                    ->values()
                    ->all(),
            ),
            'created_at' => $this->created_at?->format('d M Y, H:i'),
        ];
    }
}
