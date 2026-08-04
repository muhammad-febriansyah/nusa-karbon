<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Theme;

use App\Models\Theme;
use App\Repositories\Contracts\ThemeRepositoryInterface;
use App\Support\Color;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreThemeRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        // Hanya hex 6 digit: menolak rgb(), hsl(), nama warna CSS, dan hex 3
        // digit sekaligus menutup celah injeksi lewat kolom database.
        $hex = ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'];

        $rules = [
            'name' => ['required', 'string', 'max:100'],
            'scope' => ['required', Rule::in([
                Theme::SCOPE_ADMIN,
                Theme::SCOPE_PUBLIC,
                Theme::SCOPE_BOTH,
            ])],
            'tokens' => ['required', 'array'],
            'tokens.radius' => ['required', 'string', 'regex:/^\d+(\.\d+)?(rem|px)$/'],
        ];

        foreach (Theme::COLOR_KEYS as $key) {
            $rules["tokens.{$key}"] = $hex;
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        $messages = [
            'name.required' => 'Nama tema wajib diisi.',
            'scope.in' => 'Lingkup tema tidak dikenal.',
            'tokens.radius.regex' => 'Radius harus dalam satuan rem atau px, misalnya 0.5rem.',
        ];

        foreach (Theme::COLOR_KEYS as $key) {
            $messages["tokens.{$key}.regex"] = "Warna {$key} harus hex 6 digit, misalnya #C94A17.";
            $messages["tokens.{$key}.required"] = "Warna {$key} wajib diisi.";
        }

        return $messages;
    }

    protected function prepareForValidation(): void
    {
        if (! $this->has('slug') && is_string($this->input('name'))) {
            $this->merge(['slug' => Str::slug($this->input('name'))]);
        }
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $this->rejectUnknownTokens($validator);
            $this->rejectMissingTokens($validator);
            $this->rejectLowContrast($validator);
            $this->rejectDuplicateSlug($validator);
        });
    }

    protected function currentThemeId(): ?int
    {
        return null;
    }

    /**
     * @return array<string, string>
     */
    protected function tokens(): array
    {
        /** @var array<string, string> $tokens */
        $tokens = (array) $this->input('tokens', []);

        return $tokens;
    }

    /**
     * Kunci di luar daftar tetap ditolak agar tidak ada token liar yang
     * tersimpan tapi tidak pernah dirender.
     */
    private function rejectUnknownTokens(Validator $validator): void
    {
        $unknown = array_diff(array_keys($this->tokens()), Theme::tokenKeys());

        if ($unknown !== []) {
            $validator->errors()->add(
                'tokens',
                'Token tidak dikenal: '.implode(', ', $unknown).'.',
            );
        }
    }

    private function rejectMissingTokens(Validator $validator): void
    {
        $missing = array_diff(Theme::tokenKeys(), array_keys($this->tokens()));

        if ($missing !== []) {
            $validator->errors()->add(
                'tokens',
                'Token wajib belum lengkap: '.implode(', ', $missing).'.',
            );
        }
    }

    /**
     * Palet yang tidak terbaca ditolak sebelum tersimpan. Pesannya menyebut
     * angka nyata supaya admin tahu seberapa jauh dari ambang.
     */
    private function rejectLowContrast(Validator $validator): void
    {
        $tokens = $this->tokens();

        foreach (Theme::CONTRAST_PAIRS as [$background, $foreground, $minimum, $blocking]) {
            if (! $blocking) {
                continue;
            }

            $backgroundValue = $tokens[$background] ?? null;
            $foregroundValue = $tokens[$foreground] ?? null;

            if (! is_string($backgroundValue) || ! is_string($foregroundValue)) {
                continue;
            }

            if (! Color::isHex($backgroundValue) || ! Color::isHex($foregroundValue)) {
                continue;
            }

            $ratio = Color::contrastRatio($backgroundValue, $foregroundValue);

            if ($ratio + 0.005 < $minimum) {
                $validator->errors()->add(
                    "tokens.{$foreground}",
                    sprintf(
                        'Kontras %s terhadap %s hanya %s:1. Minimal %s:1 agar terbaca. Gelapkan warnanya atau ubah warna teksnya.',
                        $foreground,
                        $background,
                        number_format($ratio, 1, ',', '.'),
                        number_format($minimum, 1, ',', '.'),
                    ),
                );
            }
        }
    }

    private function rejectDuplicateSlug(Validator $validator): void
    {
        $slug = $this->input('slug');

        if (! is_string($slug) || $slug === '') {
            return;
        }

        $themes = app(ThemeRepositoryInterface::class);

        if ($themes->slugExists($slug, $this->currentThemeId())) {
            $validator->errors()->add('name', 'Nama tema ini sudah dipakai. Gunakan nama lain.');
        }
    }
}
