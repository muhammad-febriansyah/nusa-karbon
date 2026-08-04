import { usePage } from '@inertiajs/react';

/**
 * Pembaca Pengaturan Situs untuk halaman publik.
 *
 * Nilainya sudah diselesaikan server (`SettingService::publicSettings`): kolom
 * translatable berupa string locale aktif, path berkas berupa URL. Hook ini
 * hanya menambahkan pemeriksaan tipe dan menganggap string kosong sebagai
 * `null`, supaya komponen cukup menulis `{value && <p>{value}</p>}` alih-alih
 * memeriksa dua kondisi di setiap tempat.
 */
export function usePublicSettings() {
    const settings = usePage().props.settings;

    const text = (key: string): string | null => {
        const value = settings[key];

        return typeof value === 'string' && value.trim() !== '' ? value : null;
    };

    const number = (key: string): number | null => {
        const value = settings[key];

        if (typeof value === 'number') {
            return value;
        }

        if (typeof value === 'string' && value.trim() !== '') {
            const parsed = Number(value);

            return Number.isNaN(parsed) ? null : parsed;
        }

        return null;
    };

    const boolean = (key: string): boolean => settings[key] === true;

    return { text, number, boolean };
}
