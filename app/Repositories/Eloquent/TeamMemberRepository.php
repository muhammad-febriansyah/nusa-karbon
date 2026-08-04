<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\TeamMember;
use App\Repositories\Contracts\TeamMemberRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * @extends SortableContentRepository<TeamMember>
 */
final class TeamMemberRepository extends SortableContentRepository implements TeamMemberRepositoryInterface
{
    /**
     * @var array<int, string>
     */
    protected array $searchable = ['name'];

    /**
     * @return Builder<TeamMember>
     */
    protected function query(): Builder
    {
        return TeamMember::query();
    }

    /**
     * @return Collection<int, TeamMember>
     */
    public function ordered(): Collection
    {
        return $this->query()->orderBy('sort_order')->get();
    }

    /**
     * @return Collection<int, TeamMember>
     */
    /**
     * @return LengthAwarePaginator<int, TeamMember>
     */
    public function activePaginated(int $perPage, string $pageName = 'tim'): LengthAwarePaginator
    {
        return $this->query()
            ->active()
            ->orderBy('sort_order')
            ->paginate($perPage, ['*'], $pageName)
            ->withQueryString();
    }

    public function activeOrdered(): Collection
    {
        return $this->query()->active()->orderBy('sort_order')->get();
    }
}
