<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Gallery;

use App\Enums\GalleryItemType;
use App\Support\Locale;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SyncGalleryItemsRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = [
            'items' => ['array', 'max:200'],
            'items.*.id' => ['nullable', 'integer', 'exists:gallery_items,id'],
            'items.*.type' => ['required', Rule::enum(GalleryItemType::class)],
            'items.*.token' => ['nullable', 'string', 'ulid'],
            'items.*.video_url' => ['nullable', 'url', 'max:500'],
            'items.*.caption' => ['nullable', 'array'],
        ];

        foreach (Locale::supported() as $locale) {
            $rules["items.*.caption.{$locale}"] = ['nullable', 'string', 'max:255'];
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'items.max' => 'Maksimal 200 item per album.',
            'items.*.video_url.url' => 'Alamat video harus berupa URL lengkap, termasuk https://.',
        ];
    }

    /**
     * Item baru wajib membawa berkas atau URL video; tanpa keduanya barisnya
     * kosong dan tidak menampilkan apa pun di halaman publik.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            /** @var array<int, array<string, mixed>> $items */
            $items = (array) $this->input('items', []);

            foreach ($items as $index => $item) {
                $isNew = ! isset($item['id']);
                $hasToken = is_string($item['token'] ?? null) && $item['token'] !== '';
                $hasVideoUrl = is_string($item['video_url'] ?? null) && $item['video_url'] !== '';

                if ($isNew && ! $hasToken && ! $hasVideoUrl) {
                    $validator->errors()->add(
                        "items.{$index}.token",
                        'Item baru harus berisi berkas unggahan atau alamat video.',
                    );
                }
            }
        });
    }
}
