<?php

declare(strict_types=1);

namespace App\Http\Resources\Public;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Artikel dalam bentuk kartu.
 *
 * `content` sengaja tidak ikut. Isinya HTML panjang dan kartu hanya menampilkan
 * kutipan — mengirimnya akan melipatgandakan ukuran payload beranda tanpa ada
 * yang membacanya.
 *
 * @mixin Post
 */
final class PostCardResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->text('title'),
            'slug' => $this->text('slug'),
            'excerpt' => $this->text('excerpt'),
            'cover_url' => $this->coverUrl(),
            'reading_minutes' => $this->reading_minutes,
            'published_at' => $this->published_at?->toIso8601String(),
            'category' => $this->whenLoaded(
                'category',
                fn (): ?string => $this->category?->getTranslation(
                    'name',
                    app()->getLocale(),
                ),
            ),
        ];
    }

    private function text(string $key): ?string
    {
        $value = trim((string) $this->getTranslation($key, app()->getLocale()));

        return $value === '' ? null : $value;
    }
}
