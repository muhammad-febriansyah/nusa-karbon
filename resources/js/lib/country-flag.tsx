/**
 * Bendera negara dari kode ISO 3166-1 alpha-2.
 *
 * Dirakit dari pasangan Regional Indicator Symbol — huruf "A" pada kode jadi
 * U+1F1E6, dan seterusnya. Tidak ada berkas gambar sama sekali: 250 bendera
 * sebagai SVG berarti aset ratusan kilobita untuk sesuatu yang hanya muncul di
 * satu isian formulir.
 *
 * Peramban di Windows tidak punya glif bendera dan menampilkan dua hurufnya
 * saja. Itu bukan kegagalan yang perlu ditutup: yang muncul persis kode
 * negaranya, dan kode itu memang ditampilkan juga di ujung baris daftar.
 */
export function countryFlag(code: string): string {
    const BASE = 0x1f1e6;
    const LETTER_A = 'A'.charCodeAt(0);

    return String.fromCodePoint(
        ...[...code.toUpperCase()].map(
            (letter) => BASE + letter.charCodeAt(0) - LETTER_A,
        ),
    );
}

/**
 * Bendera sebagai elemen, siap dipakai sebagai ikon pilihan.
 *
 * `aria-hidden` karena nama negaranya sudah tertulis tepat di sebelahnya —
 * pembaca layar tidak perlu mendengar "bendera Uni Emirat Arab, Uni Emirat
 * Arab".
 */
export function CountryFlag({ code }: { code: string }) {
    return (
        <span aria-hidden className="text-base leading-none">
            {countryFlag(code)}
        </span>
    );
}
