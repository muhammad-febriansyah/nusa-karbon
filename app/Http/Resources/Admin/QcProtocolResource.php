<?php

declare(strict_types=1);

namespace App\Http\Resources\Admin;

use App\Models\QcProtocol;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin QcProtocol
 */
final class QcProtocolResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->getTranslations('title'),
            'description' => $this->getTranslations('description'),
            'result_label' => $this->getTranslations('result_label'),
            'result_value' => $this->result_value,
            'image_url' => $this->imageUrl(),
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at?->format('d M Y, H:i'),
        ];
    }
}
