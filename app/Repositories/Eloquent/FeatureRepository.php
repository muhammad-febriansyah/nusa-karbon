<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Feature;
use App\Repositories\Contracts\FeatureRepositoryInterface;
use App\Support\Locale;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * @extends SortableContentRepository<Feature>
 */
final class FeatureRepository extends SortableContentRepository implements FeatureRepositoryInterface
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
     * @return Builder<Feature>
     */
    protected function query(): Builder
    {
        return Feature::query();
    }

    /**
     * @return Collection<int, Feature>
     */
    public function ordered(): Collection
    {
        return $this->query()->orderBy('sort_order')->get();
    }

    /**
     * @return Collection<int, Feature>
     */
    public function activeOrdered(): Collection
    {
        return $this->query()->active()->orderBy('sort_order')->get();
    }
}
