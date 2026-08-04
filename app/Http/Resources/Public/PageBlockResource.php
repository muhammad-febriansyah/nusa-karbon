<?php

declare(strict_types=1);

namespace App\Http\Resources\Public;

use App\Models\PageBlock;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Blok konten halaman statis.
 *
 * Dikirim ke frontend sebagai peta `block_key` ke blok, bukan larik berurutan,
 * supaya halaman memanggil bloknya lewat nama (`blocks.hero`) alih-alih indeks.
 * Urutan blok pada halaman publik ditentukan tata letaknya, bukan `sort_order`
 * admin.
 *
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
            'key' => $this->block_key,
            'type' => $this->type->value,
            'title' => $this->text('title'),
            'content' => $this->text('content'),
            'image_url' => $this->imageUrl(),
            'extra' => $this->extra,
        ];
    }

    private function text(string $key): ?string
    {
        $value = trim((string) $this->getTranslation($key, app()->getLocale()));

        return $value === '' ? null : $value;
    }
}
