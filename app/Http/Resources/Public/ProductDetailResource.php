<?php

declare(strict_types=1);

namespace App\Http\Resources\Public;

use App\Models\PackagingOption;
use App\Models\Product;
use App\Models\ProductDocument;
use App\Models\ProductSpecification;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Satu produk beserta seluruh angka yang dipakai pembeli untuk memutuskan.
 *
 * Berbeda dari `ProductCardResource` yang sengaja ramping karena beranda memuat
 * enam kartu sekaligus: di sini pengunjung sedang melihat satu produk, jadi
 * seluruh spesifikasi, dokumen, dan data master carton ikut dikirim.
 *
 * @mixin Product
 */
final class ProductDetailResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->text('name'),
            'slug' => $this->text('slug'),
            'short_description' => $this->text('short_description'),
            'description' => $this->text('description'),
            'shape' => $this->shape,
            'size_mm' => $this->size_mm,
            'moq_kg' => $this->numeric($this->moq_kg),
            'category' => $this->whenLoaded(
                'category',
                fn (): ?string => $this->category?->getTranslation(
                    'name',
                    app()->getLocale(),
                ),
            ),
            'cover_url' => $this->coverUrl(),
            'gallery_urls' => $this->galleryUrls(),
            'is_calculable' => $this->isCalculable(),

            /*
             * Data master carton dikelompokkan, bukan disebar sebagai kolom
             * lepas. Keenam angka ini selalu dibaca bersama — pembeli memakainya
             * untuk memeriksa ulang hitungan kontainer, dan angka yang terpisah
             * dari kelompoknya kehilangan arti.
             */
            'master_carton' => [
                'net_weight_kg' => $this->numeric($this->master_carton_weight_kg),
                'tare_weight_kg' => $this->numeric($this->master_carton_tare_kg),
                'volume_cbm' => $this->numeric($this->master_carton_cbm),
                'length_cm' => $this->numeric($this->master_carton_length_cm),
                'width_cm' => $this->numeric($this->master_carton_width_cm),
                'height_cm' => $this->numeric($this->master_carton_height_cm),
                'inner_box_weight_kg' => $this->numeric($this->inner_box_weight_kg),
                'inner_boxes_per_carton' => $this->inner_boxes_per_carton,
            ],

            'specifications' => $this->specificationList(),
            'documents' => $this->documentList(),
            'packaging_options' => $this->packagingList(),
        ];
    }

    /**
     * @return list<array{parameter: string, value: string, unit: string|null, is_highlighted: bool}>
     */
    private function specificationList(): array
    {
        $specs = $this->specifications
            ->map(fn (ProductSpecification $spec): array => [
                'parameter' => (string) $spec->getTranslation(
                    'parameter',
                    app()->getLocale(),
                ),
                'value' => $spec->value,
                'unit' => $spec->unit,
                'is_highlighted' => $spec->is_highlighted,
            ])
            ->all();

        return array_values($specs);
    }

    /**
     * Dokumen yang boleh diunduh publik.
     *
     * `file_size` dikirim dalam byte apa adanya; frontend yang mengubahnya jadi
     * "1,9 MB" karena format angka bergantung locale pembaca, bukan server.
     *
     * @return list<array{id: int, title: string|null, type: string, url: string, file_size: int|null}>
     */
    private function documentList(): array
    {
        $documents = $this->documents
            ->filter(fn (ProductDocument $document): bool => $document->is_active)
            ->map(fn (ProductDocument $document): array => [
                'id' => $document->id,
                'title' => $this->translated($document->getTranslation('title', app()->getLocale())),
                // Enum diselesaikan di sini; frontend cukup menerima teksnya.
                'type' => $document->type->value,
                'url' => $document->url(),
                'file_size' => $document->file_size,
            ])
            ->all();

        return array_values($documents);
    }

    /**
     * @return list<array{id: int, name: string|null, net_weight_kg: float|null}>
     */
    private function packagingList(): array
    {
        $options = $this->packagingOptions
            ->map(fn (PackagingOption $option): array => [
                'id' => $option->id,
                'name' => $option->getTranslation('name', app()->getLocale()) ?: null,
                'net_weight_kg' => $this->numeric($option->net_weight_kg),
            ])
            ->all();

        return array_values($options);
    }

    /**
     * @return list<string>
     */
    private function galleryUrls(): array
    {
        $urls = $this->getMedia(Product::COLLECTION_GALLERY)
            ->map(fn ($media): string => $media->hasGeneratedConversion('medium')
                ? $media->getUrl('medium')
                : $media->getUrl())
            ->all();

        return array_values($urls);
    }

    /**
     * Kolom decimal Eloquent dikembalikan sebagai string. Dibiarkan begitu,
     * angka di frontend akan diurutkan dan dibandingkan sebagai teks.
     */
    private function numeric(mixed $value): ?float
    {
        return $value === null ? null : (float) $value;
    }

    /**
     * Varian `medium` (WebP, sisi terpanjang 800px) sudah cukup dan jauh lebih
     * ringan dari berkas asli.
     */
    private function coverUrl(): ?string
    {
        $media = $this->getFirstMedia(Product::COLLECTION_COVER);

        if ($media === null) {
            return null;
        }

        return $media->hasGeneratedConversion('medium')
            ? $media->getUrl('medium')
            : $media->getUrl();
    }

    private function translated(mixed $value): ?string
    {
        $text = trim((string) $value);

        return $text === '' ? null : $text;
    }

    private function text(string $key): ?string
    {
        $value = trim((string) $this->getTranslation($key, app()->getLocale()));

        return $value === '' ? null : $value;
    }
}
