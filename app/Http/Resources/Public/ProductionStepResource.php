<?php

declare(strict_types=1);

namespace App\Http\Resources\Public;

use App\Models\ProductionStep;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Satu tahap produksi pada beranda.
 *
 * Urutannya sudah ditentukan repository lewat `sort_order`, jadi nomor tahap
 * tidak ikut dikirim — frontend menurunkannya dari posisi di dalam larik.
 * Menyimpannya di dua tempat berarti keduanya bisa berbeda.
 *
 * @mixin ProductionStep
 */
final class ProductionStepResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->text('title'),
            'description' => $this->text('description'),
            'image_url' => $this->imageUrl(),
        ];
    }

    /**
     * Nilai kolom translatable untuk locale aktif. String kosong dianggap belum
     * diterjemahkan sehingga dikembalikan `null`.
     */
    private function text(string $key): ?string
    {
        $value = trim((string) $this->getTranslation($key, app()->getLocale()));

        return $value === '' ? null : $value;
    }
}
