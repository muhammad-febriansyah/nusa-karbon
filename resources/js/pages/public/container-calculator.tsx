import { Link } from '@inertiajs/react';
import { MessageSquareQuote } from 'lucide-react';
import { useMemo, useState } from 'react';
import type { ReactNode } from 'react';
import Combobox from '@/components/public/combobox';
import PageHeader from '@/components/public/page-header';
import SeoHead from '@/components/public/seo-head';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { calculateContainerLoad } from '@/lib/container-calculator';
import { cn } from '@/lib/utils';
import { contact, home } from '@/routes/public';
import products from '@/routes/public/products';

type CalculatorProduct = {
    id: number;
    code: string;
    name: string;
    slug: string | null;
    size_mm: string | null;
    shape: string | null;
    master_carton_weight_kg: number;
    master_carton_cbm: number;
    master_carton_tare_kg: number | null;
    moq_kg: number | null;
};

type CalculatorContainer = {
    id: number;
    code: string;
    name: string;
    max_payload_kg: number;
    max_cbm: number;
    internal_length_m: number | null;
    internal_width_m: number | null;
};

type Props = {
    products: CalculatorProduct[];
    containers: CalculatorContainer[];
};

/*
 * Tanpa pecahan sama sekali. Berat kotor hasil penjumlahan tara sering
 * berujung pada angka seperti 17.585,4 kg, dan satu desimal pada bilangan lima
 * digit hanya menambah bising — pelayaran pun membulatkannya.
 */
const integer = new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 });

const decimal = new Intl.NumberFormat('id-ID', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
});

/**
 * Batas yang habis lebih dulu, ditulis sebagai kalimat.
 *
 * Ini pertanyaan yang sebenarnya sedang dijawab kalkulator: bukan sekadar
 * "berapa muat", melainkan "kalau ditambah, masih masuk atau tidak".
 */
const LIMIT_SENTENCE: Record<'weight' | 'volume', string> = {
    weight: 'Batas berat muat tercapai lebih dulu — ruangnya masih tersisa.',
    volume: 'Ruang habis lebih dulu — batas berat muat belum tercapai.',
};

function SpecRow({ label, value }: { label: string; value: string }) {
    return (
        <div className="flex items-baseline justify-between gap-4">
            <dt className="text-sm text-muted-foreground">{label}</dt>
            <dd dir="ltr" className="font-mono text-sm tabular-nums">
                {value}
            </dd>
        </div>
    );
}

/**
 * Satu batas kontainer beserta seberapa jauh terpakai.
 *
 * Menggantikan denah muat yang sebelumnya mengisi ruang paling besar di
 * halaman ini. Denah itu diarsir sepanjang persentase muatan, padahal muatan
 * tidak bertambah memanjang di lantai — dan pada 99% yang terlihat hanya
 * kotak berarsir penuh tanpa satu pun angka. Dua batang ini menjawab
 * pertanyaan yang sebenarnya: mana yang habis lebih dulu, dan berapa sisa yang
 * satunya.
 */
