<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Product;

use App\Enums\DocumentType;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Support\Locale;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $default = Locale::default();

        $rules = [
            'product_category_id' => ['nullable', 'exists:product_categories,id'],
            'code' => ['required', 'string', 'max:50', 'regex:/^[A-Z0-9-]+$/'],

            'name' => ['required', 'array'],
            'slug' => ['required', 'array'],

            'short_description' => ['nullable', 'array'],
            'short_description.*' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'array'],
            'description.*' => ['nullable', 'string'],
            'meta_title' => ['nullable', 'array'],
            'meta_title.*' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'array'],
            'meta_description.*' => ['nullable', 'string', 'max:500'],

            'shape' => ['nullable', 'string', 'max:100'],
            'size_mm' => ['nullable', 'string', 'max:100'],

            'master_carton_weight_kg' => ['nullable', 'numeric', 'min:0.001', 'max:9999.999'],
            'master_carton_tare_kg' => ['nullable', 'numeric', 'min:0', 'max:999.999'],
            'master_carton_cbm' => ['nullable', 'numeric', 'min:0.0001', 'max:999.9999'],
            'master_carton_length_cm' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'master_carton_width_cm' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'master_carton_height_cm' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'inner_box_weight_kg' => ['nullable', 'numeric', 'min:0', 'max:9999.999'],
            'inner_boxes_per_carton' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'moq_kg' => ['nullable', 'numeric', 'min:0'],

            'is_active' => ['boolean'],
            'is_featured' => ['boolean'],

            'cover_tokens' => ['array', 'max:1'],
            'cover_tokens.*' => ['string', 'ulid'],
            'gallery_tokens' => ['array', 'max:20'],
            'gallery_tokens.*' => ['string', 'ulid'],
            'removed_media_ids' => ['array'],
            'removed_media_ids.*' => ['integer'],

            'specifications' => ['array', 'max:50'],
            'specifications.*.id' => ['nullable', 'integer'],
            'specifications.*.value' => ['required', 'string', 'max:255'],
            'specifications.*.unit' => ['nullable', 'string', 'max:50'],
            'specifications.*.is_highlighted' => ['boolean'],

            // Berkasnya sendiri sudah divalidasi `TempUploadRequest` saat
            // diunggah; di sini hanya tokennya yang diperiksa.
            'documents' => ['array', 'max:20'],
            'documents.*.id' => ['nullable', 'integer'],
            'documents.*.type' => ['required', Rule::enum(DocumentType::class)],
            'documents.*.token' => ['nullable', 'string', 'ulid'],
            'documents.*.batch_reference' => ['nullable', 'string', 'max:100'],
            'documents.*.issued_at' => ['nullable', 'date_format:Y-m-d'],
            'documents.*.is_active' => ['boolean'],

            'packaging_option_ids' => ['array'],
            'packaging_option_ids.*' => ['integer', 'exists:packaging_options,id'],
        ];

        foreach (Locale::supported() as $locale) {
            $required = $locale === $default ? 'required' : 'nullable';

            $rules["name.{$locale}"] = [$required, 'string', 'max:255'];
            $rules["slug.{$locale}"] = [$required, 'string', 'max:255', 'regex:/^[a-z0-9-]+$/'];
            $rules["specifications.*.parameter.{$locale}"] = [$required, 'string', 'max:255'];
            $rules["documents.*.title.{$locale}"] = [$required, 'string', 'max:255'];
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        $messages = [
            'code.required' => 'Kode produk wajib diisi.',
            'code.regex' => 'Kode produk hanya boleh berisi huruf kapital, angka, dan tanda hubung.',
            'master_carton_weight_kg.min' => 'Berat master carton harus lebih dari 0.',
            'master_carton_cbm.min' => 'Volume master carton harus lebih dari 0.',
            'gallery_tokens.max' => 'Galeri maksimal 20 gambar sekaligus.',
            'specifications.max' => 'Spesifikasi maksimal 50 baris.',
            'specifications.*.value.required' => 'Nilai spesifikasi wajib diisi.',
            'documents.max' => 'Dokumen maksimal 20 berkas.',
            'documents.*.type.required' => 'Tipe dokumen wajib dipilih.',
            'documents.*.issued_at.date_format' => 'Tanggal terbit tidak valid.',
        ];

        foreach (Locale::supported() as $locale) {
            $messages["specifications.*.parameter.{$locale}.required"] = 'Nama parameter spesifikasi wajib diisi.';
            $messages["documents.*.title.{$locale}.required"] = 'Judul dokumen wajib diisi.';
            $messages["name.{$locale}.required"] = 'Nama produk wajib diisi.';
            $messages["slug.{$locale}.required"] = 'Slug wajib diisi.';
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
            'code' => 'kode produk',
            'product_category_id' => 'kategori',
            'shape' => 'bentuk',
            'size_mm' => 'ukuran',
            'master_carton_weight_kg' => 'berat master carton',
            'master_carton_tare_kg' => 'berat karton kosong',
            'master_carton_cbm' => 'volume master carton',
            'inner_boxes_per_carton' => 'jumlah inner box per carton',
            'moq_kg' => 'MOQ',
        ];
    }

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

        $merge = ['slug' => $slug];

        if (is_string($this->input('code'))) {
            $merge['code'] = Str::upper(trim($this->input('code')));
        }

        $this->merge($merge);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $products = app(ProductRepositoryInterface::class);
            $exceptId = $this->currentProductId();

            $code = $this->input('code');

            if (is_string($code) && $code !== '' && $products->codeExists($code, $exceptId)) {
                $validator->errors()->add(
                    'code',
                    'Kode produk ini sudah dipakai produk lain. Gunakan yang lain.',
                );
            }

            /** @var array<string, string|null> $slug */
            $slug = $this->input('slug', []);

            foreach (Locale::supported() as $locale) {
                $value = $slug[$locale] ?? null;

                if ($value === null || $value === '') {
                    continue;
                }

                if ($products->slugExists($locale, $value, $exceptId)) {
                    $validator->errors()->add(
                        "slug.{$locale}",
                        'Slug ini sudah dipakai produk lain. Gunakan yang lain.',
                    );
                }
            }
        });
    }

    protected function currentProductId(): ?int
    {
        return null;
    }
}
