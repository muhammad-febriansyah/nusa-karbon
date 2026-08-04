<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Representative;

use App\Repositories\Contracts\RepresentativeRepositoryInterface;
use App\Rules\CompanyEmail;
use App\Support\Locale;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StoreRepresentativeRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $default = Locale::default();

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'position' => ['required', 'array'],
            'region' => ['nullable', 'string', 'max:255'],

            'email' => ['required', 'email:rfc', 'max:255', new CompanyEmail],
            'phone' => ['nullable', 'string', 'max:50'],
            'whatsapp' => ['nullable', 'string', 'max:50'],

            'started_at' => ['nullable', 'date'],
            'ended_at' => ['nullable', 'date', 'after_or_equal:started_at'],

            // Fotonya sendiri sudah divalidasi `TempUploadRequest`.
            'photo_token' => ['nullable', 'string', 'ulid'],
            'remove_photo' => ['boolean'],

            'is_active' => ['boolean'],
        ];

        foreach (Locale::supported() as $locale) {
            $required = $locale === $default ? 'required' : 'nullable';

            $rules["position.{$locale}"] = [$required, 'string', 'max:255'];
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        $messages = [
            'name.required' => 'Nama perwakilan wajib diisi.',
            'email.required' => 'Email perwakilan wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'ended_at.after_or_equal' => 'Tanggal berakhir tidak boleh lebih awal dari tanggal mulai.',
        ];

        foreach (Locale::supported() as $locale) {
            $messages["position.{$locale}.required"] = 'Jabatan perwakilan wajib diisi.';
        }

        return $messages;
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nama perwakilan',
            'region' => 'wilayah',
            'started_at' => 'tanggal mulai',
            'ended_at' => 'tanggal berakhir',
        ];
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('email'))) {
            $this->merge(['email' => Str::lower(trim($this->input('email')))]);
        }
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $email = $this->input('email');

            if (! is_string($email) || $email === '') {
                return;
            }

            $representatives = app(RepresentativeRepositoryInterface::class);

            if ($representatives->emailExists($email, $this->currentRepresentativeId())) {
                $validator->errors()->add(
                    'email',
                    'Email ini sudah dipakai perwakilan lain. Satu email hanya boleh untuk satu perwakilan agar verifikasi buyer tidak ambigu.',
                );
            }
        });
    }

    protected function currentRepresentativeId(): ?int
    {
        return null;
    }
}
