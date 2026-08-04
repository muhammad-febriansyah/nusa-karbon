<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Faq;

use App\Support\Locale;
use Illuminate\Foundation\Http\FormRequest;

class StoreFaqRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $default = Locale::default();

        $rules = [
            'faq_category_id' => ['nullable', 'integer', 'exists:faq_categories,id'],
            'question' => ['required', 'array'],
            'answer' => ['required', 'array'],
            'is_active' => ['boolean'],
        ];

        foreach (Locale::supported() as $locale) {
            $required = $locale === $default ? 'required' : 'nullable';

            $rules["question.{$locale}"] = [$required, 'string', 'max:500'];
            $rules["answer.{$locale}"] = [$required, 'string', 'max:5000'];
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        $messages = [
            'faq_category_id.exists' => 'Kategori FAQ yang dipilih tidak ditemukan.',
        ];

        foreach (Locale::supported() as $locale) {
            $messages["question.{$locale}.required"] = 'Pertanyaan wajib diisi.';
            $messages["answer.{$locale}.required"] = 'Jawaban wajib diisi.';
            $messages["question.{$locale}.max"] = 'Pertanyaan maksimal 500 karakter.';
        }

        return $messages;
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'faq_category_id' => 'kategori',
        ];
    }
}
