<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\QcProtocol;

use App\Support\Locale;
use Illuminate\Foundation\Http\FormRequest;

class StoreQcProtocolRequest extends FormRequest
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
            'result_label' => ['nullable', 'array'],
            'result_value' => ['nullable', 'string', 'max:255'],

            'image_path_token' => ['nullable', 'string', 'ulid'],
            'remove_image_path' => ['boolean'],
            'is_active' => ['boolean'],
        ];

        foreach (Locale::supported() as $locale) {
            $required = $locale === $default ? 'required' : 'nullable';

            $rules["title.{$locale}"] = [$required, 'string', 'max:255'];
            $rules["description.{$locale}"] = ['nullable', 'string', 'max:1000'];
            $rules["result_label.{$locale}"] = ['nullable', 'string', 'max:255'];
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        $messages = [
            'result_value.max' => 'Nilai hasil maksimal 255 karakter.',
        ];

        foreach (Locale::supported() as $locale) {
            $messages["title.{$locale}.required"] = 'Nama protokol wajib diisi.';
        }

        return $messages;
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'title' => 'nama protokol',
            'result_label' => 'label hasil',
            'result_value' => 'nilai hasil',
        ];
    }
}
