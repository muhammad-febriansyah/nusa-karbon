/**
 * Pratinjau tema di editor. Hanya halaman editor yang boleh menerapkan token
 * lewat JavaScript — di halaman lain token datang dari `<style>` yang dirender
 * server, supaya tidak ada kedipan warna default.
 *
 * @see docs/10-theming.md bagian 2
 */

const cssVariable = (key: string) => `--t-${key.replaceAll('_', '-')}`;

export function applyPreviewTokens(tokens: Record<string, string>): void {
    const root = document.documentElement;

    for (const [key, value] of Object.entries(tokens)) {
        root.style.setProperty(cssVariable(key), value);
    }
}

/**
 * Mengembalikan tampilan ke token bawaan halaman; dipanggil saat meninggalkan
 * editor tanpa menyimpan.
 */
export function clearPreviewTokens(keys: string[]): void {
    const root = document.documentElement;

    for (const key of keys) {
        root.style.removeProperty(cssVariable(key));
    }
}

/**
 * Luminansi relatif WCAG. Duplikat dari `App\Support\Color` supaya panel
 * kontras di editor bereaksi seketika tanpa round-trip ke server; sisi PHP
 * tetap yang menolak simpan.
 */
function relativeLuminance(hex: string): number {
    const value = hex.replace('#', '');
    const channels = [0, 2, 4].map((offset) =>
        Number.parseInt(value.slice(offset, offset + 2), 16),
    );

    const [r, g, b] = channels.map((channel) => {
        const ratio = channel / 255;

        return ratio <= 0.03928
            ? ratio / 12.92
            : ((ratio + 0.055) / 1.055) ** 2.4;
    });

    return 0.2126 * r + 0.7152 * g + 0.0722 * b;
}

export function contrastRatio(first: string, second: string): number {
    const a = relativeLuminance(first);
    const b = relativeLuminance(second);

    return (Math.max(a, b) + 0.05) / (Math.min(a, b) + 0.05);
}

/** Hitam atau putih — mana yang paling kontras terhadap latar. */
export function bestForeground(background: string): string {
    return contrastRatio(background, '#FFFFFF') >=
        contrastRatio(background, '#111111')
        ? '#FFFFFF'
        : '#111111';
}

export const isHex = (value: string) => /^#[0-9A-Fa-f]{6}$/.test(value);
