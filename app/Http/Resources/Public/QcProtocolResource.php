<?php

declare(strict_types=1);

namespace App\Http\Resources\Public;

use App\Models\QcProtocol;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Satu parameter yang diuji beserta ambang yang dijanjikan.
 *
 * `result_label` dan `result_value` adalah pasangan: labelnya menyebut dasar
 * angkanya ("Standar internal"), nilainya menyebut ambangnya ("Maks 2,5%").
 * Nilai tanpa label kehilangan artinya — pembeli tidak tahu itu janji, hasil
 * uji rata-rata, atau batas regulasi.
 *
 * @mixin QcProtocol
 */
final class QcProtocolResource extends JsonResource
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
            'result_label' => $this->text('result_label'),
            'result_value' => $this->result_value,
            'image_url' => $this->imageUrl(),
        ];
    }

    private function text(string $key): ?string
    {
        $value = trim((string) $this->getTranslation($key, app()->getLocale()));

        return $value === '' ? null : $value;
    }
}
