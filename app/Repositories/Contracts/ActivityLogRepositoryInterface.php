<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Spatie\Activitylog\Models\Activity;

/**
 * Log aktivitas bersifat baca-saja: tidak ada create, update, maupun delete
 * dari UI. Karena itu kontraknya berdiri sendiri, bukan turunan
 * `BaseRepositoryInterface`.
 *
 * @see docs/09-tasks.md bagian 4.5
 */
interface ActivityLogRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, Activity>
     */
    public function paginate(array $filters, int $perPage = 25): LengthAwarePaginator;

    /**
     * Aktivitas terakhir pada satu subjek, dipakai halaman detail.
     *
     * @return Collection<int, Activity>
     */
    public function latestForSubject(string $subjectType, int $subjectId, int $limit = 20): Collection;

    /**
     * Aktivitas terakhir oleh satu pengguna, dipakai halaman detail pengguna.
     *
     * @return Collection<int, Activity>
     */
    public function latestByCauser(int $userId, int $limit = 20): Collection;

    /**
     * Aktivitas terakhir lintas objek, dipakai umpan dashboard.
     *
     * @return Collection<int, Activity>
     */
    public function latest(int $limit = 8): Collection;

    /**
     * Daftar tipe objek yang pernah tercatat, untuk isi filter.
     *
     * @return array<int, array{value: string, label: string}>
     */
    public function subjectTypes(): array;
}
