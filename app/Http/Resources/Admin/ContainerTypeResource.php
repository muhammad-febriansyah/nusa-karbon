<?php

declare(strict_types=1);

namespace App\Http\Resources\Admin;

use App\Models\ContainerType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ContainerType
 */
final class ContainerTypeResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->getTranslations('name'),
            'max_payload_kg' => $this->max_payload_kg,
            'max_cbm' => $this->max_cbm,
            'internal_length_m' => $this->internal_length_m,
            'internal_width_m' => $this->internal_width_m,
            'internal_height_m' => $this->internal_height_m,
            'internal_volume_cbm' => $this->internalVolumeCbm(),
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at?->format('d M Y, H:i'),
        ];
    }
}
