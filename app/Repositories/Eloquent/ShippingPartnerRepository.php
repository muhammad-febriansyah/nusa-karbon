<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\ShippingPartner;
use App\Repositories\Contracts\ShippingPartnerRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * @extends SortableContentRepository<ShippingPartner>
 */
final class ShippingPartnerRepository extends SortableContentRepository implements ShippingPartnerRepositoryInterface
{
    /**
     * @var array<int, string>
     */
    protected array $searchable = ['name'];

    /**
     * @return Builder<ShippingPartner>
     */
    protected function query(): Builder
    {
        return ShippingPartner::query();
    }

    /**
     * @return Collection<int, ShippingPartner>
     */
    public function ordered(): Collection
    {
        return $this->query()->orderBy('sort_order')->get();
    }

    /**
     * @return Collection<int, ShippingPartner>
     */
    public function activeOrdered(): Collection
    {
        return $this->query()->active()->orderBy('sort_order')->get();
    }
}
