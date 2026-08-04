<?php

declare(strict_types=1);

namespace App\Models;

use App\Observers\BlameableObserver;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Translatable\HasTranslations;

/**
 * @property int $id
 * @property int|null $product_category_id
 * @property string $code
 * @property array<string, string> $name
 * @property array<string, string> $slug
 * @property array<string, string>|null $short_description
 * @property array<string, string>|null $description
 * @property string|null $shape
 * @property string|null $size_mm
 * @property float|null $master_carton_weight_kg
 * @property float|null $master_carton_tare_kg
 * @property float|null $master_carton_cbm
 * @property float|null $master_carton_length_cm
 * @property float|null $master_carton_width_cm
 * @property float|null $master_carton_height_cm
 * @property float|null $inner_box_weight_kg
 * @property int|null $inner_boxes_per_carton
 * @property float|null $moq_kg
 * @property array<string, string>|null $meta_title
 * @property array<string, string>|null $meta_description
 * @property int $sort_order
 * @property bool $is_active
 * @property bool $is_featured
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read ProductCategory|null $category
 * @property-read Collection<int, ProductSpecification> $specifications
 * @property-read Collection<int, ProductDocument> $documents
 * @property-read Collection<int, PackagingOption> $packagingOptions
 * @property-read Collection<int, RfqRequest> $rfqRequests
 */
#[ObservedBy([BlameableObserver::class])]
final class Product extends Model implements HasMedia
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory, HasTranslations, InteractsWithMedia, SoftDeletes;

    public const COLLECTION_COVER = 'cover';

    public const COLLECTION_GALLERY = 'gallery';

    /**
     * @var list<string>
     */
    public array $translatable = [
        'name',
        'slug',
        'short_description',
        'description',
        'meta_title',
        'meta_description',
    ];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'product_category_id',
        'code',
        'name',
        'slug',
        'short_description',
        'description',
        'shape',
        'size_mm',
        'master_carton_weight_kg',
        'master_carton_tare_kg',
        'master_carton_cbm',
        'master_carton_length_cm',
        'master_carton_width_cm',
        'master_carton_height_cm',
        'inner_box_weight_kg',
        'inner_boxes_per_carton',
        'moq_kg',
        'meta_title',
        'meta_description',
        'sort_order',
        'is_active',
        'is_featured',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'master_carton_weight_kg' => 'float',
            'master_carton_tare_kg' => 'float',
            'master_carton_cbm' => 'float',
            'master_carton_length_cm' => 'float',
            'master_carton_width_cm' => 'float',
            'master_carton_height_cm' => 'float',
            'inner_box_weight_kg' => 'float',
            'inner_boxes_per_carton' => 'integer',
            'moq_kg' => 'float',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<ProductCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    /**
     * @return HasMany<ProductSpecification, $this>
     */
    public function specifications(): HasMany
    {
        return $this->hasMany(ProductSpecification::class)->orderBy('sort_order');
    }

    /**
     * @return HasMany<ProductDocument, $this>
     */
    public function documents(): HasMany
    {
        return $this->hasMany(ProductDocument::class)->orderBy('id');
    }

    /**
     * Permintaan penawaran yang menyebut produk ini. Kolomnya nullable karena
     * buyer boleh bertanya tanpa memilih produk.
     *
     * @return HasMany<RfqRequest, $this>
     */
    public function rfqRequests(): HasMany
    {
        return $this->hasMany(RfqRequest::class);
    }

    /**
     * @return BelongsToMany<PackagingOption, $this>
     */
    public function packagingOptions(): BelongsToMany
    {
        return $this->belongsToMany(PackagingOption::class, 'product_packaging_option')
            ->orderBy('packaging_options.sort_order');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::COLLECTION_COVER)->singleFile();
        $this->addMediaCollection(self::COLLECTION_GALLERY);
    }

    /**
     * Konversi `thumb` dibuat langsung agar pratinjau segera muncul di admin;
     * sisanya lewat queue.
     *
     * @see docs/07-admin-ui-standards.md bagian 6.4
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->nonQueued()
            ->fit(Fit::Crop, 300, 225)
            ->format('webp')
            ->quality(82);

        $this->addMediaConversion('medium')
            ->fit(Fit::Contain, 800, 600)
            ->format('webp')
            ->quality(85);

        $this->addMediaConversion('large')
            ->fit(Fit::Contain, 1600, 1200)
            ->format('webp')
            ->quality(85);
    }

    /**
     * Produk hanya muncul di Container Calculator bila berat dan volume master
     * carton keduanya terisi.
     *
     * @see docs/04-erd.md bagian 3.2
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeCalculable(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->whereNotNull('master_carton_weight_kg')
            ->whereNotNull('master_carton_cbm')
            ->where('master_carton_weight_kg', '>', 0)
            ->where('master_carton_cbm', '>', 0);
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
     * Dipakai peringatan inline di admin.
     */
    public function isCalculable(): bool
    {
        return $this->master_carton_weight_kg !== null
            && $this->master_carton_weight_kg > 0
            && $this->master_carton_cbm !== null
            && $this->master_carton_cbm > 0;
    }
}
