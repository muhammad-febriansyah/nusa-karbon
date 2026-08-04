<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Gallery;

use App\Models\Gallery;

final class UpdateGalleryRequest extends StoreGalleryRequest
{
    protected function currentGalleryId(): ?int
    {
        $gallery = $this->route('gallery');

        return $gallery instanceof Gallery ? $gallery->id : null;
    }
}
