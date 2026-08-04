<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\ContainerType;

use App\Repositories\Contracts\ContainerTypeRepositoryInterface;
use App\Support\Locale;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StoreContainerTypeRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $default = Locale::default();

        $rules = [
            'code' => ['required', 'string', 'max:30', 'regex:/^[A-Z0-9_-]+$/'],
            'name' => ['required', 'array'],

            // Seluruh nilai numerik harus lebih dari nol: kapasitas atau
            // dimensi bernilai 0 akan membuat Container Calculator membagi
            // dengan nol atau mengembalikan 0 karton.
            'max_payload_kg' => ['required', 'numeric', 'min:0.01', 'max:99999999.99'],
            'max_cbm' => ['required', 'numeric', 'min:0.01', 'max:99999999.99'],
            'internal_length_m' => ['nullable', 'numeric', 'min:0.01', 'max:9999.99'],
            'internal_width_m' => ['nullable', 'numeric', 'min:0.01', 'max:9999.99'],
            'internal_height_m' => ['nullable', 'numeric', 'min:0.01', 'max:9999.99'],

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
        $messages = [
            'code.required' => 'Kode kontainer wajib diisi.',
            'code.regex' => 'Kode kontainer hanya boleh berisi huruf kapital, angka, garis bawah, dan tanda hubung.',
            'max_payload_kg.required' => 'Kapasitas berat wajib diisi.',
            'max_payload_kg.min' => 'Kapasitas berat harus lebih dari 0.',
            'max_cbm.required' => 'Kapasitas volume wajib diisi.',
            'max_cbm.min' => 'Kapasitas volume harus lebih dari 0.',
            'internal_length_m.min' => 'Panjang internal harus lebih dari 0.',
            'internal_width_m.min' => 'Lebar internal harus lebih dari 0.',
            'internal_height_m.min' => 'Tinggi internal harus lebih dari 0.',
        ];

        foreach (Locale::supported() as $locale) {
            $messages["name.{$locale}.required"] = 'Nama kontainer wajib diisi.';
        }

        return $messages;
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'code' => 'kode kontainer',
            'max_payload_kg' => 'kapasitas berat',
            'max_cbm' => 'kapasitas volume',
            'internal_length_m' => 'panjang internal',
            'internal_width_m' => 'lebar internal',
            'internal_height_m' => 'tinggi internal',
        ];
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('code'))) {
            $this->merge(['code' => Str::upper(trim($this->input('code')))]);
        }
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $code = $this->input('code');

            if (! is_string($code) || $code === '') {
                return;
            }

            $containerTypes = app(ContainerTypeRepositoryInterface::class);

            if ($containerTypes->codeExists($code, $this->currentContainerTypeId())) {
                $validator->errors()->add(
                    'code',
                    'Kode kontainer ini sudah dipakai. Gunakan yang lain.',
                );
            }
        });
    }

    protected function currentContainerTypeId(): ?int
    {
        return null;
    }
}
