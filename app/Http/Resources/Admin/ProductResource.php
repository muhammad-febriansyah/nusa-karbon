<?php

declare(strict_types=1);

namespace App\Http\Resources\Admin;

use App\Models\Product;
use App\Models\ProductDocument;
use App\Models\ProductSpecification;
use App\Support\Locale;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Product
 */
final class ProductResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->getTranslations('name'),
            'slug' => $this->getTranslations('slug'),
            'short_description' => $this->getTranslations('short_description'),
            'description' => $this->getTranslations('description'),
            'meta_title' => $this->getTranslations('meta_title'),
            'meta_description' => $this->getTranslations('meta_description'),

            'product_category_id' => $this->product_category_id,
            'category' => $this->whenLoaded('category', fn () => [
                'id' => $this->category?->id,
                'name' => $this->category?->getTranslations('name'),
            ]),

            'shape' => $this->shape,
            'size_mm' => $this->size_mm,

            'master_carton_weight_kg' => $this->master_carton_weight_kg,
            'master_carton_tare_kg' => $this->master_carton_tare_kg,
            'master_carton_cbm' => $this->master_carton_cbm,
            'master_carton_length_cm' => $this->master_carton_length_cm,
            'master_carton_width_cm' => $this->master_carton_width_cm,
            'master_carton_height_cm' => $this->master_carton_height_cm,
            'inner_box_weight_kg' => $this->inner_box_weight_kg,
            'inner_boxes_per_carton' => $this->inner_boxes_per_carton,
            'moq_kg' => $this->moq_kg,

            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
            'is_featured' => $this->is_featured,

            // Dipakai peringatan inline "belum bisa masuk Container Calculator".
            'is_calculable' => $this->isCalculable(),
            'completeness' => $this->completeness(),

            'cover_url' => $this->coverUrl(),
            'gallery' => $this->galleryItems(),
            'specifications' => $this->whenLoaded(
                'specifications',
                fn () => $this->specifications->map(fn (ProductSpecification $spec) => [
                    'id' => $spec->id,
                    'parameter' => $spec->getTranslations('parameter'),
                    'value' => $spec->value,
                    'unit' => $spec->unit,
                    'is_highlighted' => $spec->is_highlighted,
                ])->values()->all(),
                [],
            ),

            'documents' => $this->whenLoaded(
                'documents',
                fn () => $this->documents->map(fn (ProductDocument $document) => [
                    'id' => $document->id,
                    'type' => $document->type->value,
                    'type_label' => $document->type->label(),
                    'title' => $document->getTranslations('title'),
                    'file_name' => $document->file_name,
                    'file_size' => $document->file_size,
                    'mime_type' => $document->mime_type,
                    'url' => $document->url(),
                    'batch_reference' => $document->batch_reference,
                    'issued_at' => $document->issued_at?->format('Y-m-d'),
                    'download_count' => $document->download_count,
                    'is_active' => $document->is_active,
                ])->values()->all(),
                [],
            ),

            'packaging_option_ids' => $this->whenLoaded(
                'packagingOptions',
                fn () => $this->packagingOptions->pluck('id')->all(),
                [],
            ),

            'created_at' => $this->created_at?->format('d M Y, H:i'),
            'updated_at' => $this->updated_at?->format('d M Y, H:i'),
        ];
    }

    private function coverUrl(): ?string
    {
        $media = $this->getFirstMedia(Product::COLLECTION_COVER);

        if ($media === null) {
            return null;
        }

        return $media->hasGeneratedConversion('thumb')
            ? $media->getUrl('thumb')
            : $media->getUrl();
    }

    /**
     * @return array<int, array{id: int, url: string, name: string}>
     */
    private function galleryItems(): array
    {
        return $this->getMedia(Product::COLLECTION_GALLERY)
            ->map(fn ($media) => [
                'id' => $media->id,
                'url' => $media->hasGeneratedConversion('thumb')
                    ? $media->getUrl('thumb')
                    : $media->getUrl(),
                'name' => $media->file_name,
            ])
            ->values()
            ->all();
    }

    /**
     * Nama dan slug wajib; deskripsi singkat menentukan status `partial`.
     *
     * @return array<string, string>
     */
    private function completeness(): array
    {
        $names = $this->getTranslations('name');
        $slugs = $this->getTranslations('slug');
        $shorts = $this->getTranslations('short_description');

        $result = [];

        foreach (Locale::supported() as $locale) {
            $filled = collect([
                $names[$locale] ?? null,
                $slugs[$locale] ?? null,
                $shorts[$locale] ?? null,
            ])->filter(fn (?string $value) => $value !== null && $value !== '')->count();

            $result[$locale] = match ($filled) {
                3 => 'complete',
                0 => 'empty',
                default => 'partial',
            };
        }

        return $result;
    }
}
