<?php

declare(strict_types=1);

namespace App\Http\Resources\Public;

use App\Models\Feature;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Poin keunggulan pada beranda.
 *
 * @mixin Feature
 */
final class FeatureResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->text('title'),
            'description' => $this->text('description'),
            // Nama ikon lucide, sudah tervalidasi saat disimpan admin.
            'icon' => $this->icon,
            'image_url' => $this->imageUrl(),
        ];
    }

    private function text(string $key): ?string
    {
        $value = trim((string) $this->getTranslation($key, app()->getLocale()));

        return $value === '' ? null : $value;
    }
}
