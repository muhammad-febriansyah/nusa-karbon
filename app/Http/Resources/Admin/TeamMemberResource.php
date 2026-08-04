<?php

declare(strict_types=1);

namespace App\Http\Resources\Admin;

use App\Models\TeamMember;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin TeamMember
 */
final class TeamMemberResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'position' => $this->getTranslations('position'),
            'bio' => $this->getTranslations('bio'),
            'photo_url' => $this->photoUrl(),
            'linkedin_url' => $this->linkedin_url,
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at?->format('d M Y, H:i'),
        ];
    }
}
