<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\PageBlock;

use App\Support\Locale;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Hanya isi blok yang boleh berubah. `page_key`, `block_key`, dan `type`
 * sengaja tidak divalidasi maupun diterima — halaman publik bergantung pada
 * kunci itu.
 *
 * @see docs/09-tasks.md bagian 3.7
 */
final class UpdatePageBlockRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = [
            'title' => ['nullable', 'array'],
            'content' => ['nullable', 'array'],
            'extra' => ['nullable', 'array'],
            'extra.items' => ['nullable', 'array', 'max:20'],
            'extra.items.*' => ['string', 'max:500'],
            'extra.value' => ['nullable', 'string', 'max:100'],
            'extra.unit' => ['nullable', 'string', 'max:50'],

            // Gambarnya sendiri sudah divalidasi `TempUploadRequest`.
            'image_path_token' => ['nullable', 'string', 'ulid'],
            'remove_image_path' => ['boolean'],

            'is_active' => ['boolean'],
        ];

        foreach (Locale::supported() as $locale) {
            $rules["title.{$locale}"] = ['nullable', 'string', 'max:255'];
            $rules["content.{$locale}"] = ['nullable', 'string', 'max:20000'];
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        $messages = [
            'extra.items.max' => 'Maksimal 20 butir daftar.',
            'extra.value.max' => 'Nilai maksimal 100 karakter.',
        ];

        foreach (Locale::supported() as $locale) {
            $messages["title.{$locale}.max"] = 'Judul maksimal 255 karakter.';
        }

        return $messages;
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'extra.items' => 'butir daftar',
            'extra.value' => 'nilai',
            'extra.unit' => 'satuan',
        ];
    }
}
