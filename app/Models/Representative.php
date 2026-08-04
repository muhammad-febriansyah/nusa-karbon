<?php

declare(strict_types=1);

namespace App\Models;

use App\Observers\BlameableObserver;
use Database\Factories\RepresentativeFactory;
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
 * @property string $name
 * @property array<string, string> $position
 * @property string|null $region
 * @property string $email
 * @property string|null $phone
 * @property string|null $whatsapp
 * @property string|null $photo_path
 * @property Carbon|null $started_at
 * @property Carbon|null $ended_at
 * @property int $sort_order
 * @property bool $is_active
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
#[ObservedBy([BlameableObserver::class])]
final class Representative extends Model
{
    /** @use HasFactory<RepresentativeFactory> */
    use HasFactory, HasTranslations, LogsActivity, SoftDeletes;

    /**
     * Direktori foto perwakilan pada disk `public`.
     */
    public const PHOTO_DIRECTORY = 'representatives';

    /**
     * @var list<string>
     */
    public array $translatable = ['position'];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'position',
        'region',
        'email',
        'phone',
        'whatsapp',
        'photo_path',
        'started_at',
        'ended_at',
        'sort_order',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'started_at' => 'date',
            'ended_at' => 'date',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function photoUrl(): ?string
    {
        if ($this->photo_path === null || $this->photo_path === '') {
            return null;
        }

        return Storage::disk('public')->url($this->photo_path);
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
     * Seluruh atribut dicatat beserta nilai lama dan barunya. Halaman publik
     * yang bergantung pada tabel ini adalah alat pencegah penipuan, jadi
     * perubahan tanpa jejak tidak dapat diterima.
     *
     * @see docs/04-erd.md bagian 3.3
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->useLogName('representative');
    }
}
