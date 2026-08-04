import type { ReactNode } from 'react';
import BlurFade from '@/components/public/blur-fade';
import { cn } from '@/lib/utils';

type Props = {
    /** Label kecil di atas judul. */
    eyebrow?: string;
    title: string;
    description?: string | null;
    /** Tombol di sisi kanan judul, misalnya "Lihat semua". */
    action?: ReactNode;
    children: ReactNode;
    className?: string;
    id?: string;
    /** Judul rata tengah, dipakai bagian yang isinya kartu selebar penuh. */
    centered?: boolean;
};

/**
 * Pembungkus satu bagian halaman publik.
 *
 * Menjaga ritme vertikal, lebar baca, dan tingkat heading tetap konsisten:
 * judul bagian selalu `h2`, jadi satu-satunya `h1` di halaman adalah judul
 * hero. Menyerahkan ini ke tiap halaman selalu berakhir dengan urutan heading
 * yang kacau.
 */
export default function Section({
    eyebrow,
    title,
    description,
    action,
    children,
    className,
    id,
    centered = false,
}: Props) {
    return (
        <section
            id={id}
            className={cn('mx-auto w-full max-w-7xl px-6 py-20', className)}
        >
            <BlurFade>
                <div
                    className={cn(
                        'flex flex-col gap-4',
                        centered
                            ? 'items-center text-center'
                            : 'sm:flex-row sm:items-end sm:justify-between',
                    )}
                >
                    <div className={cn('max-w-2xl', centered && 'mx-auto')}>
                        {/*
                         * Eyebrow memakai warna teks redup, bukan warna merek.
                         * Label bagian bukan tempat menarik perhatian — yang
                         * perlu menonjol adalah judulnya, dan warna merek
                         * disimpan untuk hal yang bisa ditekan.
                         */}
                        {eyebrow && (
                            <p className="text-sm font-medium text-muted-foreground">
                                {eyebrow}
                            </p>
                        )}
                        <h2 className="mt-2 font-display text-3xl leading-[1.1] font-bold tracking-[-0.03em] text-balance sm:text-4xl">
                            {title}
                        </h2>
                        {description && (
                            <p className="mt-3 text-base leading-relaxed text-muted-foreground">
                                {description}
                            </p>
                        )}
                    </div>

                    {action && <div className="shrink-0">{action}</div>}
                </div>
            </BlurFade>

            <div className="mt-12">{children}</div>
        </section>
    );
}
