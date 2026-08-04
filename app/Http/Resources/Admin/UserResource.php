<?php

declare(strict_types=1);

namespace App\Http\Resources\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
final class UserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'avatar_url' => $this->avatar,
            'is_active' => $this->is_active,
            'two_factor_enabled' => $this->two_factor_confirmed_at !== null,
            'email_verified' => $this->email_verified_at !== null,
            'last_login_at' => $this->last_login_at?->format('d M Y, H:i').
                ($this->last_login_at !== null ? ' WIB' : ''),
            'last_login_ip' => $this->last_login_ip,
            'created_at' => $this->created_at?->format('d M Y, H:i'),
        ];
    }
}
