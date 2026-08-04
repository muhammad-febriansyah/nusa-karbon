<?php

declare(strict_types=1);

namespace App\Http\Resources\Admin;

use App\Models\Faq;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Faq
 */
final class FaqResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'faq_category_id' => $this->faq_category_id,
            'category_name' => $this->whenLoaded(
                'category',
                fn () => $this->category?->getTranslations('name'),
            ),
            'question' => $this->getTranslations('question'),
            'answer' => $this->getTranslations('answer'),
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at?->format('d M Y, H:i'),
        ];
    }
}
