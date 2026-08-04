<?php

declare(strict_types=1);

namespace App\Http\Resources\Public;

use App\Models\Certificate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Sertifikat untuk trust bar dan halaman Tentang Kami.
 *
 * `file_path` sengaja tidak diekspos. Berkas scan sertifikat diunduh lewat
 * endpoint bertanda tangan pada Fase 5.4, bukan lewat URL storage langsung.
 *
 * @mixin Certificate
 */
final class CertificateResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->text('name'),
            'description' => $this->text('description'),
            'issuer' => $this->issuer,
            'certificate_number' => $this->certificate_number,
            'logo_url' => $this->logoUrl(),
            /*
             * Tanggal terbit dan masa berlaku ikut dikirim. Nomor sertifikat
             * tanpa masa berlaku tidak bisa diverifikasi — penerbit selalu
             * menanyakan keduanya, dan sertifikat kedaluwarsa yang tampil
             * seperti masih berlaku justru menyesatkan.
             */
            'issued_at' => $this->issued_at?->toDateString(),
            'expires_at' => $this->expires_at?->toDateString(),
        ];
    }

    private function text(string $key): ?string
    {
        $value = trim((string) $this->getTranslation($key, app()->getLocale()));

        return $value === '' ? null : $value;
    }
}
