<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\RfqRequest;
use App\Repositories\Contracts\RfqRequestRepositoryInterface;
use App\Support\Locale;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * @extends BaseRepository<RfqRequest>
 */
final class RfqRequestRepository extends BaseRepository implements RfqRequestRepositoryInterface
{
    /**
     * @var array<int, string>
     */
    protected array $searchable = ['reference_no', 'company_name', 'contact_name', 'email'];

    /**
     * @var array<int, string>
     */
    protected array $sortable = ['created_at', 'status', 'country_code', 'company_name'];

    protected string $defaultSortBy = 'created_at';

    /**
     * @var 'asc'|'desc'
     */
    protected string $defaultSortDir = 'desc';

    /**
     * @return Builder<RfqRequest>
     */
    protected function query(): Builder
    {
        return RfqRequest::query()->with(['product:id,code,name', 'containerType:id,code,name']);
    }

    /**
     * @param  Builder<RfqRequest>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<RfqRequest>
     */
    protected function applyFilters(Builder $query, array $filters): Builder
    {
        $query = parent::applyFilters($query, $filters);

        $status = $filters['status'] ?? null;

        if (is_string($status) && $status !== '') {
            $query->where('status', $status);
        }

        $country = $filters['country_code'] ?? null;

        if (is_string($country) && $country !== '') {
            $query->where('country_code', strtoupper($country));
        }

        $product = $filters['product_id'] ?? null;

        if ($product !== null && $product !== '') {
            $query->where('product_id', (int) $product);
        }

        $from = $filters['from'] ?? null;

        if (is_string($from) && $from !== '') {
            $query->whereDate('created_at', '>=', $from);
        }

        $to = $filters['to'] ?? null;

        if (is_string($to) && $to !== '') {
            $query->whereDate('created_at', '<=', $to);
        }

        if (($filters['unread'] ?? null) === '1') {
            $query->unread();
        }

        return $query;
    }

    public function nextReferenceNo(): string
    {
        $date = now()->format('Ymd');
        $prefix = "RFQ-{$date}-";

        $last = RfqRequest::query()
            ->where('reference_no', 'like', $prefix.'%')
            ->orderByDesc('reference_no')
            ->value('reference_no');

        $sequence = $last === null
            ? 1
            : ((int) substr($last, -4)) + 1;

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Collection<int, RfqRequest>
     */
    public function allMatching(array $filters): Collection
    {
        return $this->applySorting($this->applyFilters($this->query(), $filters), $filters)->get();
    }

    public function countUnread(): int
    {
        return RfqRequest::query()->unread()->count();
    }

    /**
     * @return array<int, array{country_code: string, total: int}>
     */
    public function topCountries(int $limit = 5): array
    {
        return RfqRequest::query()
            ->selectRaw('country_code, count(*) as total')
            ->groupBy('country_code')
            ->orderByDesc('total')
            ->limit($limit)
            ->get()
            ->map(fn (RfqRequest $row): array => [
                'country_code' => $row->country_code,
                'total' => (int) $row->getAttribute('total'),
            ])
            ->all();
    }

    /**
     * @return array<string, int>
     */
    public function statusTotals(): array
    {
        return RfqRequest::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->map(fn (mixed $total): int => (int) $total)
            ->all();
    }

    /**
     * @return array<int, array{name: string, total: int}>
     */
    public function topProducts(int $limit = 5): array
    {
        $locale = Locale::default();

        return RfqRequest::query()
            ->join('products', 'products.id', '=', 'rfq_requests.product_id')
            ->selectRaw('products.name as product_name, count(*) as total')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total')
            ->limit($limit)
            ->get()
            ->map(function (RfqRequest $row) use ($locale): array {
                /** @var array<string, string> $name */
                $name = json_decode((string) $row->getAttribute('product_name'), true) ?: [];

                return [
                    'name' => $name[$locale] ?? '—',
                    'total' => (int) $row->getAttribute('total'),
                ];
            })
            ->all();
    }

    /**
     * @return array<int, array{month: string, total: int}>
     */
    public function monthlyTotals(int $months = 12): array
    {
        $start = now()->subMonths($months - 1)->startOfMonth();

        /** @var array<string, int> $counts */
        $counts = RfqRequest::query()
            ->where('created_at', '>=', $start)
            ->get(['created_at'])
            ->groupBy(fn (RfqRequest $rfq): string => $rfq->created_at?->format('Y-m') ?? '')
            ->map(fn ($group): int => $group->count())
            ->all();

        $result = [];

        for ($index = 0; $index < $months; $index++) {
            $month = $start->copy()->addMonths($index)->format('Y-m');

            $result[] = ['month' => $month, 'total' => $counts[$month] ?? 0];
        }

        return $result;
    }
}
