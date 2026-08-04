<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Representative;

use App\Models\Representative;

final class UpdateRepresentativeRequest extends StoreRepresentativeRequest
{
    protected function currentRepresentativeId(): ?int
    {
        $representative = $this->route('representative');

        return $representative instanceof Representative ? $representative->id : null;
    }
}
