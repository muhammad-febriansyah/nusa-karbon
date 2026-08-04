<?php

declare(strict_types=1);

namespace App\Http\Resources\Admin;

use App\Models\RfqRequest;
use App\Support\Locale;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin RfqRequest
 */
final class RfqRequestResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $locale = Locale::default();

        return [
            'id' => $this->id,
            'reference_no' => $this->reference_no,
            'company_name' => $this->company_name,
            'contact_name' => $this->contact_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'whatsapp' => $this->whatsapp,
            'whatsapp_link' => $this->whatsappLink(),
            'country_code' => $this->country_code,
            'incoterms' => $this->incoterms?->value,
            'port_of_destination' => $this->port_of_destination,
            'monthly_tonnage' => $this->monthly_tonnage,
            'packaging_notes' => $this->packaging_notes,
            'message' => $this->message,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'status_color' => $this->status->color(),
            'is_read' => $this->is_read,
            'locale' => $this->locale,
            'source_url' => $this->source_url,
            'ip_address' => $this->ip_address,
            'product_name' => $this->whenLoaded(
                'product',
                fn () => $this->product?->getTranslation('name', $locale),
            ),
            'container_code' => $this->whenLoaded(
                'containerType',
                fn () => $this->containerType?->code,
            ),
            'notes' => $this->whenLoaded(
                'notes',
                fn () => $this->notes->map(fn ($note): array => [
                    'id' => $note->id,
                    'note' => $note->note,
                    'user_name' => $note->user?->name,
                    'created_at' => $note->created_at?->format('d M Y, H:i').' WIB',
                ])->all(),
            ),
            'created_at' => $this->created_at?->format('d M Y, H:i').' WIB',
            'created_at_date' => $this->created_at?->toDateString(),
        ];
    }
}
