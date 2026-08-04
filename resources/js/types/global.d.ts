import type { Auth } from '@/types/auth';
import type { PlannedLocale, SupportedLocale } from '@/types/locale';

declare module 'react' {
    // eslint-disable-next-line @typescript-eslint/no-unused-vars
    interface InputHTMLAttributes<T> {
        passwordrules?: string;
    }
}

declare module '@inertiajs/core' {
    export interface InertiaConfig {
        sharedPageProps: {
            name: string;
            /** Identitas merek dari Pengaturan Situs. */
            brand: {
                name: string;
                logo: string | null;
                favicon: string | null;
            };
            auth: Auth;
            sidebarOpen: boolean;
            unread: {
                rfq: number;
                contactMessages: number;
            };
            /** Locale aktif dan daftar bahasa dari `config/locales.php`. */
            locale: {
                current: string;
                direction: 'ltr' | 'rtl';
                supported: SupportedLocale[];
                /** Bahasa Fase 6 — tampil di pemilih, belum bisa dituju. */
                planned: PlannedLocale[];
            };
            /**
             * Pengaturan situs bertanda `is_public`. Kosong pada rute admin —
             * halaman admin membacanya lewat propnya sendiri.
             */
            settings: Record<string, unknown>;
            /** Basis URL kanonik dan hreflang. `null` pada rute admin. */
            seo: {
                canonical: string;
                baseUrl: string;
                /**
                 * Path request apa adanya, selalu diawali garis miring.
                 * Masih memuat prefix locale bila ada; `localizedPath()` yang
                 * menukarnya saat menyusun hreflang.
                 */
                path: string;
            } | null;
            [key: string]: unknown;
        };
    }
}
