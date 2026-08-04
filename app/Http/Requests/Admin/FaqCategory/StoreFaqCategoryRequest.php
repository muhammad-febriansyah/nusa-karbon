<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\FaqCategory;

use App\Support\Locale;
use Illuminate\Foundation\Http\FormRequest;

class StoreFaqCategoryRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $default = Locale::default();

        $rules = [
            'name' => ['required', 'array'],
            'is_active' => ['boolean'],
        ];

        foreach (Locale::supported() as $locale) {
            $required = $locale === $default ? 'required' : 'nullable';

            $rules["name.{$locale}"] = [$required, 'string', 'max:255'];
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
            $messages["name.{$locale}.required"] = 'Nama kategori wajib diisi.';
        }

        return $messages;
    }
}
