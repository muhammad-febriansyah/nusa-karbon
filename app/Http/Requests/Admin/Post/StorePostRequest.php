<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Post;

use App\Enums\PostStatus;
use App\Repositories\Contracts\PostRepositoryInterface;
use App\Support\Locale;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StorePostRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $default = Locale::default();

        $rules = [
            'post_category_id' => ['nullable', 'integer', 'exists:post_categories,id'],

            'title' => ['required', 'array'],
            'slug' => ['required', 'array'],
            'excerpt' => ['nullable', 'array'],
            'content' => ['nullable', 'array'],
            'meta_title' => ['nullable', 'array'],
            'meta_description' => ['nullable', 'array'],

            'status' => ['required', Rule::enum(PostStatus::class)],
            'published_at' => [
                'nullable',
                'date',
                Rule::requiredIf(fn (): bool => $this->input('status') === PostStatus::Scheduled->value),
            ],

            'tags' => ['array', 'max:20'],
            'tags.*' => ['string', 'max:50'],

            'cover_token' => ['nullable', 'string', 'ulid'],
            'remove_cover' => ['boolean'],
        ];

        foreach (Locale::supported() as $locale) {
            $required = $locale === $default ? 'required' : 'nullable';

            $rules["title.{$locale}"] = [$required, 'string', 'max:255'];
            $rules["slug.{$locale}"] = [$required, 'string', 'max:255', 'regex:/^[a-z0-9-]+$/'];
            $rules["excerpt.{$locale}"] = ['nullable', 'string', 'max:500'];
            $rules["content.{$locale}"] = ['nullable', 'string', 'max:100000'];
            $rules["meta_title.{$locale}"] = ['nullable', 'string', 'max:255'];
            $rules["meta_description.{$locale}"] = ['nullable', 'string', 'max:500'];
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        $messages = [
            'status.required' => 'Status artikel wajib dipilih.',
            'published_at.required' => 'Artikel terjadwal wajib punya tanggal tayang.',
            'tags.max' => 'Maksimal 20 tag per artikel.',
        ];

        foreach (Locale::supported() as $locale) {
            $messages["title.{$locale}.required"] = 'Judul artikel wajib diisi.';
            $messages["slug.{$locale}.required"] = 'Slug artikel wajib diisi.';
            $messages["slug.{$locale}.regex"] = 'Slug hanya boleh berisi huruf kecil, angka, dan tanda hubung.';
        }

        return $messages;
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'post_category_id' => 'kategori',
            'published_at' => 'tanggal tayang',
        ];
    }

    /**
     * Slug dikosongi admin diisi otomatis dari judul.
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

            $posts = app(PostRepositoryInterface::class);

            if ($posts->slugExists($value, $this->currentPostId())) {
                $validator->errors()->add(
                    "slug.{$locale}",
                    'Slug ini sudah dipakai artikel lain.',
                );
            }
        });
    }

    protected function currentPostId(): ?int
    {
        return null;
    }
}
