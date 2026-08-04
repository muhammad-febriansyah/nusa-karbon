<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\ProductionStep;

use App\Support\Locale;
use Illuminate\Foundation\Http\FormRequest;

class StoreProductionStepRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $default = Locale::default();

        $rules = [
            'title' => ['required', 'array'],
            'description' => ['nullable', 'array'],

            // Gambarnya sendiri sudah divalidasi `TempUploadRequest`.
            'image_path_token' => ['nullable', 'string', 'ulid'],
            'remove_image_path' => ['boolean'],
            'is_active' => ['boolean'],
        ];

        foreach (Locale::supported() as $locale) {
            $required = $locale === $default ? 'required' : 'nullable';

            $rules["title.{$locale}"] = [$required, 'string', 'max:255'];
            $rules["description.{$locale}"] = ['nullable', 'string', 'max:1000'];
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        $messages = [];

        foreach (Locale::supported() as $locale) {
            $messages["title.{$locale}.required"] = 'Judul tahapan wajib diisi.';
        }

        return $messages;
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'title' => 'judul tahapan',
        ];
    }
}
