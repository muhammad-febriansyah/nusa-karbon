<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\GalleryFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Spatie\Translatable\HasTranslations;

/**
 * Album galeri: kumpulan foto dan video produksi, QC, atau stuffing.
 *
 * @see docs/04-erd.md bagian 3.5
 *
 * @property int $id
 * @property array<string, string> $title
 * @property array<string, string> $slug
 * @property array<string, string>|null $description
 * @property string|null $cover_path
 * @property int $sort_order
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Collection<int, GalleryItem> $items
 */
final class Gallery extends Model
{
    /** @use HasFactory<GalleryFactory> */
    use HasFactory, HasTranslations, SoftDeletes;

    /**
     * Direktori sampul album pada disk `public`.
     */
    public const COVER_DIRECTORY = 'galleries/covers';

    /**
     * @var list<string>
     */
    public array $translatable = ['title', 'slug', 'description'];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'slug',
        'description',
        'cover_path',
        'sort_order',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<GalleryItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(GalleryItem::class)->orderBy('sort_order');
    }

    public function coverUrl(): ?string
    {
        if ($this->cover_path === null || $this->cover_path === '') {
            return null;
        }

        return Storage::disk('public')->url($this->cover_path);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
