/**
 * Satu bahasa yang aktif di `config/locales.php`.
 *
 * `prefix` adalah segmen URL bahasa tersebut — kosong untuk bahasa default,
 * karena bahasa default sengaja tidak memakai prefix agar tidak menimbulkan
 * konten duplikat di mesin pencari.
 *
 * @see docs/08-multilingual.md bagian 2
 */
type LocaleIdentity = {
    code: string;
    name: string;
    native: string;
    dir: 'ltr' | 'rtl';
    /** Kode negara bendera dari `config/locales.php`, misalnya `id` atau `gb`. */
    flag: string;
};

export type SupportedLocale = LocaleIdentity & {
    prefix: string;
};

/**
 * Bahasa yang sudah diumumkan tetapi rutenya belum ada.
 *
 * Sengaja tanpa `prefix`: tidak ada alamat yang bisa dituju, dan tipe yang
 * memaksa `prefix` ada akan mengundang kode yang menautkannya ke halaman yang
 * belum dibuat.
 */
export type PlannedLocale = LocaleIdentity;
