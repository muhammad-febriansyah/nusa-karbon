<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ShippingPartnerFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * Mitra pelayaran yang logonya tampil di halaman Export Info.
 *
 * @see docs/04-erd.md bagian 3.3
 *
 * @property int $id
 * @property string $name
 * @property string|null $logo_path
 * @property string|null $website_url
 * @property int $sort_order
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
final class ShippingPartner extends Model
{
    /** @use HasFactory<ShippingPartnerFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Direktori berkas pada disk `public`.
     */
    public const FILE_DIRECTORY = 'shipping-partners';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'logo_path',
        'website_url',
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

    public function logoUrl(): ?string
    {
        if ($this->logo_path === null || $this->logo_path === '') {
            return null;
        }

        return Storage::disk('public')->url($this->logo_path);
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
