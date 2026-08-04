<?php

declare(strict_types=1);

namespace App\Http\Resources\Public;

use App\Models\Post;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Satu artikel lengkap dengan isinya.
 *
 * Berbeda dari `PostCardResource` yang sengaja membuang `content`: di sini
 * isinya justru yang dibaca. HTML-nya sudah dibersihkan Purifier saat disimpan
 * admin, jadi aman dirender apa adanya di sisi klien.
 *
 * @mixin Post
 */
final class PostDetailResource extends JsonResource
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
            'content' => $this->text('content'),
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
            'author' => $this->whenLoaded(
                'author',
                fn (): ?string => $this->author?->name,
            ),
            'tags' => $this->tagList(),
        ];
    }

    /**
     * @return list<string>
     */
    private function tagList(): array
    {
        $tags = $this->tags
            ->map(fn (Tag $tag): string => (string) $tag->getTranslation('name', app()->getLocale()))
            ->all();

        return array_values($tags);
    }

    private function text(string $key): ?string
    {
        $value = trim((string) $this->getTranslation($key, app()->getLocale()));

        return $value === '' ? null : $value;
    }
}
