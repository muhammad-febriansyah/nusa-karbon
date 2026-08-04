<?php

declare(strict_types=1);

namespace App\Actions\ContainerType;

use App\Models\ContainerType;
use App\Repositories\Contracts\ContainerTypeRepositoryInterface;

final readonly class CreateContainerTypeAction
{
    public function __construct(
        private ContainerTypeRepositoryInterface $containerTypes,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data): ContainerType
    {
        $data['sort_order'] = $this->containerTypes->nextSortOrder();

        return $this->containerTypes->create($data);
    }
}
