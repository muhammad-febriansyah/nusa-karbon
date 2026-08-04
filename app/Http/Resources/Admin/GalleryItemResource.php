<?php

declare(strict_types=1);

namespace App\Http\Resources\Admin;

use App\Models\GalleryItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin GalleryItem
 */
final class GalleryItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type->value,
            'type_label' => $this->type->label(),
            'file_url' => $this->fileUrl(),
            'video_url' => $this->video_url,
            'playback_url' => $this->playbackUrl(),
            'caption' => $this->getTranslations('caption'),
            'sort_order' => $this->sort_order,
        ];
    }
}
