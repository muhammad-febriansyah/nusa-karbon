<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\RfqRequest;
use Illuminate\Database\Eloquent\Collection;

/**
 * @extends BaseRepositoryInterface<RfqRequest>
 */
interface RfqRequestRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Nomor referensi berikutnya untuk hari ini, format `RFQ-YYYYMMDD-NNNN`.
     */
    public function nextReferenceNo(): string;

    /**
     * Seluruh RFQ yang cocok filter, tanpa pagination — dipakai ekspor CSV.
     *
     * @param  array<string, mixed>  $filters
     * @return Collection<int, RfqRequest>
     */
    public function allMatching(array $filters): Collection;

    public function countUnread(): int;

    /**
     * Jumlah RFQ per negara, terbanyak lebih dulu.
     *
     * @return array<int, array{country_code: string, total: int}>
     */
    public function topCountries(int $limit = 5): array;

    /**
     * Jumlah RFQ per bulan untuk `$months` bulan terakhir.
     *
     * @return array<int, array{month: string, total: int}>
     */
    public function monthlyTotals(int $months = 12): array;

    /**
     * Jumlah RFQ per status.
     *
     * @return array<string, int>
     */
    public function statusTotals(): array;

    /**
     * Produk yang paling sering diminta buyer.
     *
     * @return array<int, array{name: string, total: int}>
     */
    public function topProducts(int $limit = 5): array;
}
