import { Link } from '@inertiajs/react';
import { Download, MessageSquareQuote } from 'lucide-react';
import type { ReactNode } from 'react';
import ShapeDrawing from '@/components/public/home/shape-drawing';
import PageHeader from '@/components/public/page-header';
import ProductCard from '@/components/public/product-card';
import SeoHead from '@/components/public/seo-head';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import { containerCalculator, contact, home } from '@/routes/public';
import products from '@/routes/public/products';
import type {
    PublicContainerLoadRow,
    PublicProductCard,
    PublicProductDetail,
} from '@/types';

type Props = {
    product: PublicProductDetail;
    containerLoads: PublicContainerLoadRow[];
    related: PublicProductCard[];
};

const integer = new Intl.NumberFormat('id-ID');

const decimal = new Intl.NumberFormat('id-ID', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
});

const LIMIT_LABEL: Record<PublicContainerLoadRow['limited_by'], string> = {
    weight: 'Berat',
    volume: 'Ruang',
};

/**
 * Ukuran berkas dibaca manusia.
 *
 * Dihitung di sini, bukan di server, karena pemisah desimalnya mengikuti bahasa
 * pembaca — server tidak tahu locale mana yang sedang dipakai peramban.
 */
function fileSize(bytes: number | null): string | null {
    if (bytes === null) {
        return null;
    }

    if (bytes < 1024 * 1024) {
        return `${integer.format(Math.round(bytes / 1024))} KB`;
    }

    return `${decimal.format(bytes / (1024 * 1024))} MB`;
}

function SectionBlock({
    title,
    description,
    children,
}: {
    title: string;
    description?: string;
    children: ReactNode;
}) {
    return (
        <section className="border-t border-border/70 py-12">
            <h2 className="font-display text-2xl leading-tight font-bold tracking-[-0.02em]">
                {title}
            </h2>
            {description && (
                <p className="mt-2 max-w-2xl text-sm leading-relaxed text-muted-foreground">
                    {description}
                </p>
            )}
            <div className="mt-6">{children}</div>
        </section>
    );
}

/**
 * Pembungkus tabel.
 *
 * Tabel data selalu punya lebar minimum yang tidak bisa dikompres — memaksanya
 * muat di layar 375px berarti memotong angka. Digulir di dalam wadahnya
 * sendiri, jadi badan halaman tetap tidak pernah bergulir mendatar.
 */
function TableScroll({ children }: { children: ReactNode }) {
    return (
        <div className="rounded-panel overflow-x-auto border border-border/70 bg-card">
            {children}
        </div>
    );
}

function Th({
    children,
    numeric = false,
}: {
    children: ReactNode;
    numeric?: boolean;
}) {
    return (
        <th
            scope="col"
            className={cn(
                'px-5 py-3 text-xs font-medium whitespace-nowrap text-muted-foreground',
                numeric ? 'text-end' : 'text-start',
            )}
        >
            {children}
        </th>
    );
}

function Td({
    children,
    numeric = false,
    className,
}: {
    children: ReactNode;
    numeric?: boolean;
    className?: string;
}) {
    return (
        <td
            dir={numeric ? 'ltr' : undefined}
            className={cn(
                'px-5 py-3 text-sm whitespace-nowrap',
                numeric ? 'text-end font-mono tabular-nums' : 'text-start',
                className,
            )}
        >
            {children}
        </td>
    );
}

/** Satu keping ringkasan di sisi kanan gambar produk. */
function Fact({ label, value }: { label: string; value: string }) {
    return (
        <div className="rounded-panel border border-border/70 bg-card p-4">
            <dt className="text-xs text-muted-foreground">{label}</dt>
            <dd
                dir="ltr"
                className="mt-1 font-display text-lg font-semibold tabular-nums"
            >
                {value}
            </dd>
        </div>
    );
}

/**
 * Detail satu produk.
 *
 * Urutannya mengikuti urutan pertanyaan pembeli: bentuk dan ukurannya apa,
 * angka ujinya berapa, sekarton isinya berapa, sekontainer muat berapa, lalu
 * dokumen apa yang bisa diperiksa. Semuanya angka dari admin — tidak ada satu
 * pun nilai contoh di kode ini.
 */
