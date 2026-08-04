<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\TeamMemberFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Spatie\Translatable\HasTranslations;

/**
 * Anggota tim yang ditampilkan di halaman About Us.
 *
 * @see docs/04-erd.md bagian 3.3
 *
 * @property int $id
 * @property string $name
 * @property array<string, string> $position
 * @property array<string, string>|null $bio
 * @property string|null $photo_path
 * @property string|null $linkedin_url
 * @property int $sort_order
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
final class TeamMember extends Model
{
    /** @use HasFactory<TeamMemberFactory> */
    use HasFactory, HasTranslations, SoftDeletes;

    /**
     * @var list<string>
     */
    public array $translatable = ['position', 'bio'];

    /**
     * Direktori berkas pada disk `public`.
     */
    public const FILE_DIRECTORY = 'team-members';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'position',
        'bio',
        'photo_path',
        'linkedin_url',
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
}
