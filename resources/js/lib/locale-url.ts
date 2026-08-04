import type { SupportedLocale } from '@/types';

/**
 * Menukar prefix locale pada sebuah path.
 *
 * Bahasa default sengaja tidak memakai prefix — pindah ke bahasa default
 * berarti membuang segmen pertama, bukan menggantinya. Query string
 * dipertahankan apa adanya.
 *
 * Dipakai bersama oleh language switcher dan penyusun tag `hreflang`; keduanya
 * wajib menghasilkan URL yang sama persis, kalau tidak mesin pencari akan
 * melihat tautan bahasa yang tidak saling menunjuk.
 *
 * @see docs/08-multilingual.md bagian 2
 */
export function localizedPath(
    url: string,
    target: SupportedLocale,
    supported: SupportedLocale[],
): string {
    const [path = '', query] = url.split('?');
    const prefixes = supported
        .map((locale) => locale.prefix)
        .filter((prefix) => prefix !== '');

    const segments = path.split('/').filter(Boolean);

    if (segments.length > 0 && prefixes.includes(segments[0])) {
        segments.shift();
    }

    if (target.prefix !== '') {
        segments.unshift(target.prefix);
    }

    return `/${segments.join('/')}${query === undefined ? '' : `?${query}`}`;
}
