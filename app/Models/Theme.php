<?php

declare(strict_types=1);

namespace App\Models;

use App\Observers\BlameableObserver;
use Database\Factories\ThemeFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * Palet warna panel admin dan website publik.
 *
 * @see docs/10-theming.md
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $scope
 * @property array<string, string> $tokens
 * @property bool $is_preset
 * @property bool $is_active_admin
 * @property bool $is_active_public
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
#[ObservedBy([BlameableObserver::class])]
final class Theme extends Model
{
    /** @use HasFactory<ThemeFactory> */
    use HasFactory, LogsActivity, SoftDeletes;

    public const SCOPE_ADMIN = 'admin';

    public const SCOPE_PUBLIC = 'public';

    public const SCOPE_BOTH = 'both';

    /**
     * Slug preset default per scope; dipakai sebagai cadangan bila tidak ada
     * tema aktif.
     */
    public const DEFAULT_ADMIN_SLUG = 'ember-light';

    public const DEFAULT_PUBLIC_SLUG = 'ash-carbon';

    /**
     * Token warna yang wajib ada. Daftarnya mengikuti variabel `--t-*` di
     * `resources/css/app.css` — token di luar daftar ini ditolak supaya tidak
     * ada nilai liar yang tersimpan tapi tidak pernah dipakai.
     *
     * @var list<string>
     */
    public const COLOR_KEYS = [
        // Brand
        'primary', 'primary_foreground',
        'secondary', 'secondary_foreground',
        'accent', 'accent_foreground',

        // Permukaan
        'background', 'foreground',
        'card', 'card_foreground',
        'popover', 'popover_foreground',
        'muted', 'muted_foreground',
        'border', 'input', 'ring',

        // Semantik — sistem tombol bergantung pada kelompok ini
        'success', 'success_foreground',
        'destructive', 'destructive_foreground',
        'warning', 'warning_foreground',
        'info', 'info_foreground',
        'neutral', 'neutral_foreground',
        'violet', 'violet_foreground',
        'teal', 'teal_foreground',

        // Sidebar
        'sidebar', 'sidebar_foreground',
        'sidebar_accent', 'sidebar_accent_foreground',
        'sidebar_primary', 'sidebar_primary_foreground',
        'sidebar_border', 'sidebar_ring',
    ];

    /**
     * Pasangan latar → teks yang wajib memenuhi rasio kontras minimum.
     * Pasangan bernilai 4.5 ditolak bila gagal; sisanya hanya peringatan.
     *
     * @see docs/10-theming.md bagian 4
     *
     * @var array<int, array{0: string, 1: string, 2: float, 3: bool}>
     */
    public const CONTRAST_PAIRS = [
        ['background', 'foreground', 4.5, true],
        ['card', 'card_foreground', 4.5, true],
        ['popover', 'popover_foreground', 4.5, true],
        ['primary', 'primary_foreground', 4.5, true],
        ['secondary', 'secondary_foreground', 4.5, true],
        ['accent', 'accent_foreground', 4.5, true],
        ['success', 'success_foreground', 4.5, true],
        ['destructive', 'destructive_foreground', 4.5, true],
        ['warning', 'warning_foreground', 4.5, true],
        ['info', 'info_foreground', 4.5, true],
        ['neutral', 'neutral_foreground', 4.5, true],
        ['violet', 'violet_foreground', 4.5, true],
        ['teal', 'teal_foreground', 4.5, true],
        ['sidebar', 'sidebar_foreground', 4.5, true],
        ['sidebar_accent', 'sidebar_accent_foreground', 4.5, true],
        ['sidebar_primary', 'sidebar_primary_foreground', 4.5, true],

        // Hanya peringatan: teks sekunder dan garis batas memang dibuat lebih
        // redup, memaksanya 4,5:1 akan merusak hierarki visual.
        ['muted', 'muted_foreground', 3.5, false],
        ['background', 'border', 1.4, false],
    ];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'scope',
        'tokens',
        'is_preset',
        'is_active_admin',
        'is_active_public',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tokens' => 'array',
            'is_preset' => 'boolean',
            'is_active_admin' => 'boolean',
            'is_active_public' => 'boolean',
        ];
    }

    /**
     * Seluruh kunci token termasuk `radius`.
     *
     * @return list<string>
     */
    public static function tokenKeys(): array
    {
        return [...self::COLOR_KEYS, 'radius'];
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public static function scopeOptions(): array
    {
        return [
            ['value' => self::SCOPE_ADMIN, 'label' => 'Panel admin'],
            ['value' => self::SCOPE_PUBLIC, 'label' => 'Website publik'],
            ['value' => self::SCOPE_BOTH, 'label' => 'Keduanya'],
        ];
    }

    public function supportsScope(string $scope): bool
    {
        return $this->scope === self::SCOPE_BOTH || $this->scope === $scope;
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeForScope(Builder $query, string $scope): Builder
    {
        return $query->whereIn('scope', [$scope, self::SCOPE_BOTH]);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'scope', 'tokens', 'is_active_admin', 'is_active_public'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->useLogName('theme');
    }
}
