import { Link } from '@inertiajs/react';
import type { InertiaLinkProps } from '@inertiajs/react';
import { ChevronRight } from 'lucide-react';
import type { ReactNode } from 'react';
import { toUrl } from '@/lib/utils';

type Crumb = { name: string; href: NonNullable<InertiaLinkProps['href']> };

type Props = {
    eyebrow?: string;
    title: string;
    description?: string | null;
    /**
     * Jejak halaman menuju halaman ini, tanpa halaman ini sendiri. Kosongkan
     * untuk halaman yang langsung berada di bawah beranda.
     */
    breadcrumbs?: Crumb[];
    /** Isi tambahan di bawah keterangan, misalnya deretan penyaring. */
    children?: ReactNode;
};

/**
 * Kepala halaman untuk seluruh halaman publik selain beranda.
 *
 * Beranda punya hero-nya sendiri; sisanya memakai kepala yang sama supaya
 * pengunjung selalu tahu di mana ia berada begitu berpindah antar halaman.
 * Judulnya `h1` — satu-satunya di halaman itu.
 */
export default function PageHeader({
    eyebrow,
    title,
    description,
    breadcrumbs = [],
    children,
}: Props) {
    return (
        <header className="border-b border-border/70 bg-muted/40">
            <div className="mx-auto w-full max-w-7xl px-6 py-12 lg:py-16">
                {breadcrumbs.length > 0 && (
                    <nav aria-label="Jejak halaman" className="mb-5">
                        <ol className="flex flex-wrap items-center gap-1 text-sm text-muted-foreground">
                            {breadcrumbs.map((crumb) => (
                                <li
                                    key={toUrl(crumb.href)}
                                    className="flex items-center gap-1"
                                >
                                    <Link
                                        href={crumb.href}
                                        className="underline-offset-4 transition-colors hover:text-primary hover:underline"
                                    >
                                        {crumb.name}
                                    </Link>
                                    <ChevronRight
                                        aria-hidden
                                        className="size-3.5 rtl:rotate-180"
                                    />
                                </li>
                            ))}

                            {/*
                             * Halaman yang sedang dibuka jadi ruas terakhir,
                             * diturunkan dari judulnya sendiri. Tanpa ini, tiap
                             * pemanggil harus mengulang judulnya di dua tempat —
                             * dan jejaknya berakhir dengan tanda panah yang
                             * tidak menunjuk ke mana-mana.
                             */}
                            <li aria-current="page" className="text-foreground">
                                {title}
                            </li>
                        </ol>
                    </nav>
                )}

                {eyebrow && (
                    <p className="text-sm font-medium text-muted-foreground">
                        {eyebrow}
                    </p>
                )}

                <h1 className="mt-2 max-w-3xl font-display text-[clamp(2rem,4vw,3rem)] leading-[1.05] font-bold tracking-[-0.03em] text-balance">
                    {title}
                </h1>

                {description && (
                    <p className="mt-4 max-w-2xl text-base leading-relaxed text-muted-foreground">
                        {description}
                    </p>
                )}

                {children && <div className="mt-8">{children}</div>}
            </div>
        </header>
    );
}
