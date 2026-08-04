<?php

declare(strict_types=1);

namespace App\Actions\Post;

use App\Models\Post;
use App\Models\Tag;
use App\Support\Locale;
use Illuminate\Support\Str;

/**
 * Menyamakan tag artikel dengan daftar nama yang dikirim form. Tag yang belum
 * ada dibuat, yang tidak lagi dipakai dilepas kaitannya.
 *
 * @see docs/09-tasks.md bagian 3.4
 */
final readonly class SyncPostTagsAction
{
    /**
     * @param  list<string>  $names
     */
    public function handle(Post $post, array $names): void
    {
        $locale = Locale::default();
        $ids = [];

        foreach ($names as $name) {
            $name = trim($name);

            if ($name === '') {
                continue;
            }

            $slug = Str::slug($name);

            $tag = Tag::query()->firstOrCreate(
                ["slug->{$locale}" => $slug],
                [
                    'name' => [$locale => $name],
                    'slug' => [$locale => $slug],
                ],
            );

            $ids[] = $tag->id;
        }

        $post->tags()->sync($ids);
    }
}
