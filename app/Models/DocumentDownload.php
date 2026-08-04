<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\DocumentDownloadFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Baris hanya ditulis sekali, jadi `updated_at` dimatikan.
 *
 * @property int $id
 * @property int $product_document_id
 * @property string $ip_address
 * @property string|null $country_code
 * @property string|null $user_agent
 * @property string|null $referer
 * @property string $locale
 * @property Carbon|null $created_at
 * @property-read ProductDocument $document
 */
final class DocumentDownload extends Model
{
    /** @use HasFactory<DocumentDownloadFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'product_document_id',
        'ip_address',
        'country_code',
        'user_agent',
        'referer',
        'locale',
    ];

    /**
     * @return BelongsTo<ProductDocument, $this>
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(ProductDocument::class, 'product_document_id');
    }
}
