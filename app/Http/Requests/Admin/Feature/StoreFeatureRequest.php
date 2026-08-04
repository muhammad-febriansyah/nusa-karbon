<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Feature;

use App\Support\Locale;
use Illuminate\Foundation\Http\FormRequest;

class StoreFeatureRequest extends FormRequest
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
            'icon' => ['nullable', 'string', 'max:100', 'regex:/^[a-z0-9-]+$/'],

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
        $messages = [
            'icon.regex' => 'Nama ikon lucide hanya berisi huruf kecil, angka, dan tanda hubung.',
        ];

        foreach (Locale::supported() as $locale) {
            $messages["title.{$locale}.required"] = 'Judul keunggulan wajib diisi.';
        }

        return $messages;
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'title' => 'judul keunggulan',
            'icon' => 'ikon',
        ];
    }
}
