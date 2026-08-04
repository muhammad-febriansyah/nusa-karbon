<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ContainerTypeFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\Translatable\HasTranslations;

/**
 * @property int $id
 * @property string $code
 * @property array<string, string> $name
 * @property float $max_payload_kg
 * @property float $max_cbm
 * @property float|null $internal_length_m
 * @property float|null $internal_width_m
 * @property float|null $internal_height_m
 * @property int $sort_order
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
final class ContainerType extends Model
{
    /** @use HasFactory<ContainerTypeFactory> */
    use HasFactory, HasTranslations, SoftDeletes;

    /**
     * @var list<string>
     */
    public array $translatable = ['name'];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'code',
        'name',
        'max_payload_kg',
        'max_cbm',
        'internal_length_m',
        'internal_width_m',
        'internal_height_m',
        'sort_order',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'max_payload_kg' => 'float',
            'max_cbm' => 'float',
            'internal_length_m' => 'float',
            'internal_width_m' => 'float',
            'internal_height_m' => 'float',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
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
     * Volume internal dalam CBM bila ketiga dimensinya terisi. Dipakai sebagai
     * pembanding informatif terhadap `max_cbm`, bukan penggantinya — `max_cbm`
     * adalah kapasitas muat praktis yang selalu lebih kecil.
     */
    public function internalVolumeCbm(): ?float
    {
        if ($this->internal_length_m === null
            || $this->internal_width_m === null
            || $this->internal_height_m === null) {
            return null;
        }

        return round(
            $this->internal_length_m * $this->internal_width_m * $this->internal_height_m,
            2,
        );
    }
}
