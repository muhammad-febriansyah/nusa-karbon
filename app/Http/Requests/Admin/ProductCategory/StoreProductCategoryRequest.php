<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\ProductCategory;

use App\Repositories\Contracts\ProductCategoryRepositoryInterface;
use App\Support\Locale;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StoreProductCategoryRequest extends FormRequest
{
    /**
     * Aturan dibangun dari daftar locale aktif, sehingga menambah bahasa baru
     * cukup dilakukan di `config/locales.php`.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $default = Locale::default();

        $rules = [
            'name' => ['required', 'array'],
            'slug' => ['required', 'array'],
            'description' => ['nullable', 'array'],
            'icon' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ];

        foreach (Locale::supported() as $locale) {
            $required = $locale === $default ? 'required' : 'nullable';

            $rules["name.{$locale}"] = [$required, 'string', 'max:255'];
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
            $messages["name.{$locale}.required"] = 'Nama kategori wajib diisi.';
            $messages["slug.{$locale}.required"] = 'Slug wajib diisi.';
            $messages["slug.{$locale}.regex"] = 'Slug hanya boleh berisi huruf kecil, angka, dan tanda hubung.';
        }

        return $messages;
    }

    /**
     * Slug dibuat otomatis dari nama bila dikosongkan.
     */
    protected function prepareForValidation(): void
    {
        /** @var array<string, string|null> $name */
        $name = $this->input('name', []);
        /** @var array<string, string|null> $slug */
        $slug = $this->input('slug', []);

        foreach (Locale::supported() as $locale) {
            $currentSlug = $slug[$locale] ?? null;

            if (($currentSlug === null || $currentSlug === '') && ! empty($name[$locale])) {
                $slug[$locale] = Str::slug((string) $name[$locale]);
            }
        }

        $this->merge(['slug' => $slug]);
    }

    /**
     * Slug wajib unik per locale, jadi tidak bisa memakai `Rule::unique()`
     * biasa pada kolom JSON.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            /** @var array<string, string|null> $slug */
            $slug = $this->input('slug', []);
            $categories = app(ProductCategoryRepositoryInterface::class);

            foreach (Locale::supported() as $locale) {
                $value = $slug[$locale] ?? null;

                if ($value === null || $value === '') {
                    continue;
                }

                if ($categories->slugExists($locale, $value, $this->currentCategoryId())) {
                    $validator->errors()->add(
                        "slug.{$locale}",
                        'Slug ini sudah dipakai kategori lain. Gunakan yang lain.',
                    );
                }
            }
        });
    }

    protected function currentCategoryId(): ?int
    {
        return null;
    }
}
