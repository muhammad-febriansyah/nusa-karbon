<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\PostCategory;

use App\Repositories\Contracts\PostCategoryRepositoryInterface;
use App\Support\Locale;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StorePostCategoryRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $default = Locale::default();

        $rules = [
            'name' => ['required', 'array'],
            'slug' => ['required', 'array'],
            'description' => ['nullable', 'array'],
            'is_active' => ['boolean'],
        ];

        foreach (Locale::supported() as $locale) {
            $required = $locale === $default ? 'required' : 'nullable';

            $rules["name.{$locale}"] = [$required, 'string', 'max:255'];
            $rules["slug.{$locale}"] = [$required, 'string', 'max:255', 'regex:/^[a-z0-9-]+$/'];
            $rules["description.{$locale}"] = ['nullable', 'string', 'max:500'];
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
            $messages["slug.{$locale}.required"] = 'Slug kategori wajib diisi.';
            $messages["slug.{$locale}.regex"] = 'Slug hanya boleh berisi huruf kecil, angka, dan tanda hubung.';
        }

        return $messages;
    }

    protected function prepareForValidation(): void
    {
        $locale = Locale::default();

        /** @var array<string, string|null> $slug */
        $slug = (array) $this->input('slug', []);
        /** @var array<string, string|null> $name */
        $name = (array) $this->input('name', []);

        if (($slug[$locale] ?? '') === '' && is_string($name[$locale] ?? null)) {
            $slug[$locale] = Str::slug($name[$locale]);

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

            $categories = app(PostCategoryRepositoryInterface::class);

            if ($categories->slugExists($value, $this->currentPostCategoryId())) {
                $validator->errors()->add(
                    "slug.{$locale}",
                    'Slug ini sudah dipakai kategori lain.',
                );
            }
        });
    }

    protected function currentPostCategoryId(): ?int
    {
        return null;
    }
}
