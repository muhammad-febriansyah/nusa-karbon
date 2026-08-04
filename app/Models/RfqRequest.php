<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Incoterm;
use App\Enums\RfqStatus;
use Database\Factories\RfqRequestFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Permintaan penawaran dari buyer.
 *
 * @see docs/04-erd.md bagian 3.6
 *
 * @property int $id
 * @property string $reference_no
 * @property int|null $product_id
 * @property string $company_name
 * @property string|null $contact_name
 * @property string $email
 * @property string|null $phone
 * @property string|null $whatsapp
 * @property string $country_code
 * @property Incoterm|null $incoterms
 * @property string|null $port_of_destination
 * @property string|null $monthly_tonnage
 * @property int|null $container_type_id
 * @property string|null $packaging_notes
 * @property string|null $message
 * @property RfqStatus $status
 * @property bool $is_read
 * @property string $locale
 * @property string|null $source_url
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Product|null $product
 * @property-read ContainerType|null $containerType
 * @property-read Collection<int, RfqNote> $notes
 */
final class RfqRequest extends Model
{
    /** @use HasFactory<RfqRequestFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'reference_no',
        'product_id',
        'company_name',
        'contact_name',
        'email',
        'phone',
        'whatsapp',
        'country_code',
        'incoterms',
        'port_of_destination',
        'monthly_tonnage',
        'container_type_id',
        'packaging_notes',
        'message',
        'status',
        'is_read',
        'locale',
        'source_url',
        'ip_address',
        'user_agent',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => RfqStatus::class,
            'incoterms' => Incoterm::class,
            'is_read' => 'boolean',
            'product_id' => 'integer',
            'container_type_id' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return BelongsTo<ContainerType, $this>
     */
    public function containerType(): BelongsTo
    {
        return $this->belongsTo(ContainerType::class);
    }

    /**
     * @return HasMany<RfqNote, $this>
     */
    public function notes(): HasMany
    {
        return $this->hasMany(RfqNote::class)->latest('id');
    }

    /**
     * Nomor WhatsApp siap klik; hanya angka yang disisakan.
     */
    public function whatsappLink(): ?string
    {
        $number = preg_replace('/\D+/', '', (string) ($this->whatsapp ?? $this->phone));

        if ($number === null || $number === '') {
            return null;
        }

        return "https://wa.me/{$number}";
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeUnread(Builder $query): Builder
    {
        return $query->where('is_read', false)->where('status', '!=', RfqStatus::Spam);
    }
}
