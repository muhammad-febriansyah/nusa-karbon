import type { ReactNode } from 'react';
import { cn } from '@/lib/utils';

type Props = {
    /** Kode negara dari `config/locales.php`, misalnya `id` atau `gb`. */
    code: string;
    className?: string;
};

/**
 * Bendera digambar sebagai SVG, bukan emoji.
 *
 * Emoji bendera dirender berbeda di tiap sistem operasi — dan di Windows tidak
 * dirender sama sekali, hanya muncul sebagai dua huruf kode negara. Ini juga
 * mengikuti aturan proyek: emoji tidak pernah dipakai sebagai ikon.
 *
 * Bentuknya disederhanakan seperlunya agar tetap terbaca pada ukuran 20 piksel.
 * Bendera Saudi memuat kalimat syahadat yang mustahil terbaca selebar 24 unit,
 * jadi tulisannya diwakili guratan — sama seperti seluruh set ikon bendera yang
 * beredar. Cukup untuk penanda bahasa di navbar, tidak untuk dokumen resmi,
 * cetakan, atau ukuran besar; untuk itu pakai berkas bendera resmi.
 *
 * Kode yang belum digambar jatuh ke lencana kode negara, bukan ke bendera
 * tebakan. Bendera yang salah lebih buruk daripada tidak ada bendera.
 */
const FLAGS: Record<string, ReactNode> = {
    id: (
        <>
            <rect width="24" height="8" fill="#CE1126" />
            <rect y="8" width="24" height="8" fill="#FFFFFF" />
        </>
    ),
    ru: (
        <>
            <rect width="24" height="5.34" fill="#FFFFFF" />
            <rect y="5.34" width="24" height="5.33" fill="#0039A6" />
            <rect y="10.67" width="24" height="5.33" fill="#D52B1E" />
        </>
    ),
    gb: (
        <>
            <rect width="24" height="16" fill="#012169" />
            <path
                d="M0 0 L24 16 M24 0 L0 16"
                stroke="#FFFFFF"
                strokeWidth="3.2"
            />
            <path
                d="M0 0 L24 16 M24 0 L0 16"
                stroke="#C8102E"
                strokeWidth="1.8"
            />
            <path d="M12 0 V16 M0 8 H24" stroke="#FFFFFF" strokeWidth="5.3" />
            <path d="M12 0 V16 M0 8 H24" stroke="#C8102E" strokeWidth="3.2" />
        </>
    ),
    sa: (
        <>
            <rect width="24" height="16" fill="#165D31" />
            {/* Guratan yang mewakili tulisan; tidak terbaca pada ukuran ini. */}
            <path
                d="M5 6.2 H19"
                stroke="#FFFFFF"
                strokeWidth="1.5"
                strokeLinecap="round"
                strokeDasharray="2.6 1.4"
            />
            {/* Pedang: bilah mendatar dengan ujung runcing di sebelah kiri. */}
            <path
                d="M6 10.6 H18"
                stroke="#FFFFFF"
                strokeWidth="1"
                strokeLinecap="round"
            />
            <path d="M6 10.6 L8 9.6 V11.6 Z" fill="#FFFFFF" />
        </>
    ),
};

export default function LocaleFlag({ code, className }: Props) {
    const flag = FLAGS[code.toLowerCase()];

    /*
     * Tanpa gambar, kode negaranya sendiri yang tampil. Bentuk dan ukurannya
     * sengaja sama persis dengan bendera lain supaya baris pilihannya tetap
     * lurus meski sebagiannya belum digambar.
     */
    if (flag === undefined) {
        return (
            <span
                aria-hidden
                className={cn(
                    'inline-flex h-4 w-6 shrink-0 items-center justify-center',
                    'rounded-xs border border-border font-mono text-[9px]',
                    'font-medium text-muted-foreground uppercase',
                    className,
                )}
            >
                {code.slice(0, 2)}
            </span>
        );
    }

    return (
        <svg
            viewBox="0 0 24 16"
            aria-hidden
            className={cn(
                'h-4 w-6 shrink-0 rounded-xs border border-border',
                className,
            )}
        >
            {flag}
        </svg>
    );
}
