<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\UploadKind;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

final class TempUploadRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'kind' => ['required', Rule::enum(UploadKind::class)],
            'file' => $this->kind()->rules(),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        $kind = $this->kind();
        $dimensions = $kind->minDimensions();

        $messages = [
            'file.required' => 'Tidak ada berkas yang diunggah.',
            'file.mimes' => 'Format berkas belum didukung. Gunakan: '
                .Str::upper(implode(', ', $kind->extensions())).'.',
            'file.max' => 'Ukuran berkas melebihi batas '
                .number_format($kind->maxKilobytes() / 1024, 0, ',', '.')
                .' MB. Kompres berkas terlebih dahulu.',
            'file.image' => 'Berkas harus berupa gambar.',
        ];

        if ($dimensions !== null) {
            $messages['file.dimensions'] = "Resolusi gambar terlalu kecil. Minimal {$dimensions['width']} × {$dimensions['height']} agar tidak pecah saat ditampilkan.";
        }

        return $messages;
    }

    public function kind(): UploadKind
    {
        return UploadKind::tryFrom((string) $this->input('kind'))
            ?? UploadKind::Gallery;
    }
}
