import { Link } from '@inertiajs/react';
import BrandMark from '@/components/brand-mark';
import { home } from '@/routes/public';
import type { AuthLayoutProps } from '@/types';

const highlights = [
    { value: '18+', label: 'Negara Tujuan' },
    { value: '3', label: 'Bahasa' },
    { value: '24 Jam', label: 'Respons RFQ' },
];

export default function AuthBrandLayout({
    children,
    title,
    description,
}: AuthLayoutProps) {
    return (
        <div className="grid min-h-svh lg:grid-cols-2">
            <aside className="surface-brand relative hidden flex-col justify-between overflow-hidden p-11 lg:flex">
                <div
                    aria-hidden
                    className="surface-brand-glow pointer-events-none absolute -end-40 -bottom-50 size-[620px] rounded-full"
                />
                <div
                    aria-hidden
                    className="surface-brand-mesh pointer-events-none absolute inset-0 opacity-7"
                />

                <Link href={home()} className="relative">
                    <BrandMark
                        imageClassName="h-9.5"
                        labelClassName="text-xl font-bold tracking-tight text-background"
                        accentClassName="text-primary"
                    />
                </Link>

                <div className="relative max-w-[470px]">
                    <h1 className="text-[42px] leading-tight font-semibold tracking-tight text-balance text-background">
                        Kelola ekspor briket
                        <br />
                        Anda dari satu tempat.
                    </h1>
                    <p className="mt-5 text-[14.5px] leading-relaxed font-light text-pretty text-background/60">
                        Produk, spesifikasi teknis, dokumen ekspor, dan
                        permintaan penawaran buyer — semuanya dalam satu panel.
                    </p>
                </div>

                <dl className="relative flex items-center gap-6.5">
                    {highlights.map((highlight, index) => (
                        <div
                            key={highlight.label}
                            className="flex items-center"
                        >
                            {index > 0 && (
                                <span
                                    aria-hidden
                                    className="me-6.5 h-8.5 w-px bg-background/15"
                                />
                            )}
                            <div className="flex flex-col gap-0.5">
                                <dt className="text-[22px] font-semibold text-accent tabular-nums">
                                    <span dir="ltr">{highlight.value}</span>
                                </dt>
                                <dd className="text-xs text-background/50">
                                    {highlight.label}
                                </dd>
                            </div>
                        </div>
                    ))}
                </dl>
            </aside>

            <main className="flex flex-col justify-center gap-6 bg-background px-6 py-11 sm:px-16">
                <div className="flex flex-col items-center gap-3.5">
                    <Link href={home()}>
                        <img
                            src="/logo-nusacarbon-full.png"
                            alt="Nusacarbon"
                            className="block h-19.5 w-auto"
                        />
                    </Link>

                    <div className="flex flex-col items-center gap-1.5 text-center">
                        <h1 className="text-2xl font-semibold tracking-tight">
                            {title}
                        </h1>
                        {description && (
                            <p className="text-sm text-muted-foreground">
                                {description}
                            </p>
                        )}
                    </div>
                </div>

                <div className="mx-auto flex w-full max-w-md flex-col gap-5">
                    {children}
                </div>

                <div className="flex flex-col items-center gap-5 text-xs text-muted-foreground">
                    <p>Butuh akses? Hubungi administrator sistem.</p>
                    <p>© {new Date().getFullYear()} PT Nusa Carbon Indonesia</p>
                </div>
            </main>
        </div>
    );
}
