<?php

declare(strict_types=1);

namespace App\Http\Resources\Admin;

use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ContactMessage
 */
final class ContactMessageResource extends JsonResource
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
            'subject' => $this->subject,
            'message' => $this->message,
            'is_read' => $this->is_read,
            'is_spam' => $this->is_spam,
            'locale' => $this->locale,
            'ip_address' => $this->ip_address,
            'created_at' => $this->created_at?->format('d M Y, H:i').' WIB',
        ];
    }
}
