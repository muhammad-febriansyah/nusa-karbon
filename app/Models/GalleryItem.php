<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\GalleryItemType;
use Database\Factories\GalleryItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Spatie\Translatable\HasTranslations;

/**
 * Satu foto atau video di dalam album.
 *
 * @see docs/04-erd.md bagian 3.5
 *
 * @property int $id
 * @property int $gallery_id
 * @property GalleryItemType $type
 * @property string|null $file_path
 * @property string|null $video_url
 * @property array<string, string>|null $caption
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Gallery $gallery
 */
final class GalleryItem extends Model
{
    /** @use HasFactory<GalleryItemFactory> */
    use HasFactory, HasTranslations;

    /**
     * Direktori berkas item pada disk `public`.
     */
    public const FILE_DIRECTORY = 'galleries/items';

    /**
     * @var list<string>
     */
    public array $translatable = ['caption'];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'gallery_id',
        'type',
        'file_path',
        'video_url',
        'caption',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => GalleryItemType::class,
            'gallery_id' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Gallery, $this>
     */
    public function gallery(): BelongsTo
    {
        return $this->belongsTo(Gallery::class);
    }

    public function fileUrl(): ?string
    {
        if ($this->file_path === null || $this->file_path === '') {
            return null;
        }

        return Storage::disk('public')->url($this->file_path);
    }

    /**
     * Alamat yang dipakai pemutar video: berkas unggahan bila ada, kalau tidak
     * URL penyedia luar.
     */
    public function playbackUrl(): ?string
    {
        return $this->fileUrl() ?? $this->video_url;
    }
}
