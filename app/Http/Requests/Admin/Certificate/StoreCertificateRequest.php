<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Certificate;

use App\Support\Locale;
use Illuminate\Foundation\Http\FormRequest;

class StoreCertificateRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $default = Locale::default();

        $rules = [
            'name' => ['required', 'array'],
            'description' => ['nullable', 'array'],

            'issuer' => ['nullable', 'string', 'max:255'],
            'certificate_number' => ['nullable', 'string', 'max:100'],

            'issued_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:issued_at'],

            // Berkasnya sendiri sudah divalidasi `TempUploadRequest`.
            'logo_token' => ['nullable', 'string', 'ulid'],
            'remove_logo' => ['boolean'],
            'file_token' => ['nullable', 'string', 'ulid'],
            'remove_file' => ['boolean'],

            'show_in_trust_bar' => ['boolean'],
            'is_active' => ['boolean'],
        ];

        foreach (Locale::supported() as $locale) {
            $required = $locale === $default ? 'required' : 'nullable';

            $rules["name.{$locale}"] = [$required, 'string', 'max:255'];
            $rules["description.{$locale}"] = ['nullable', 'string', 'max:1000'];
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        $messages = [
            'issued_at.date' => 'Tanggal terbit tidak valid.',
            'expires_at.date' => 'Tanggal kedaluwarsa tidak valid.',
            'expires_at.after_or_equal' => 'Tanggal kedaluwarsa tidak boleh lebih awal dari tanggal terbit.',
            'certificate_number.max' => 'Nomor sertifikat maksimal 100 karakter.',
        ];

        foreach (Locale::supported() as $locale) {
            $messages["name.{$locale}.required"] = 'Nama sertifikat wajib diisi.';
            $messages["description.{$locale}.max"] = 'Deskripsi maksimal 1000 karakter.';
        }

        return $messages;
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'issuer' => 'penerbit',
            'certificate_number' => 'nomor sertifikat',
            'issued_at' => 'tanggal terbit',
            'expires_at' => 'tanggal kedaluwarsa',
        ];
    }
}
