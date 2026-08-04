<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\TeamMember;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * @extends SortableContentRepositoryInterface<TeamMember>
 */
interface TeamMemberRepositoryInterface extends SortableContentRepositoryInterface
{
    /**
     * Anggota tim aktif per halaman, untuk roster publik.
     *
     * Nama halamannya sendiri (`tim`), bukan `page` bawaan, supaya berpindah
     * halaman roster tidak mengganggu penomoran lain di halaman yang sama.
     *
     * @return LengthAwarePaginator<int, TeamMember>
     */
    public function activePaginated(int $perPage, string $pageName = 'tim'): LengthAwarePaginator;
}
