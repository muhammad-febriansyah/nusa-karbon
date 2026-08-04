<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Certificate;
use App\Repositories\Contracts\CertificateRepositoryInterface;
use App\Support\Locale;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * @extends BaseRepository<Certificate>
 */
final class CertificateRepository extends BaseRepository implements CertificateRepositoryInterface
{
    /**
     * @var array<int, string>
     */
    protected array $searchable = [];

    public function __construct()
    {
        $this->searchable = ['name->'.Locale::default(), 'issuer', 'certificate_number'];
    }

    /**
     * @var array<int, string>
     */
    protected array $sortable = [
        'sort_order', 'issuer', 'issued_at', 'expires_at', 'is_active', 'created_at',
    ];

    protected string $defaultSortBy = 'sort_order';

    /**
     * @var 'asc'|'desc'
     */
    protected string $defaultSortDir = 'asc';

    /**
     * @return Builder<Certificate>
     */
    protected function query(): Builder
    {
        return Certificate::query();
    }

    /**
     * @param  Builder<Certificate>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<Certificate>
     */
    protected function applyFilters(Builder $query, array $filters): Builder
    {
        $query = parent::applyFilters($query, $filters);

        $status = $filters['is_active'] ?? null;

        if ($status !== null && $status !== '') {
            $query->where('is_active', filter_var($status, FILTER_VALIDATE_BOOLEAN));
        }

        $expiry = $filters['expiry'] ?? null;

        if ($expiry === 'expired') {
            $query->whereNotNull('expires_at')
                ->whereDate('expires_at', '<', now()->toDateString());
        }

        if ($expiry === 'expiring') {
            $query->expiringWithin();
        }

        return $query;
    }

    /**
     * @return Collection<int, Certificate>
     */
    public function activeOrdered(): Collection
    {
        return $this->query()->active()->orderBy('sort_order')->get();
    }

    /**
     * @return Collection<int, Certificate>
     */
    public function trustBar(): Collection
    {
        return $this->query()->trustBar()->orderBy('sort_order')->get();
    }

    /**
     * @return Collection<int, Certificate>
     */
    public function expiringWithin(int $days = Certificate::EXPIRY_WARNING_DAYS): Collection
    {
        return $this->query()
            ->expiringWithin($days)
            ->orderBy('expires_at')
            ->get();
    }

    public function nextSortOrder(): int
    {
        return (int) Certificate::query()->max('sort_order') + 1;
    }
}
