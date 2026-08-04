<?php

declare(strict_types=1);

namespace App\Http\Resources\Public;

use App\Models\PackagingOption;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Opsi kemasan pada beranda dan halaman Info Ekspor.
 *
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
            'name' => $this->text('name'),
            'description' => $this->text('description'),
            'net_weight_kg' => $this->net_weight_kg,
            'image_url' => $this->imageUrl(),
        ];
    }

    private function text(string $key): ?string
    {
        $value = trim((string) $this->getTranslation($key, app()->getLocale()));

        return $value === '' ? null : $value;
    }
}
