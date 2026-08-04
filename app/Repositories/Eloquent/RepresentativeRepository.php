<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Representative;
use App\Repositories\Contracts\RepresentativeRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * @extends BaseRepository<Representative>
 */
final class RepresentativeRepository extends BaseRepository implements RepresentativeRepositoryInterface
{
    /**
     * @var array<int, string>
     */
    protected array $searchable = ['name', 'email', 'region'];

    /**
     * @var array<int, string>
     */
    protected array $sortable = ['sort_order', 'name', 'region', 'is_active', 'created_at'];

    protected string $defaultSortBy = 'sort_order';

    /**
     * @var 'asc'|'desc'
     */
    protected string $defaultSortDir = 'asc';

    /**
     * @return Builder<Representative>
     */
    protected function query(): Builder
    {
        return Representative::query();
    }

    /**
     * @param  Builder<Representative>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<Representative>
     */
    protected function applyFilters(Builder $query, array $filters): Builder
    {
        $query = parent::applyFilters($query, $filters);

        $status = $filters['is_active'] ?? null;

        if ($status !== null && $status !== '') {
            $query->where('is_active', filter_var($status, FILTER_VALIDATE_BOOLEAN));
        }

        $region = $filters['region'] ?? null;

        if (is_string($region) && $region !== '') {
            $query->where('region', $region);
        }

        return $query;
    }

    /**
     * @return Collection<int, Representative>
     */
    public function activeOrdered(): Collection
    {
        return $this->query()->active()->orderBy('sort_order')->get();
    }

    /**
     * @return Collection<int, Representative>
     */
    public function searchActive(string $query): Collection
    {
        /*
         * Nomor telepon dicocokkan setelah seluruh pemisahnya dibuang. Pembeli
         * menyalin nomor apa adanya dari WhatsApp — kadang "+62 812-3456-7890",
         * kadang "628123456789" — dan pencocokan mentah akan gagal justru pada
         * kasus yang paling sering terjadi.
         */
        $digits = preg_replace('/\D+/', '', $query) ?? '';

        return Representative::query()
            ->active()
            ->where(function (Builder $builder) use ($query, $digits): void {
                $builder
                    ->where('name', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%");

                if ($digits !== '' && mb_strlen($digits) >= 6) {
                    $builder
                        ->orWhereRaw("REPLACE(REPLACE(REPLACE(REPLACE(phone, ' ', ''), '-', ''), '+', ''), '.', '') LIKE ?", ["%{$digits}%"])
                        ->orWhereRaw("REPLACE(REPLACE(REPLACE(REPLACE(whatsapp, ' ', ''), '-', ''), '+', ''), '.', '') LIKE ?", ["%{$digits}%"]);
                }
            })
            ->orderBy('sort_order')
            ->get();
    }

    public function emailExists(string $email, ?int $exceptId = null): bool
    {
        return Representative::query()
            ->where('email', $email)
            ->when($exceptId !== null, fn (Builder $query) => $query->whereKeyNot($exceptId))
            ->exists();
    }

    public function nextSortOrder(): int
    {
        return (int) Representative::query()->max('sort_order') + 1;
    }
}
