<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;
use Illuminate\Translation\PotentiallyTranslatedString;

/**
 * Menolak email berdomain gratisan untuk perwakilan resmi.
 *
 * Halaman "Verify Our Representative" dipakai buyer untuk memastikan orang
 * yang menghubunginya benar-benar dari perusahaan. Perwakilan yang memakai
 * Gmail atau sejenisnya membuat halaman itu kehilangan gunanya — penipu bisa
 * membuat alamat serupa dalam hitungan menit.
 *
 * @see docs/09-tasks.md bagian 3.2
 */
final class CompanyEmail implements ValidationRule
{
    /**
     * Penyedia email gratis yang paling sering dipakai buyer maupun penipu.
     *
     * @var list<string>
     */
    public const FREE_DOMAINS = [
        'gmail.com',
        'googlemail.com',
        'yahoo.com',
        'yahoo.co.id',
        'ymail.com',
        'rocketmail.com',
        'hotmail.com',
        'outlook.com',
        'outlook.co.id',
        'live.com',
        'msn.com',
        'aol.com',
        'icloud.com',
        'me.com',
        'mail.com',
        'gmx.com',
        'zoho.com',
        'protonmail.com',
        'proton.me',
        'mail.ru',
        'yandex.com',
        'yandex.ru',
        'qq.com',
        '163.com',
        '126.com',
        'naver.com',
        'rediffmail.com',
    ];

    public static function isFreeDomain(string $email): bool
    {
        $domain = Str::lower(Str::after($email, '@'));

        return in_array($domain, self::FREE_DOMAINS, true);
    }

    /**
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! Str::contains($value, '@')) {
            return;
        }

        if (self::isFreeDomain($value)) {
            $fail('Perwakilan resmi wajib memakai email domain perusahaan. Alamat gratisan seperti Gmail atau Yahoo justru dipakai penipu untuk menyamar sebagai perwakilan.');
        }
    }
}
