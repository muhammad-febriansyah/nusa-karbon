<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Post;
use Mews\Purifier\Facades\Purifier;

/**
 * Pembantu isi artikel: pembersihan HTML dan perkiraan waktu baca.
 *
 * Isi artikel datang dari editor TipTap, jadi HTML-nya selalu dibersihkan di
 * sisi server — editor di browser bisa dilewati siapa pun yang mengirim
 * request sendiri.
 *
 * @see docs/09-tasks.md bagian 3.4
 */
final class PostContent
{
    /**
     * Membersihkan HTML dari tag dan atribut berbahaya.
     */
    public static function sanitise(?string $html): ?string
    {
        if ($html === null || trim($html) === '') {
            return null;
        }

        return Purifier::clean($html);
    }

    /**
     * Membersihkan seluruh nilai translatable sekaligus.
     *
     * @param  array<string, string|null>|null  $values
     * @return array<string, string|null>|null
     */
    public static function sanitiseTranslations(?array $values): ?array
    {
        if ($values === null) {
            return null;
        }

        return array_map(
            static fn (?string $value): ?string => self::sanitise($value),
            $values,
        );
    }

    /**
     * Perkiraan waktu baca dalam menit, minimal satu menit untuk artikel yang
     * ada isinya.
     *
     * @param  array<string, string|null>|null  $content
     */
    public static function readingMinutes(?array $content): ?int
    {
        if ($content === null) {
            return null;
        }

        $text = trim(strip_tags(implode(' ', array_filter($content))));

        if ($text === '') {
            return null;
        }

        $words = count(preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY) ?: []);

        return max(1, (int) ceil($words / Post::WORDS_PER_MINUTE));
    }
}
