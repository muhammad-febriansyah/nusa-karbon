<?php

declare(strict_types=1);

namespace App\Http\Resources\Public;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Produk dalam bentuk kartu etalase.
 *
 * Berbeda dari `Admin\ProductResource` yang mengirim seluruh terjemahan agar
 * bisa diedit: di sini teks sudah diselesaikan ke locale aktif, dan kolom yang
 * tidak dipakai kartu sengaja tidak ikut. Payload beranda memuat banyak produk
 * sekaligus, jadi setiap kolom mubazir terkalikan.
 *
 * @mixin Product
 */
final class ProductCardResource extends JsonResource
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
            'shape' => $this->shape,
            'size_mm' => $this->size_mm,
            'moq_kg' => $this->moq_kg,
            'category' => $this->whenLoaded(
                'category',
                fn (): ?string => $this->category?->getTranslation(
                    'name',
                    app()->getLocale(),
                ),
            ),
            'cover_url' => $this->coverUrl(),
            // Menentukan apakah kartu boleh menautkan ke Container Calculator.
            'is_calculable' => $this->isCalculable(),
        ];
    }

    /**
     * Nilai kolom translatable untuk locale aktif. String kosong dianggap belum
     * diterjemahkan sehingga dikembalikan `null` — komponen frontend cukup
     * memeriksa satu kondisi, bukan dua.
     */
    private function text(string $key): ?string
    {
        $value = trim((string) $this->getTranslation($key, app()->getLocale()));

        return $value === '' ? null : $value;
    }

    /**
     * Varian `medium` (WebP, sisi terpanjang 800px) sudah cukup untuk kartu dan
     * jauh lebih ringan dari berkas asli. `thumb` terlalu kecil dan pecah pada
     * layar retina.
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
}
