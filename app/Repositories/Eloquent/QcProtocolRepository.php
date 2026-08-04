<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\QcProtocol;
use App\Repositories\Contracts\QcProtocolRepositoryInterface;
use App\Support\Locale;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * @extends SortableContentRepository<QcProtocol>
 */
final class QcProtocolRepository extends SortableContentRepository implements QcProtocolRepositoryInterface
{
    /**
     * @var array<int, string>
     */
    protected array $searchable = [];

    public function __construct()
    {
        $this->searchable = ['title->'.Locale::default(), 'result_value'];
    }

    /**
     * @return Builder<QcProtocol>
     */
    protected function query(): Builder
    {
        return QcProtocol::query();
    }

    /**
     * @return Collection<int, QcProtocol>
     */
    public function ordered(): Collection
    {
        return $this->query()->orderBy('sort_order')->get();
    }

    /**
     * @return Collection<int, QcProtocol>
     */
    public function activeOrdered(): Collection
    {
        return $this->query()->active()->orderBy('sort_order')->get();
    }
}