function ConstraintBar({
    label,
    used,
    limit,
    unit,
    format,
    binding,
}: {
    label: string;
    used: number;
    limit: number;
    unit: string;
    format: (value: number) => string;
    /** Batas inilah yang menghentikan muatan. */
    binding: boolean;
}) {
    const ratio = limit <= 0 ? 0 : Math.min(used / limit, 1);

    /*
     * Dibulatkan ke bawah, bukan ke terdekat. Muatan 99,98% yang ditampilkan
     * sebagai "100%" membuat pembeli mengira kontainernya persis penuh dan
     * tidak ada ruang tersisa untuk sample atau dokumen.
     */
    const percent = Math.floor(ratio * 100);
    const remaining = Math.max(limit - used, 0);

    return (
        <div>
            <div className="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1">
                <span className="flex items-center gap-2 text-sm font-medium">
                    {label}
                    {binding && (
                        <span className="rounded-full bg-primary/10 px-2 py-0.5 text-xs font-medium text-primary">
                            batas
                        </span>
                    )}
                </span>

                <span dir="ltr" className="font-mono text-sm tabular-nums">
                    {format(used)} / {format(limit)} {unit}
                </span>
            </div>

            {/*
             * Batangnya `aria-hidden`: angka yang diwakilinya sudah tertulis
             * lengkap di atas dan di bawahnya, jadi membacakannya lagi hanya
             * menambah bising di pembaca layar.
             */}
            <div
                aria-hidden
                className="mt-2 h-2.5 w-full overflow-hidden rounded-full bg-muted"
            >
                <div
                    className={cn(
                        'h-full rounded-full transition-[width] duration-500 ease-out',
                        binding ? 'bg-primary' : 'bg-foreground/25',
                    )}
                    style={{ width: `${percent}%` }}
                />
            </div>

            <p className="mt-1.5 text-xs text-muted-foreground">
                <span dir="ltr" className="tabular-nums">
                    {percent}%
                </span>{' '}
                terpakai · sisa{' '}
                <span dir="ltr" className="tabular-nums">
                    {format(remaining)} {unit}
                </span>
            </p>
        </div>
    );
}

function Field({
    label,
    htmlFor,
    children,
}: {
    label: string;
    htmlFor: string;
    children: ReactNode;
}) {
    return (
        <div className="space-y-2">
            <Label htmlFor={htmlFor} className="text-sm font-medium">
                {label}
            </Label>
            {children}
        </div>
    );
}

/**
 * Kalkulator muatan kontainer.
 *
 * Hitungannya berjalan di peramban lewat `lib/container-calculator.ts` — cermin
 * dari `ContainerCalculatorService` di PHP, dengan paritas yang dijaga tes.
 * Pengunjung mengganti pilihan berkali-kali saat menyusun pesanan, dan menunggu
 * server di tiap pergantian membuat alat ini terasa lamban.
 */
