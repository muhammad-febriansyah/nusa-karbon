<?php

declare(strict_types=1);

namespace App\Models;

use App\Observers\BlameableObserver;
use Database\Factories\CertificateFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Translatable\HasTranslations;

/**
 * @property int $id
 * @property array<string, string> $name
 * @property string|null $issuer
 * @property string|null $certificate_number
 * @property array<string, string>|null $description
 * @property string|null $logo_path
 * @property string|null $file_path
 * @property Carbon|null $issued_at
 * @property Carbon|null $expires_at
 * @property bool $show_in_trust_bar
 * @property int $sort_order
 * @property bool $is_active
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
#[ObservedBy([BlameableObserver::class])]
final class Certificate extends Model
{
    /** @use HasFactory<CertificateFactory> */
    use HasFactory, HasTranslations, LogsActivity, SoftDeletes;

    /**
     * Direktori logo penerbit pada disk `public`.
     */
    public const LOGO_DIRECTORY = 'certificates/logos';

    /**
     * Direktori berkas scan sertifikat pada disk `public`.
     */
    public const FILE_DIRECTORY = 'certificates/files';

    /**
     * Ambang peringatan kedaluwarsa di dashboard, dalam hari.
     *
     * @see docs/09-tasks.md bagian 3.1
     */
    public const EXPIRY_WARNING_DAYS = 60;

    /**
     * @var list<string>
     */
    public array $translatable = ['name', 'description'];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'issuer',
        'certificate_number',
        'description',
        'logo_path',
        'file_path',
        'issued_at',
        'expires_at',
        'show_in_trust_bar',
        'sort_order',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'issued_at' => 'date',
            'expires_at' => 'date',
            'show_in_trust_bar' => 'boolean',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function logoUrl(): ?string
    {
        return $this->publicUrl($this->logo_path);
    }

    public function fileUrl(): ?string
    {
        return $this->publicUrl($this->file_path);
    }

    /**
     * Sertifikat yang sudah lewat tanggal kedaluwarsa.
     */
    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    /**
     * Sertifikat yang kedaluwarsa dalam ambang peringatan dan belum lewat.
     */
    public function isExpiringSoon(int $days = self::EXPIRY_WARNING_DAYS): bool
    {
        if ($this->expires_at === null || $this->isExpired()) {
            return false;
        }

        return $this->expires_at->lessThanOrEqualTo(now()->addDays($days));
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
    public function scopeTrustBar(Builder $query): Builder
    {
        return $query->active()->where('show_in_trust_bar', true);
    }

    /**
     * Sertifikat aktif yang kedaluwarsa dalam `$days` hari ke depan. Yang sudah
     * lewat tanggal tidak ikut — itu masalah lain, bukan peringatan dini.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeExpiringWithin(Builder $query, int $days = self::EXPIRY_WARNING_DAYS): Builder
    {
        return $query->active()
            ->whereNotNull('expires_at')
            ->whereDate('expires_at', '>=', now()->toDateString())
            ->whereDate('expires_at', '<=', now()->addDays($days)->toDateString());
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->useLogName('certificate');
    }

    private function publicUrl(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        return Storage::disk('public')->url($path);
    }
}
