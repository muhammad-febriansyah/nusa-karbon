<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\ShippingPartner;

use Illuminate\Foundation\Http\FormRequest;

class StoreShippingPartnerRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'website_url' => ['nullable', 'url', 'max:500'],

            // Logonya sendiri sudah divalidasi `TempUploadRequest`.
            'logo_path_token' => ['nullable', 'string', 'ulid'],
            'remove_logo_path' => ['boolean'],

            'is_active' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama mitra pelayaran wajib diisi.',
            'website_url.url' => 'Alamat situs harus berupa URL lengkap, termasuk https://.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nama mitra',
            'website_url' => 'alamat situs',
        ];
    }
}
