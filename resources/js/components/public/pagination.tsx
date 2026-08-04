import { Link } from '@inertiajs/react';
import type { InertiaLinkProps } from '@inertiajs/react';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import type { ReactNode } from 'react';
import { cn } from '@/lib/utils';
import type { PaginatedMeta } from '@/types/pagination';

type Props = {
    meta: PaginatedMeta;
    /** Menyusun tautan ke satu nomor halaman, lengkap dengan saringan aktif. */
    hrefFor: (page: number) => NonNullable<InertiaLinkProps['href']>;
    /** Nama satuan isinya, dipakai label tombol: "Halaman produk berikutnya". */
    unit: string;
};

/**
 * Jumlah nomor yang ditampilkan sebelum dipersingkat dengan elipsis.
 *
 * Tujuh muat pada satu baris di layar 375px bersama kedua tombol panah. Lebih
 * dari itu, nomornya melipat dan kehilangan gunanya sebagai penanda posisi.
 */
const MAX_VISIBLE = 7;

/**
 * Nomor halaman yang ditampilkan, dengan elipsis bila terlalu banyak.
 *
 * Halaman pertama dan terakhir selalu ikut supaya pengunjung bisa melompat ke
 * ujung mana pun dalam satu tekanan — itu dua tujuan paling sering pada daftar
 * panjang.
 */
function pageNumbers(current: number, last: number): (number | 'gap')[] {
    if (last <= MAX_VISIBLE) {
        return Array.from({ length: last }, (_, index) => index + 1);
    }

    const pages = new Set<number>([1, last, current]);

    for (const offset of [-1, 1]) {
        const page = current + offset;

        if (page > 1 && page < last) {
            pages.add(page);
        }
    }

    const sorted = [...pages].sort((a, b) => a - b);
    const result: (number | 'gap')[] = [];

    sorted.forEach((page, index) => {
        const previous = sorted[index - 1];

        if (previous !== undefined && page - previous > 1) {
            result.push('gap');
        }

        result.push(page);
    });

    return result;
}

function Cell({
    children,
    className,
}: {
    children: ReactNode;
    className?: string;
}) {
    return (
        <span
            className={cn(
                'flex h-10 min-w-10 items-center justify-center rounded-full px-3 text-sm',
                className,
            )}
        >
            {children}
        </span>
    );
}

/**
 * Penomoran halaman untuk daftar publik.
 *
 * Nomornya ditampilkan, bukan hanya panah maju-mundur: pada daftar yang isinya
 * dijelajahi, pengunjung perlu tahu ada berapa halaman dan sedang di mana —
 * dan bisa kembali ke tempat yang sama lewat tautan yang dibagikan.
 */
export default function Pagination({ meta, hrefFor, unit }: Props) {
    if (meta.last_page <= 1) {
        return null;
    }

    const numbers = pageNumbers(meta.current_page, meta.last_page);
    const atStart = meta.current_page === 1;
    const atEnd = meta.current_page === meta.last_page;

    const arrow =
        'flex size-10 items-center justify-center rounded-full border border-border/70 transition-colors';
    const number =
        'transition-colors hover:bg-muted focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none';

    return (
        <nav
            aria-label="Penomoran halaman"
            className="mt-12 flex flex-wrap items-center justify-center gap-2"
        >
            {atStart ? (
                <span aria-hidden className={cn(arrow, 'opacity-40')}>
                    <ChevronLeft className="size-4 rtl:rotate-180" />
                </span>
            ) : (
                <Link
                    href={hrefFor(meta.current_page - 1)}
                    preserveScroll
                    aria-label={`${unit} sebelumnya`}
                    className={cn(
                        arrow,
                        'hover:border-foreground/20 hover:text-primary',
                        'focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none',
                    )}
                >
                    <ChevronLeft className="size-4 rtl:rotate-180" />
                </Link>
            )}

            {numbers.map((page, index) =>
                page === 'gap' ? (
                    <Cell
                        // Elipsis tidak pernah berpindah posisi relatif dalam
                        // satu daftar, jadi indeks aman sebagai kunci di sini.
                        key={`gap-${index}`}
                        className="text-muted-foreground"
                    >
                        <span aria-hidden>…</span>
                    </Cell>
                ) : page === meta.current_page ? (
                    <Cell
                        key={page}
                        className="bg-primary font-semibold text-primary-foreground tabular-nums"
                    >
                        <span aria-current="page">{page}</span>
                    </Cell>
                ) : (
                    <Link
                        key={page}
                        href={hrefFor(page)}
                        preserveScroll
                        aria-label={`Halaman ${page}`}
                        className={cn(
                            'flex h-10 min-w-10 items-center justify-center rounded-full px-3 text-sm tabular-nums',
                            number,
                        )}
                    >
                        {page}
                    </Link>
                ),
            )}

            {atEnd ? (
                <span aria-hidden className={cn(arrow, 'opacity-40')}>
                    <ChevronRight className="size-4 rtl:rotate-180" />
                </span>
            ) : (
                <Link
                    href={hrefFor(meta.current_page + 1)}
                    preserveScroll
                    aria-label={`${unit} berikutnya`}
                    className={cn(
                        arrow,
                        'hover:border-foreground/20 hover:text-primary',
                        'focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none',
                    )}
                >
                    <ChevronRight className="size-4 rtl:rotate-180" />
                </Link>
            )}
        </nav>
    );
}
