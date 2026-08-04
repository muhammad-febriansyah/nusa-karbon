<?php

declare(strict_types=1);

namespace App\Http\Resources\Admin;

use App\Models\Gallery;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Gallery
 */
final class GalleryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->getTranslations('title'),
            'slug' => $this->getTranslations('slug'),
            'description' => $this->getTranslations('description'),
            'cover_url' => $this->coverUrl(),
            'items_count' => $this->whenCounted('items'),
            // `resolve()` supaya koleksi tidak terbungkus kunci `data` saat
            // dikirim sebagai prop Inertia.
            'items' => $this->whenLoaded(
                'items',
                fn () => GalleryItemResource::collection($this->items)->resolve(),
            ),
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at?->format('d M Y, H:i'),
        ];
    }
}
