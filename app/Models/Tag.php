<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\TagFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;
use Spatie\Translatable\HasTranslations;

/**
 * Tag artikel. Tidak memakai soft delete: menghapus tag hanya melepas
 * kaitannya dari artikel, tidak menghilangkan isinya.
 *
 * @see docs/04-erd.md bagian 3.4
 *
 * @property int $id
 * @property array<string, string> $name
 * @property array<string, string> $slug
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Post> $posts
 */
final class Tag extends Model
{
    /** @use HasFactory<TagFactory> */
    use HasFactory, HasTranslations;

    /**
     * @var list<string>
     */
    public array $translatable = ['name', 'slug'];

    /**
     * @var list<string>
     */
    protected $fillable = ['name', 'slug'];

    /**
     * @return BelongsToMany<Post, $this>
     */
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'post_tag');
    }
}
