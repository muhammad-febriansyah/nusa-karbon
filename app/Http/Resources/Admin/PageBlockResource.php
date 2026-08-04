<?php

declare(strict_types=1);

namespace App\Http\Resources\Admin;

use App\Models\PageBlock;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin PageBlock
 */
final class PageBlockResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'page_key' => $this->page_key,
            'block_key' => $this->block_key,
            'type' => $this->type->value,
            'type_label' => $this->type->label(),
            'title' => $this->getTranslations('title'),
            'content' => $this->getTranslations('content'),
            'image_url' => $this->imageUrl(),
            'extra' => $this->extra,
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
        ];
    }
}
