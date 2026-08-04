<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\TeamMember;

use App\Support\Locale;
use Illuminate\Foundation\Http\FormRequest;

class StoreTeamMemberRequest extends FormRequest
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
            'bio' => ['nullable', 'array'],
            'linkedin_url' => ['nullable', 'url', 'max:500'],

            'photo_path_token' => ['nullable', 'string', 'ulid'],
            'remove_photo_path' => ['boolean'],
            'is_active' => ['boolean'],
        ];

        foreach (Locale::supported() as $locale) {
            $required = $locale === $default ? 'required' : 'nullable';

            $rules["position.{$locale}"] = [$required, 'string', 'max:255'];
            $rules["bio.{$locale}"] = ['nullable', 'string', 'max:1000'];
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        $messages = [
            'name.required' => 'Nama anggota tim wajib diisi.',
            'linkedin_url.url' => 'Alamat LinkedIn harus berupa URL lengkap, termasuk https://.',
        ];

        foreach (Locale::supported() as $locale) {
            $messages["position.{$locale}.required"] = 'Jabatan wajib diisi.';
        }

        return $messages;
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nama anggota tim',
            'position' => 'jabatan',
            'linkedin_url' => 'alamat LinkedIn',
        ];
    }
}
