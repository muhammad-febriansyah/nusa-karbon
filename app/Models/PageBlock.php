<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PageBlockType;
use Database\Factories\PageBlockFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Spatie\Translatable\HasTranslations;

/**
 * Blok konten halaman statis.
 *
 * `page_key` dan `block_key` ditentukan seeder dan tidak bisa diubah dari UI —
 * halaman publik memanggil blok berdasarkan kunci itu, jadi mengubahnya berarti
 * mengosongkan bagian halaman tanpa jejak.
 *
 * @see docs/04-erd.md bagian 3.5
 *
 * @property int $id
 * @property string $page_key
 * @property string $block_key
 * @property PageBlockType $type
 * @property array<string, string>|null $title
 * @property array<string, string>|null $content
 * @property string|null $image_path
 * @property array<string, mixed>|null $extra
 * @property int $sort_order
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
final class PageBlock extends Model
{
    /** @use HasFactory<PageBlockFactory> */
    use HasFactory, HasTranslations;

    /**
     * Direktori gambar blok pada disk `public`.
     */
    public const IMAGE_DIRECTORY = 'page-blocks';

    /**
     * @var list<string>
     */
    public array $translatable = ['title', 'content'];

    /**
     * Kunci sengaja tidak fillable: hanya seeder yang boleh membuatnya.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'content',
        'image_path',
        'extra',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => PageBlockType::class,
            'extra' => 'array',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function imageUrl(): ?string
    {
        if ($this->image_path === null || $this->image_path === '') {
            return null;
        }

        return Storage::disk('public')->url($this->image_path);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeForPageKey(Builder $query, string $pageKey): Builder
    {
        return $query->where('page_key', $pageKey)->orderBy('sort_order');
    }
}
