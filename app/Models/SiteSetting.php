<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SettingType;
use Database\Factories\SiteSettingFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * Satu baris pengaturan situs.
 *
 * @see docs/04-erd.md bagian 3.7
 *
 * @property int $id
 * @property string $group
 * @property string $key
 * @property string $label
 * @property string|null $description
 * @property mixed $value
 * @property SettingType $type
 * @property array<int, string>|null $rules
 * @property int $sort_order
 * @property bool $is_public
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
final class SiteSetting extends Model
{
    /** @use HasFactory<SiteSettingFactory> */
    use HasFactory, LogsActivity;

    /**
     * Kunci yang nilainya disimpan terenkripsi dan tidak pernah dikirim ke
     * frontend, termasuk panel admin.
     *
     * @var list<string>
     */
    public const SECRET_KEYS = ['turnstile_secret_key'];

    /**
     * Direktori berkas pengaturan pada disk `public`.
     */
    public const FILE_DIRECTORY = 'settings';

    /**
     * @var list<string>
     */
    protected $fillable = ['value'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => SettingType::class,
            'value' => 'array',
            'rules' => 'array',
            'sort_order' => 'integer',
            'is_public' => 'boolean',
        ];
    }

    public function isSecret(): bool
    {
        return in_array($this->key, self::SECRET_KEYS, true);
    }

    /**
     * Nilai siap pakai. Kunci rahasia didekripsi di sini dan hanya boleh
     * dipanggil sisi server.
     */
    public function plainValue(): mixed
    {
        $value = $this->value['data'] ?? null;

        if (! $this->isSecret() || ! is_string($value) || $value === '') {
            return $value;
        }

        return Crypt::decryptString($value);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopePublic(Builder $query): Builder
    {
        return $query->where('is_public', true);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['value'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->useLogName('site_setting');
    }
}
