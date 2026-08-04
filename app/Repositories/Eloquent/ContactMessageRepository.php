<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\ContactMessage;
use App\Repositories\Contracts\ContactMessageRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;

/**
 * @extends BaseRepository<ContactMessage>
 */
final class ContactMessageRepository extends BaseRepository implements ContactMessageRepositoryInterface
{
    /**
     * @var array<int, string>
     */
    protected array $searchable = ['name', 'email', 'subject'];

    /**
     * @var array<int, string>
     */
    protected array $sortable = ['created_at', 'name', 'is_read'];

    protected string $defaultSortBy = 'created_at';

    /**
     * @var 'asc'|'desc'
     */
    protected string $defaultSortDir = 'desc';

    /**
     * @return Builder<ContactMessage>
     */
    protected function query(): Builder
    {
        return ContactMessage::query();
    }

    /**
     * @param  Builder<ContactMessage>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<ContactMessage>
     */
    protected function applyFilters(Builder $query, array $filters): Builder
    {
        $query = parent::applyFilters($query, $filters);

        if (($filters['unread'] ?? null) === '1') {
            $query->unread();
        }

        $spam = $filters['is_spam'] ?? null;

        if ($spam !== null && $spam !== '') {
            $query->where('is_spam', filter_var($spam, FILTER_VALIDATE_BOOLEAN));
        }

        return $query;
    }

    public function countUnread(): int
    {
        return ContactMessage::query()->unread()->count();
    }
}
