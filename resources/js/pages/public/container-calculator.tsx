import { Link } from '@inertiajs/react';
import { MessageSquareQuote } from 'lucide-react';
import { useMemo, useState } from 'react';
import type { ReactNode } from 'react';
import PageHeader from '@/components/public/page-header';
import SeoHead from '@/components/public/seo-head';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
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

const integer = new Intl.NumberFormat('id-ID');

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

function Figure({
    label,
    value,
    unit,
    accent = false,
}: {
    label: string;
    value: string;
    unit?: string;
    accent?: boolean;
}) {
    return (
        <div className="rounded-panel border border-border/70 bg-card p-5">
            <dt className="text-sm text-muted-foreground">{label}</dt>
            <dd
                dir="ltr"
                className={cn(
                    'mt-2 font-display text-[clamp(1.75rem,3vw,2.5rem)] leading-none font-semibold tracking-[-0.02em] tabular-nums',
                    accent && 'text-primary',
                )}
            >
                {value}
                {unit && (
                    <span className="ms-1.5 text-sm font-normal text-muted-foreground">
                        {unit}
                    </span>
                )}
            </dd>
        </div>
    );
}

function Field({ label, children }: { label: string; children: ReactNode }) {
    return (
        <div className="space-y-2">
            <Label className="text-sm font-medium">{label}</Label>
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

    /*
     * Dibulatkan ke bawah, bukan ke terdekat. Muatan 99,98% yang ditampilkan
     * sebagai "100%" membuat pembeli mengira kontainernya persis penuh dan tidak
     * ada ruang tersisa untuk sample atau dokumen.
     */
    const percent = load === null ? 0 : Math.floor(load.utilisation * 100);

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
                            <Field label="Produk">
                                <Select
                                    value={productId}
                                    onValueChange={setProductId}
                                >
                                    <SelectTrigger className="w-full">
                                        <SelectValue placeholder="Pilih produk" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {items.map((item) => (
                                            <SelectItem
                                                key={item.id}
                                                value={String(item.id)}
                                            >
                                                {item.name}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </Field>

                            <Field label="Jenis kontainer">
                                <Select
                                    value={containerId}
                                    onValueChange={setContainerId}
                                >
                                    <SelectTrigger className="w-full">
                                        <SelectValue placeholder="Pilih kontainer" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {containers.map((item) => (
                                            <SelectItem
                                                key={item.id}
                                                value={String(item.id)}
                                            >
                                                {item.name}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </Field>

                            {product && (
                                <dl className="rounded-panel space-y-3 border border-border/70 bg-card p-5">
                                    <p className="text-sm font-medium">
                                        Master carton {product.name}
                                    </p>

                                    <div className="flex items-baseline justify-between gap-4">
                                        <dt className="text-sm text-muted-foreground">
                                            Berat bersih
                                        </dt>
                                        <dd
                                            dir="ltr"
                                            className="font-mono text-sm tabular-nums"
                                        >
                                            {decimal.format(
                                                product.master_carton_weight_kg,
                                            )}{' '}
                                            kg
                                        </dd>
                                    </div>

                                    <div className="flex items-baseline justify-between gap-4">
                                        <dt className="text-sm text-muted-foreground">
                                            Volume
                                        </dt>
                                        <dd
                                            dir="ltr"
                                            className="font-mono text-sm tabular-nums"
                                        >
                                            {product.master_carton_cbm} CBM
                                        </dd>
                                    </div>

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
                        </form>

                        <div>
                            {load !== null && container !== undefined && (
                                <>
                                    <dl className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                                        <Figure
                                            label="Karton"
                                            value={integer.format(load.cartons)}
                                            accent
                                        />
                                        <Figure
                                            label="Berat bersih"
                                            value={integer.format(
                                                load.net_weight_kg,
                                            )}
                                            unit="kg"
                                        />
                                        <Figure
                                            label="Volume"
                                            value={decimal.format(
                                                load.volume_cbm,
                                            )}
                                            unit={`/ ${decimal.format(container.max_cbm)}`}
                                        />
                                        <Figure
                                            label="Terpakai"
                                            value={String(percent)}
                                            unit="%"
                                        />
                                    </dl>

                                    <p className="mt-5 text-base leading-relaxed text-muted-foreground">
                                        {LIMIT_SENTENCE[load.limited_by]}
                                    </p>

                                    {/*
                                     * Denah muat: rasionya mengikuti panjang dan
                                     * lebar dalam yang sebenarnya, jadi 20FT dan
                                     * 40FT memang terlihat berbeda panjang.
                                     */}
                                    <div
                                        className="rounded-panel relative mt-8 w-full overflow-hidden border border-border/70 bg-card"
                                        style={{
                                            aspectRatio:
                                                container.internal_length_m ===
                                                    null ||
                                                container.internal_width_m ===
                                                    null
                                                    ? '5.9 / 2.35'
                                                    : `${container.internal_length_m} / ${container.internal_width_m}`,
                                        }}
                                    >
                                        <div
                                            aria-hidden
                                            className="surface-container-fill absolute inset-y-0 start-0 border-e-2 border-primary transition-[width] duration-500 ease-out"
                                            style={{ width: `${percent}%` }}
                                        />
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