export default function ProductShow({
    product,
    containerLoads,
    related,
}: Props) {
    const carton = product.master_carton;

    const facts = [
        { label: 'Kode produk', value: product.code },
        { label: 'Bentuk', value: product.shape },
        {
            label: 'Ukuran',
            value: product.size_mm === null ? null : `${product.size_mm} mm`,
        },
        {
            label: 'MOQ',
            value:
                product.moq_kg === null
                    ? null
                    : `${integer.format(product.moq_kg)} kg`,
        },
    ].filter(
        (fact): fact is { label: string; value: string } => fact.value !== null,
    );

    const cartonRows = [
        { label: 'Berat bersih', value: carton.net_weight_kg, unit: 'kg' },
        { label: 'Berat kemasan', value: carton.tare_weight_kg, unit: 'kg' },
        { label: 'Volume', value: carton.volume_cbm, unit: 'CBM' },
        { label: 'Panjang', value: carton.length_cm, unit: 'cm' },
        { label: 'Lebar', value: carton.width_cm, unit: 'cm' },
        { label: 'Tinggi', value: carton.height_cm, unit: 'cm' },
        {
            label: 'Berat inner box',
            value: carton.inner_box_weight_kg,
            unit: 'kg',
        },
        {
            label: 'Inner box per karton',
            value: carton.inner_boxes_per_carton,
            unit: 'pcs',
        },
    ].filter(
        (row): row is { label: string; value: number; unit: string } =>
            row.value !== null,
    );

    return (
        <>
            <SeoHead
                title={product.name ?? product.code}
                description={product.short_description}
            />

            <PageHeader
                eyebrow={product.category ?? undefined}
                title={product.name ?? product.code}
                description={product.short_description}
                breadcrumbs={[
                    { name: 'Beranda', href: home() },
                    { name: 'Produk', href: products.index() },
                ]}
            />

            <div className="mx-auto w-full max-w-7xl px-6">
                <div className="grid gap-10 py-12 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)] lg:gap-14">
                    <div className="rounded-panel overflow-hidden border border-border/70 bg-muted">
                        {product.cover_url ? (
                            <img
                                src={product.cover_url}
                                alt={product.name ?? product.code}
                                fetchPriority="high"
                                decoding="async"
                                className="aspect-4/3 w-full object-cover"
                            />
                        ) : (
                            /*
                             * Tanpa foto, gambar tekniknya yang tampil. Bidang
                             * abu-abu kosong tidak memberi tahu apa pun,
                             * sedangkan gambar berukuran menjawab pertanyaan
                             * pertama pembeli.
                             */
                            <div className="flex aspect-4/3 w-full items-center justify-center p-10">
                                <ShapeDrawing
                                    shape={product.shape}
                                    sizeMm={product.size_mm}
                                    className="max-w-[16rem] text-foreground"
                                />
                            </div>
                        )}
                    </div>

                    <div>
                        {facts.length > 0 && (
                            <dl className="grid grid-cols-2 gap-3">
                                {facts.map((fact) => (
                                    <Fact
                                        key={fact.label}
                                        label={fact.label}
                                        value={fact.value}
                                    />
                                ))}
                            </dl>
                        )}

                        {product.description && (
                            <p className="mt-6 text-base leading-relaxed text-muted-foreground">
                                {product.description}
                            </p>
                        )}

                        <div className="mt-8 flex flex-wrap items-center gap-x-7 gap-y-4">
                            <Button asChild variant="create" size="lg">
                                <Link href={contact()}>
                                    <MessageSquareQuote />
                                    Minta penawaran
                                </Link>
                            </Button>

                            {product.is_calculable && (
                                <Link
                                    href={containerCalculator()}
                                    className="text-sm font-medium underline-offset-8 transition-colors hover:text-primary hover:underline"
                                >
                                    Hitung muatan kontainer
                                </Link>
                            )}
                        </div>

                        {product.packaging_options.length > 0 && (
                            <div className="mt-8">
                                <h2 className="text-sm font-semibold tracking-tight">
                                    Kemasan tersedia
                                </h2>
                                <ul className="mt-3 flex flex-wrap gap-2">
                                    {product.packaging_options.map((option) => (
                                        <li
                                            key={option.id}
                                            className="rounded-full border border-border/70 bg-card px-3.5 py-1.5 text-sm text-muted-foreground"
                                        >
                                            {option.name}
                                            {option.net_weight_kg !== null && (
                                                <span
                                                    dir="ltr"
                                                    className="ms-1.5 font-mono text-xs tabular-nums"
                                                >
                                                    {option.net_weight_kg} kg
                                                </span>
                                            )}
                                        </li>
                                    ))}
                                </ul>
                            </div>
                        )}
                    </div>
                </div>

                {product.specifications.length > 0 && (
                    <SectionBlock
                        title="Spesifikasi"
                        description="Angka hasil uji batch. Nilai bertanda adalah yang paling sering dijadikan penyaring oleh pembeli."
                    >
                        <TableScroll>
                            <table className="w-full">
                                <thead className="border-b border-border/70">
                                    <tr>
                                        <Th>Parameter</Th>
                                        <Th numeric>Nilai</Th>
                                        <Th>Satuan</Th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-border/70">
                                    {product.specifications.map((spec) => (
                                        <tr key={spec.parameter}>
                                            <Td
                                                className={
                                                    spec.is_highlighted
                                                        ? 'font-medium'
                                                        : undefined
                                                }
                                            >
                                                {spec.parameter}
                                            </Td>
                                            <Td
                                                numeric
                                                className={
                                                    spec.is_highlighted
                                                        ? 'font-semibold text-primary'
                                                        : undefined
                                                }
                                            >
                                                {spec.value}
                                            </Td>
                                            <Td className="text-muted-foreground">
                                                {spec.unit ?? '—'}
                                            </Td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </TableScroll>
                    </SectionBlock>
                )}

                {cartonRows.length > 0 && (
                    <SectionBlock
                        title="Master carton"
                        description="Angka inilah yang dipakai kalkulator kontainer. Anda bisa memeriksa ulang hitungannya sendiri."
                    >
                        <dl className="grid grid-cols-2 gap-3 sm:grid-cols-4">
                            {cartonRows.map((row) => (
                                <div
                                    key={row.label}
                                    className="rounded-panel border border-border/70 bg-card p-4"
                                >
                                    <dt className="text-xs text-muted-foreground">
                                        {row.label}
                                    </dt>
                                    <dd
                                        dir="ltr"
                                        className="mt-1 font-display text-lg font-semibold tabular-nums"
                                    >
                                        {row.value}
                                        <span className="ms-1 text-xs font-normal text-muted-foreground">
                                            {row.unit}
                                        </span>
                                    </dd>
                                </div>
                            ))}
                        </dl>
                    </SectionBlock>
                )}

                {containerLoads.length > 0 && (
                    <SectionBlock
                        title="Muatan per kontainer"
                        description="Dihitung dari master carton di atas terhadap tiap jenis kontainer. Kolom terakhir menyebut batas mana yang habis lebih dulu — itu yang menentukan apakah menambah pesanan masih ada gunanya."
                    >
                        <TableScroll>
                            <table className="w-full">
                                <thead className="border-b border-border/70">
                                    <tr>
                                        <Th>Kontainer</Th>
                                        <Th numeric>Karton</Th>
                                        <Th numeric>Berat bersih</Th>
                                        <Th numeric>Volume</Th>
                                        <Th numeric>Terpakai</Th>
                                        <Th>Batas</Th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-border/70">
                                    {containerLoads.map((load) => (
                                        <tr key={load.container_id}>
                                            <Td className="font-medium">
                                                {load.name}
                                            </Td>
                                            <Td numeric>
                                                {integer.format(load.cartons)}
                                            </Td>
                                            <Td numeric>
                                                {integer.format(
                                                    load.net_weight_kg,
                                                )}{' '}
                                                kg
                                            </Td>
                                            <Td numeric>
                                                {decimal.format(
                                                    load.volume_cbm,
                                                )}{' '}
                                                CBM
                                            </Td>
                                            {/*
                                             * Dibulatkan ke bawah. Muatan 99,98%
                                             * yang ditulis "100%" membuat pembeli
                                             * mengira tidak ada ruang tersisa
                                             * sama sekali.
                                             */}
                                            <Td numeric>
                                                {Math.floor(
                                                    load.utilisation * 100,
                                                )}
                                                %
                                            </Td>
                                            <Td className="text-muted-foreground">
                                                {LIMIT_LABEL[load.limited_by]}
                                            </Td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </TableScroll>
                    </SectionBlock>
                )}

                {product.documents.length > 0 && (
                    <SectionBlock
                        title="Dokumen"
                        description="Bisa diunduh dan diperiksa sebelum Anda membayar."
                    >
                        <ul className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                            {product.documents.map((document) => (
                                <li key={document.id}>
                                    <a
                                        href={document.url}
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        className="rounded-panel flex items-center gap-3 border border-border/70 bg-card p-4 transition-colors hover:border-foreground/20 focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
                                    >
                                        <Download className="size-4 shrink-0 text-primary" />
                                        <span className="min-w-0 flex-1">
                                            <span className="block truncate text-sm font-medium">
                                                {document.title ??
                                                    document.type}
                                            </span>
                                            {fileSize(document.file_size) && (
                                                <span
                                                    dir="ltr"
                                                    className="mt-0.5 block font-mono text-xs text-muted-foreground tabular-nums"
                                                >
                                                    {fileSize(
                                                        document.file_size,
                                                    )}
                                                </span>
                                            )}
                                        </span>
                                    </a>
                                </li>
                            ))}
                        </ul>
                    </SectionBlock>
                )}

                {related.length > 0 && (
                    <SectionBlock title="Produk serupa">
                        <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            {related.map((item) => (
                                <ProductCard key={item.id} product={item} />
                            ))}
                        </div>
                    </SectionBlock>
                )}
            </div>
        </>
    );
}
