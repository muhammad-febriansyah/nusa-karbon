<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ProductSpecificationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Spatie\Translatable\HasTranslations;

/**
 * @property int $id
 * @property int $product_id
 * @property array<string, string> $parameter
 * @property string $value
 * @property string|null $unit
 * @property bool $is_highlighted
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Product $product
 */
final class ProductSpecification extends Model
{
    /** @use HasFactory<ProductSpecificationFactory> */
    use HasFactory, HasTranslations;

    /**
     * @var list<string>
     */
    public array $translatable = ['parameter'];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'product_id',
        'parameter',
        'value',
        'unit',
        'is_highlighted',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_highlighted' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
