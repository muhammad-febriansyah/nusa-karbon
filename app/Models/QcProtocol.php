<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\QcProtocolFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Spatie\Translatable\HasTranslations;

/**
 * Protokol pengujian mutu beserta hasil ujinya.
 *
 * @see docs/04-erd.md bagian 3.3
 *
 * @property int $id
 * @property array<string, string> $title
 * @property array<string, string>|null $description
 * @property array<string, string>|null $result_label
 * @property string|null $result_value
 * @property string|null $image_path
 * @property int $sort_order
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
final class QcProtocol extends Model
{
    /** @use HasFactory<QcProtocolFactory> */
    use HasFactory, HasTranslations, SoftDeletes;

    /**
     * @var list<string>
     */
    public array $translatable = ['title', 'description', 'result_label'];

    /**
     * Direktori berkas pada disk `public`.
     */
    public const FILE_DIRECTORY = 'qc-protocols';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'description',
        'result_label',
        'result_value',
        'image_path',
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
}
