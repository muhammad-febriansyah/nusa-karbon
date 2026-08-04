<?php

declare(strict_types=1);

namespace App\Http\Resources\Admin;

use App\Models\PackagingOption;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin PackagingOption
 */
final class PackagingOptionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->getTranslations('name'),
            'description' => $this->getTranslations('description'),
            'image_url' => $this->imageUrl(),
            'net_weight_kg' => $this->net_weight_kg,
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
            'products_count' => $this->whenCounted('products'),
            'created_at' => $this->created_at?->format('d M Y, H:i'),
        ];
    }
}
