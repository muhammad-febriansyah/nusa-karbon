<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Gallery;

use App\Repositories\Contracts\GalleryRepositoryInterface;
use App\Support\Locale;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StoreGalleryRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $default = Locale::default();

        $rules = [
            'title' => ['required', 'array'],
            'slug' => ['required', 'array'],
            'description' => ['nullable', 'array'],

            'cover_token' => ['nullable', 'string', 'ulid'],
            'remove_cover' => ['boolean'],

            'is_active' => ['boolean'],
        ];

        foreach (Locale::supported() as $locale) {
            $required = $locale === $default ? 'required' : 'nullable';

            $rules["title.{$locale}"] = [$required, 'string', 'max:255'];
            $rules["slug.{$locale}"] = [$required, 'string', 'max:255', 'regex:/^[a-z0-9-]+$/'];
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
            $messages["title.{$locale}.required"] = 'Judul album wajib diisi.';
            $messages["slug.{$locale}.required"] = 'Slug album wajib diisi.';
            $messages["slug.{$locale}.regex"] = 'Slug hanya boleh berisi huruf kecil, angka, dan tanda hubung.';
        }

        return $messages;
    }

    /**
     * Slug dikosongi admin diisi otomatis dari judul, lalu tetap bisa diubah.
     */
    protected function prepareForValidation(): void
    {
        $locale = Locale::default();

        /** @var array<string, string|null> $slug */
        $slug = (array) $this->input('slug', []);
        /** @var array<string, string|null> $title */
        $title = (array) $this->input('title', []);

        if (($slug[$locale] ?? '') === '' && is_string($title[$locale] ?? null)) {
            $slug[$locale] = Str::slug($title[$locale]);

            $this->merge(['slug' => $slug]);
        }
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $locale = Locale::default();
            /** @var array<string, string|null> $slug */
            $slug = (array) $this->input('slug', []);
            $value = $slug[$locale] ?? null;

            if (! is_string($value) || $value === '') {
                return;
            }

            $galleries = app(GalleryRepositoryInterface::class);

            if ($galleries->slugExists($value, $this->currentGalleryId())) {
                $validator->errors()->add(
                    "slug.{$locale}",
                    'Slug ini sudah dipakai album lain.',
                );
            }
        });
    }

    protected function currentGalleryId(): ?int
    {
        return null;
    }
}
