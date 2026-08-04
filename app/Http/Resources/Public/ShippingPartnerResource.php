<?php

declare(strict_types=1);

namespace App\Http\Resources\Public;

use App\Models\ShippingPartner;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Mitra pelayaran. Namanya nama dagang, jadi tidak translatable.
 *
 * @mixin ShippingPartner
 */
final class ShippingPartnerResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'logo_url' => $this->logoUrl(),
            'website_url' => $this->website_url,
        ];
    }
}