export default function ContainerCalculator({
    products: items,
    containers,
}: Props) {
    const [productId, setProductId] = useState<string>(
        items[0] === undefined ? '' : String(items[0].id),
    );
    const [containerId, setContainerId] = useState<string>(
        containers[0] === undefined ? '' : String(containers[0].id),
    );

    const product = items.find((item) => String(item.id) === productId);
    const container = containers.find(
        (item) => String(item.id) === containerId,
    );

    const load = useMemo(() => {
        if (product === undefined || container === undefined) {
            return null;
        }

        return calculateContainerLoad(product, container);
    }, [product, container]);

    const meetsMoq =
        product?.moq_kg == null || load === null
            ? null
            : load.net_weight_kg >= product.moq_kg;

    return (
        <>
            <SeoHead
                title="Kalkulator Kontainer"
                description="Hitung berapa karton briket arang batok kelapa yang muat dalam satu kontainer, lengkap dengan berat bersih, volume, dan batas mana yang habis lebih dulu."
            />

            <PageHeader
                eyebrow="Alat"
                title="Kalkulator muatan kontainer"
                breadcrumbs={[{ name: 'Beranda', href: home() }]}
                description="Pilih produk dan jenis kontainer, lalu lihat berapa karton yang muat. Angkanya dihitung dari data master carton yang sama dengan yang tertera di tiap halaman produk."
            />

            <div className="mx-auto w-full max-w-7xl px-6 py-12">
                {items.length === 0 || containers.length === 0 ? (
                    <p className="text-base text-muted-foreground">
                        Kalkulator belum bisa dipakai karena data master carton
                        produk atau daftar kontainer belum lengkap.
                    </p>
                ) : (
                    <div className="grid gap-10 lg:grid-cols-[minmax(0,20rem)_minmax(0,1fr)] lg:gap-14">
                        <form
                            className="space-y-5"
                            /*
                             * Tidak ada tombol hitung dan tidak ada pengiriman
                             * form. Hasilnya sudah berubah begitu pilihan
                             * diganti; tombol yang tidak menambah apa pun justru
                             * membuat pengunjung mengira hasilnya belum final.
                             */
                            onSubmit={(event) => event.preventDefault()}
                        >
                            <Field label="Produk" htmlFor="produk">
                                {/*
                                 * Bisa dicari: daftarnya sepanjang katalog, dan
                                 * pembeli datang membawa satu ukuran di kepala
                                 * ketimbang menelusuri tiga puluh baris.
                                 */}
                                <Combobox
                                    id="produk"
                                    name="produk"
                                    defaultValue={productId}
                                    onValueChange={setProductId}
                                    className="h-11"
                                    placeholder="Pilih produk"
                                    searchPlaceholder="Cari produk…"
                                    emptyText="Produk tidak ditemukan."
                                    options={items.map((item) => ({
                                        value: String(item.id),
                                        label: item.name,
                                        hint: item.size_mm ?? undefined,
                                    }))}
                                />
                            </Field>

                            <Field label="Jenis kontainer" htmlFor="kontainer">
                                <Combobox
                                    id="kontainer"
                                    name="kontainer"
                                    defaultValue={containerId}
                                    onValueChange={setContainerId}
                                    className="h-11"
                                    placeholder="Pilih kontainer"
                                    searchPlaceholder="Cari kontainer…"
                                    emptyText="Kontainer tidak ditemukan."
                                    options={containers.map((item) => ({
                                        value: String(item.id),
                                        label: item.name,
                                        hint: item.code,
                                    }))}
                                />
                            </Field>

                            {product && (
                                <dl className="rounded-panel space-y-3 border border-border/70 bg-card p-5">
                                    <p className="text-sm font-medium">
                                        Master carton {product.name}
                                    </p>

                                    <SpecRow
                                        label="Berat bersih"
                                        value={`${decimal.format(product.master_carton_weight_kg)} kg`}
                                    />
                                    <SpecRow
                                        label="Volume"
                                        value={`${product.master_carton_cbm} CBM`}
                                    />

                                    {product.slug && (
                                        <Link
                                            href={products.show(product.slug)}
                                            className="inline-block text-sm font-medium text-primary underline-offset-4 hover:underline"
                                        >
                                            Lihat spesifikasi lengkap
                                        </Link>
                                    )}
                                </dl>
                            )}

                            {/*
                             * Batas kontainernya ikut ditulis. Tanpa ini,
                             * kalimat "batas berat muat belum tercapai" tidak
                             * bisa diperiksa sendiri oleh pembacanya — ia tahu
                             * beratnya 16.590 kg tetapi tidak tahu terhadap apa.
                             */}
                            {container && (
                                <dl className="rounded-panel space-y-3 border border-border/70 bg-card p-5">
                                    <p className="text-sm font-medium">
                                        Kapasitas {container.name}
                                    </p>

                                    <SpecRow
                                        label="Batas berat"
                                        value={`${integer.format(container.max_payload_kg)} kg`}
                                    />
                                    <SpecRow
                                        label="Batas volume"
                                        value={`${decimal.format(container.max_cbm)} CBM`}
                                    />

                                    {container.internal_length_m !== null &&
                                        container.internal_width_m !== null && (
                                            <SpecRow
                                                label="Dimensi dalam"
                                                value={`${decimal.format(container.internal_length_m)} × ${decimal.format(container.internal_width_m)} m`}
                                            />
                                        )}
                                </dl>
                            )}
                        </form>

                        <div>
                            {load !== null && container !== undefined && (
                                <>
                                    <div className="rounded-panel border border-border/70 bg-card p-6 sm:p-8">
                                        <p className="text-sm text-muted-foreground">
                                            Muat dalam satu {container.name}
                                        </p>

                                        <p
                                            dir="ltr"
                                            className="mt-2 font-display text-[clamp(2.5rem,6vw,4rem)] leading-none font-semibold tracking-[-0.03em] text-primary tabular-nums"
                                        >
                                            {integer.format(load.cartons)}
                                            <span className="ms-2.5 font-sans text-base font-normal text-muted-foreground">
                                                karton
                                            </span>
                                        </p>

                                        {/*
                                         * Berat kotor ikut ditulis. Itu angka
                                         * yang dipakai pelayaran dan batas jalan
                                         * di pelabuhan tujuan; berat bersih saja
                                         * membuat pembeli menghitung ulang
                                         * sendiri dengan tara yang ia tebak.
                                         */}
                                        <p className="mt-3 text-sm text-muted-foreground">
                                            <span
                                                dir="ltr"
                                                className="font-mono tabular-nums text-foreground"
                                            >
                                                {integer.format(
                                                    load.net_weight_kg,
                                                )}{' '}
                                                kg
                                            </span>{' '}
                                            bersih ·{' '}
                                            <span
                                                dir="ltr"
                                                className="font-mono tabular-nums text-foreground"
                                            >
                                                {integer.format(
                                                    load.gross_weight_kg,
                                                )}{' '}
                                                kg
                                            </span>{' '}
                                            kotor
                                        </p>

                                        <div className="mt-8 space-y-5">
                                            <ConstraintBar
                                                label="Volume"
                                                used={load.volume_cbm}
                                                limit={container.max_cbm}
                                                unit="CBM"
                                                format={(value) =>
                                                    decimal.format(value)
                                                }
                                                binding={
                                                    load.limited_by === 'volume'
                                                }
                                            />
                                            <ConstraintBar
                                                label="Berat"
                                                used={load.net_weight_kg}
                                                limit={container.max_payload_kg}
                                                unit="kg"
                                                format={(value) =>
                                                    integer.format(value)
                                                }
                                                binding={
                                                    load.limited_by === 'weight'
                                                }
                                            />
                                        </div>

                                        <p className="mt-6 text-sm leading-relaxed text-muted-foreground">
                                            {LIMIT_SENTENCE[load.limited_by]}
                                        </p>
                                    </div>

                                    {meetsMoq === false &&
                                        product?.moq_kg != null && (
                                            <p className="rounded-panel mt-6 border border-warning/40 bg-warning/10 p-4 text-sm leading-relaxed">
                                                Satu {container.name} memuat{' '}
                                                <span
                                                    dir="ltr"
                                                    className="font-mono tabular-nums"
                                                >
                                                    {integer.format(
                                                        load.net_weight_kg,
                                                    )}{' '}
                                                    kg
                                                </span>
                                                , masih di bawah MOQ produk ini
                                                yang{' '}
                                                <span
                                                    dir="ltr"
                                                    className="font-mono tabular-nums"
                                                >
                                                    {integer.format(
                                                        product.moq_kg,
                                                    )}{' '}
                                                    kg
                                                </span>
                                                . Pilih kontainer yang lebih
                                                besar, atau kirim permintaan
                                                penawaran untuk membahas
                                                pengecualian.
                                            </p>
                                        )}

                                    <div className="mt-8">
                                        <Button
                                            asChild
                                            variant="create"
                                            size="lg"
                                        >
                                            <Link href={contact()}>
                                                <MessageSquareQuote />
                                                Minta penawaran untuk muatan ini
                                            </Link>
                                        </Button>
                                    </div>

                                    <p className="mt-6 max-w-2xl text-sm leading-relaxed text-muted-foreground">
                                        Perkiraan ini memakai kapasitas berat
                                        dan volume kontainer. Susunan pallet,
                                        tinggi tumpukan, dan aturan pelayaran
                                        bisa mengubah angka akhirnya — pastikan
                                        ulang di penawaran resmi.
                                    </p>
                                </>
                            )}
                        </div>
                    </div>
                )}
            </div>
        </>
    );
}
