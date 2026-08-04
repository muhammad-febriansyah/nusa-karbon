<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;

/**
 * @extends BaseRepository<User>
 */
final class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    /**
     * @var array<int, string>
     */
    protected array $searchable = ['name', 'email'];

    /**
     * @var array<int, string>
     */
    protected array $sortable = ['name', 'email', 'last_login_at', 'is_active', 'created_at'];

    protected string $defaultSortBy = 'name';

    /**
     * @var 'asc'|'desc'
     */
    protected string $defaultSortDir = 'asc';

    /**
     * @return Builder<User>
     */
    protected function query(): Builder
    {
        return User::query();
    }

    /**
     * @param  Builder<User>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<User>
     */
    protected function applyFilters(Builder $query, array $filters): Builder
    {
        $query = parent::applyFilters($query, $filters);

        $status = $filters['is_active'] ?? null;

        if ($status !== null && $status !== '') {
            $query->where('is_active', filter_var($status, FILTER_VALIDATE_BOOLEAN));
        }

        return $query;
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public function options(): array
    {
        return User::query()
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (User $user): array => [
                'value' => (string) $user->id,
                'label' => $user->name,
            ])
            ->all();
    }

    public function countActiveAdmins(): int
    {
        return User::query()
            ->where('is_active', true)
            ->whereHas('roles', fn (Builder $query) => $query->where('name', 'admin'))
            ->count();
    }
}
