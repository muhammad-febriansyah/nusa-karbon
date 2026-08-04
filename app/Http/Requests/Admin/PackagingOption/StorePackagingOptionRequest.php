<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\PackagingOption;

use App\Support\Locale;
use Illuminate\Foundation\Http\FormRequest;

class StorePackagingOptionRequest extends FormRequest
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

            'net_weight_kg' => ['nullable', 'numeric', 'min:0.001', 'max:9999999.999'],

            // Gambarnya sendiri sudah divalidasi `TempUploadRequest`.
            'image_token' => ['nullable', 'string', 'ulid'],
            'remove_image' => ['boolean'],

            'is_active' => ['boolean'],
        ];

        foreach (Locale::supported() as $locale) {
            $required = $locale === $default ? 'required' : 'nullable';

            $rules["name.{$locale}"] = [$required, 'string', 'max:255'];
            $rules["description.{$locale}"] = ['nullable', 'string', 'max:500'];
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        $messages = [
            'net_weight_kg.min' => 'Berat bersih harus lebih dari 0.',
        ];

        foreach (Locale::supported() as $locale) {
            $messages["name.{$locale}.required"] = 'Nama kemasan wajib diisi.';
            $messages["description.{$locale}.max"] = 'Deskripsi maksimal 500 karakter.';
        }

        return $messages;
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'net_weight_kg' => 'berat bersih',
        ];
    }
}
