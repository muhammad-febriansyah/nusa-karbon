<?php

declare(strict_types=1);

namespace App\Actions\ContainerType;

use App\Models\ContainerType;
use App\Repositories\Contracts\ContainerTypeRepositoryInterface;

final readonly class DeleteContainerTypeAction
{
    public function __construct(
        private ContainerTypeRepositoryInterface $containerTypes,
    ) {}

    public function handle(ContainerType $containerType): bool
    {
        return $this->containerTypes->delete($containerType);
    }
}
