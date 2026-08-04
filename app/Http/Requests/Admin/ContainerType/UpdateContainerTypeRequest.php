<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\ContainerType;

use App\Models\ContainerType;

final class UpdateContainerTypeRequest extends StoreContainerTypeRequest
{
    protected function currentContainerTypeId(): ?int
    {
        $containerType = $this->route('container_type');

        return $containerType instanceof ContainerType ? $containerType->id : null;
    }
}
