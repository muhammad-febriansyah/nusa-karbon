<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PostStatus;
use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Spatie\Translatable\HasTranslations;

/**
 * Artikel blog.
 *
 * @see docs/04-erd.md bagian 3.4
 *
 * @property int $id
 * @property int|null $post_category_id
 * @property int|null $user_id
 * @property array<string, string> $title
 * @property array<string, string> $slug
 * @property array<string, string>|null $excerpt
 * @property array<string, string>|null $content
 * @property string|null $cover_path
 * @property array<string, string>|null $meta_title
 * @property array<string, string>|null $meta_description
 * @property PostStatus $status
 * @property Carbon|null $published_at
 * @property int $view_count
 * @property int|null $reading_minutes
 * @property string|null $search_title
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read PostCategory|null $category
 * @property-read User|null $author
 * @property-read Collection<int, Tag> $tags
 */
final class Post extends Model
{
    /** @use HasFactory<PostFactory> */
    use HasFactory, HasTranslations, SoftDeletes;

    /**
     * Direktori sampul artikel pada disk `public`.
     */
    public const COVER_DIRECTORY = 'posts/covers';

    /**
     * Kecepatan baca rata-rata untuk menghitung `reading_minutes`.
     */
    public const WORDS_PER_MINUTE = 200;

    /**
     * @var list<string>
     */
    public array $translatable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'meta_title',
        'meta_description',
    ];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'post_category_id',
        'user_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'cover_path',
        'meta_title',
        'meta_description',
        'status',
        'published_at',
        'reading_minutes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => PostStatus::class,
            'published_at' => 'datetime',
            'post_category_id' => 'integer',
            'user_id' => 'integer',
            'view_count' => 'integer',
            'reading_minutes' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<PostCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(PostCategory::class, 'post_category_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return BelongsToMany<Tag, $this>
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'post_tag');
    }

    public function coverUrl(): ?string
    {
        if ($this->cover_path === null || $this->cover_path === '') {
            return null;
        }

        return Storage::disk('public')->url($this->cover_path);
    }

    /**
     * Artikel yang sudah tayang: berstatus terbit dan waktu tayangnya lewat.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', PostStatus::Published)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /**
     * Artikel terjadwal yang waktunya sudah tiba; dipakai command penerbitan
     * otomatis.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeDueForPublishing(Builder $query): Builder
    {
        return $query->where('status', PostStatus::Scheduled)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }
}
