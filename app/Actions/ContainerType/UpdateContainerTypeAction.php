<?php

declare(strict_types=1);

namespace App\Actions\ContainerType;

use App\Models\ContainerType;
use App\Repositories\Contracts\ContainerTypeRepositoryInterface;

final readonly class UpdateContainerTypeAction
{
    public function __construct(
        private ContainerTypeRepositoryInterface $containerTypes,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(ContainerType $containerType, array $data): ContainerType
    {
        return $this->containerTypes->update($containerType, $data);
    }
}
