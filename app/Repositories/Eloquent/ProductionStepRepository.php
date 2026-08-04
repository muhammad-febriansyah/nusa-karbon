<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\ProductionStep;
use App\Repositories\Contracts\ProductionStepRepositoryInterface;
use App\Support\Locale;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * @extends SortableContentRepository<ProductionStep>
 */
final class ProductionStepRepository extends SortableContentRepository implements ProductionStepRepositoryInterface
{
    /**
     * @var array<int, string>
     */
    protected array $searchable = [];

    public function __construct()
    {
        $this->searchable = ['title->'.Locale::default()];
    }

    /**
     * @return Builder<ProductionStep>
     */
    protected function query(): Builder
    {
        return ProductionStep::query();
    }

    /**
     * @return Collection<int, ProductionStep>
     */
    public function ordered(): Collection
    {
        return $this->query()->orderBy('sort_order')->get();
    }

    /**
     * @return Collection<int, ProductionStep>
     */
    public function activeOrdered(): Collection
    {
        return $this->query()->active()->orderBy('sort_order')->get();
    }
}
