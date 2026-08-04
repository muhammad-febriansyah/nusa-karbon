import { Link, router } from '@inertiajs/react';
import {
    Box,
    Copy,
    Download,
    FileText,
    ImageOff,
    Inbox,
    Pencil,
    Star,
    TriangleAlert,
} from 'lucide-react';
import PageHeader from '@/components/shared/page-header';
import Panel from '@/components/shared/panel';
import StatusBadge from '@/components/shared/status-badge';
import type { StatusTone } from '@/components/shared/status-badge';
import TranslationIndicator from '@/components/shared/translation-indicator';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes/admin';
import { index as containerTypesIndex } from '@/routes/admin/container-types';
import {
    duplicate as duplicateProduct,
    edit as productEdit,
    index as productsIndex,
    show as productShow,
} from '@/routes/admin/products';
import { show as rfqShow } from '@/routes/admin/rfqs';
import type { ContainerLoad, Product, RfqRequest } from '@/types/models';

const LOCALE = 'id';

type PackagingOption = { id: number; name: string };

type Props = {
    product: Product;
    packagingOptions: PackagingOption[];
    containerLoads: ContainerLoad[];
    rfqs: RfqRequest[];
    rfqTotal: number;
};

type Field = {
    label: string;
    value: string | null;
    /** Angka dan kode dibaca kiri-ke-kanan walau antarmukanya RTL. */
    ltr?: boolean;
};

const number = (value: number) => value.toLocaleString('id-ID');

const decimal = (value: number | null, digits = 2, unit?: string) =>
    value === null
        ? null
        : `${value.toLocaleString('id-ID', {
              minimumFractionDigits: 0,
              maximumFractionDigits: digits,
          })}${unit ? ` ${unit}` : ''}`;

const fileSize = (bytes: number) =>
    bytes >= 1_048_576
        ? `${(bytes / 1_048_576).toLocaleString('id-ID', { maximumFractionDigits: 1 })} MB`
        : `${Math.max(1, Math.round(bytes / 1024)).toLocaleString('id-ID')} KB`;

/** Baris label–nilai; dipakai di beberapa panel. */
function FieldList({
    fields,
    columns,
}: {
    fields: Field[];
    columns?: boolean;
}) {
    return (
        <dl className={columns ? 'grid gap-4 sm:grid-cols-2' : 'grid gap-3'}>
            {fields.map((field) => (
                <div key={field.label} className="space-y-0.5">
                    <dt className="text-xs text-muted-foreground">
                        {field.label}
                    </dt>
                    <dd dir={field.ltr ? 'ltr' : undefined} className="text-sm">
                        {field.value ?? '—'}
                    </dd>
                </div>
            ))}
        </dl>
    );
}

export default function ProductShow({
    product,
    packagingOptions,
    containerLoads,
    rfqs,
    rfqTotal,
}: Props) {
    const name = product.name?.[LOCALE] ?? 'Tanpa nama';

    const dimensions =
        product.master_carton_length_cm &&
        product.master_carton_width_cm &&
        product.master_carton_height_cm
            ? `${decimal(product.master_carton_length_cm, 1)} × ${decimal(
                  product.master_carton_width_cm,
                  1,
              )} × ${decimal(product.master_carton_height_cm, 1)} cm`
            : null;

    const summary: Field[] = [
        { label: 'Kode produk', value: product.code, ltr: true },
        {
            label: 'Kategori',
            value: product.category?.name?.[LOCALE] ?? 'Tanpa kategori',
        },
        { label: 'Bentuk', value: product.shape },
        { label: 'Ukuran', value: product.size_mm, ltr: true },
        {
            label: 'MOQ',
            value: decimal(product.moq_kg, 0, 'kg'),
            ltr: true,
        },
        {
            label: 'Urutan tampil',
            value: String(product.sort_order),
            ltr: true,
        },
    ];

    const carton: Field[] = [
        {
            label: 'Berat bersih',
            value: decimal(product.master_carton_weight_kg, 2, 'kg'),
            ltr: true,
        },
        {
            label: 'Berat kemasan',
            value: decimal(product.master_carton_tare_kg, 2, 'kg'),
            ltr: true,
        },
        {
            label: 'Volume',
            value: decimal(product.master_carton_cbm, 4, 'CBM'),
            ltr: true,
        },
        { label: 'Dimensi', value: dimensions, ltr: true },
        {
            label: 'Berat inner box',
            value: decimal(product.inner_box_weight_kg, 2, 'kg'),
            ltr: true,
        },
        {
            label: 'Inner box per karton',
            value:
                product.inner_boxes_per_carton === null
                    ? null
                    : number(product.inner_boxes_per_carton),
            ltr: true,
        },
    ];

    const metadata: Field[] = [
        { label: 'Dibuat', value: product.created_at },
        { label: 'Terakhir diubah', value: product.updated_at },
        { label: 'Meta title', value: product.meta_title?.[LOCALE] ?? null },
        {
            label: 'Meta description',
            value: product.meta_description?.[LOCALE] ?? null,
        },
    ];

    return (
        <>
            <PageHeader
                title={name}
                description={`Rangkuman data produk ${product.code} beserta estimasi muatannya.`}
                breadcrumbs={[
                    { title: 'Dashboard', href: dashboard() },
                    { title: 'Produk', href: productsIndex() },
                    { title: name, href: productShow(product.id) },
                ]}
                actions={
                    <>
                        <Button
                            type="button"
                            variant="filter"
                            onClick={() =>
                                router.post(
                                    duplicateProduct(product.id).url,
                                    {},
                                    { preserveScroll: true },
                                )
                            }
                        >
                            <Copy />
                            Duplikat
                        </Button>
                        <Button asChild variant="edit">
                            <Link href={productEdit(product.id)}>
                                <Pencil />
                                Ubah Produk
                            </Link>
                        </Button>
                    </>
                }
            />

            {!product.is_calculable && (
                <div className="rounded-card flex items-start gap-3 border border-s-[3px] border-warning/30 border-s-warning bg-warning/10 px-4 py-3">
                    <TriangleAlert
                        aria-hidden
                        className="mt-0.5 size-4 shrink-0 text-warning"
                    />
                    <div className="space-y-0.5 text-sm">
                        <p className="font-medium text-warning">
                            Produk belum masuk Container Calculator
                        </p>
                        <p className="text-muted-foreground">
                            Berat dan volume master carton keduanya harus terisi
                            supaya estimasi muatan bisa dihitung.
                        </p>
                    </div>
                </div>
            )}

            <div className="grid gap-5 lg:grid-cols-3">
                <div className="space-y-5 lg:col-span-2">
                    <Panel
                        title="Ringkasan"
                        description="Identitas produk seperti yang tampil di katalog."
                    >
                        <FieldList fields={summary} columns />

                        {product.short_description?.[LOCALE] && (
                            <div className="mt-5 space-y-0.5">
                                <p className="text-xs text-muted-foreground">
                                    Deskripsi singkat
                                </p>
                                <p className="text-sm">
                                    {product.short_description[LOCALE]}
                                </p>
                            </div>
                        )}

                        {product.description?.[LOCALE] && (
                            <div className="mt-5 space-y-0.5">
                                <p className="text-xs text-muted-foreground">
                                    Deskripsi
                                </p>
                                <p className="text-sm whitespace-pre-line">
                                    {product.description[LOCALE]}
                                </p>
                            </div>
                        )}
                    </Panel>

                    <Panel
                        title="Master Carton"
                        description="Sumber angka Container Calculator."
                    >
                        <FieldList fields={carton} columns />
                    </Panel>

                    <Panel
                        title="Spesifikasi Teknis"
                        description="Angka yang dibandingkan buyer sebelum meminta penawaran."
                    >
                        {product.specifications.length === 0 ? (
                            <p className="text-sm text-muted-foreground">
                                Belum ada baris spesifikasi.
                            </p>
                        ) : (
                            <ul className="overflow-hidden rounded-[var(--radius)] border border-border">
                                {product.specifications.map((spec) => (
                                    <li
                                        key={spec.id}
                                        className="flex flex-wrap items-center justify-between gap-3 border-b border-border px-4 py-2.5 last:border-b-0"
                                    >
                                        <span className="flex items-center gap-1.5 text-sm">
                                            {spec.parameter?.[LOCALE] ?? '—'}
                                            {spec.is_highlighted && (
                                                <Star
                                                    aria-label="Ditonjolkan di halaman publik"
                                                    className="size-3.5 text-warning"
                                                />
                                            )}
                                        </span>
                                        <span
                                            dir="ltr"
                                            className="text-sm font-medium tabular-nums"
                                        >
                                            {spec.value}
                                            {spec.unit ? ` ${spec.unit}` : ''}
                                        </span>
                                    </li>
                                ))}
                            </ul>
                        )}
                    </Panel>

                    <Panel
                        title="Estimasi Muatan Kontainer"
                        description="Hasil Container Calculator memakai data master carton di atas."
                    >
                        {containerLoads.length === 0 ? (
                            // Dua sebab yang berbeda: datanya kurang, atau belum
                            // ada kontainer sama sekali. Pesannya dibedakan agar
                            // admin tahu apa yang harus diperbaiki.
                            <p className="text-sm text-muted-foreground">
                                {product.is_calculable ? (
                                    <>
                                        Belum ada tipe kontainer aktif.{' '}
                                        <Link
                                            href={containerTypesIndex()}
                                            className="text-primary hover:underline"
                                        >
                                            Tambahkan tipe kontainer
                                        </Link>{' '}
                                        untuk melihat estimasi muatan.
                                    </>
                                ) : (
                                    'Lengkapi berat dan volume master carton untuk melihat estimasi muatan.'
                                )}
                            </p>
                        ) : (
                            <div className="overflow-x-auto">
                                <table className="w-full text-sm">
                                    <thead>
                                        <tr className="border-b border-border text-start text-xs text-muted-foreground">
                                            <th className="py-2 pe-3 text-start font-medium">
                                                Kontainer
                                            </th>
                                            <th className="py-2 pe-3 text-end font-medium">
                                                Karton
                                            </th>
                                            <th className="py-2 pe-3 text-end font-medium">
                                                Berat kotor
                                            </th>
                                            <th className="py-2 pe-3 text-end font-medium">
                                                Volume
                                            </th>
                                            <th className="py-2 text-end font-medium">
                                                Pemakaian
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {containerLoads.map((load) => (
                                            <tr
                                                key={load.container_id}
                                                className="border-b border-border last:border-b-0"
                                            >
                                                <td className="py-2.5 pe-3">
                                                    <span
                                                        dir="ltr"
                                                        className="font-medium"
                                                    >
                                                        {load.code}
                                                    </span>
                                                    <span className="block text-xs text-muted-foreground">
                                                        {/* Batasannya disebut
                                                            eksplisit supaya
                                                            angkanya tidak
                                                            disalahartikan. */}
                                                        Dibatasi{' '}
                                                        {load.limited_by ===
                                                        'weight'
                                                            ? 'berat'
                                                            : 'volume'}
                                                    </span>
                                                </td>
                                                <td
                                                    dir="ltr"
                                                    className="py-2.5 pe-3 text-end tabular-nums"
                                                >
                                                    {number(load.cartons)}
                                                </td>
                                                <td
                                                    dir="ltr"
                                                    className="py-2.5 pe-3 text-end tabular-nums"
                                                >
                                                    {decimal(
                                                        load.gross_weight_kg,
                                                        0,
                                                        'kg',
                                                    )}
                                                </td>
                                                <td
                                                    dir="ltr"
                                                    className="py-2.5 pe-3 text-end tabular-nums"
                                                >
                                                    {decimal(
                                                        load.volume_cbm,
                                                        2,
                                                        'CBM',
                                                    )}
                                                </td>
                                                <td className="py-2.5 text-end">
                                                    <span
                                                        dir="ltr"
                                                        className="tabular-nums"
                                                    >
                                                        {Math.round(
                                                            load.utilisation *
                                                                100,
                                                        )}
                                                        %
                                                    </span>
                                                    <span
                                                        aria-hidden
                                                        className="ms-auto mt-2 block h-1.5 w-16 overflow-hidden rounded-full bg-muted"
                                                    >
                                                        <span
                                                            className="block h-1.5 rounded-full bg-primary"
                                                            style={{
                                                                width: `${Math.round(load.utilisation * 100)}%`,
                                                            }}
                                                        />
                                                    </span>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        )}
                    </Panel>

                    <Panel
                        title="Dokumen"
                        description="Berkas yang bisa diunduh buyer dari halaman produk."
                    >
                        {product.documents.length === 0 ? (
                            <p className="text-sm text-muted-foreground">
                                Belum ada dokumen terlampir.
                            </p>
                        ) : (
                            <ul className="grid gap-2">
                                {product.documents.map((document) => (
                                    <li
                                        key={document.id}
                                        className="flex flex-wrap items-center justify-between gap-3 rounded-[var(--radius)] border border-border px-4 py-3"
                                    >
                                        <div className="flex min-w-0 items-center gap-3">
                                            <FileText
                                                aria-hidden
                                                className="size-4 shrink-0 text-muted-foreground"
                                            />
                                            <div className="min-w-0 leading-tight">
                                                <p className="truncate text-sm font-medium">
                                                    {document.title?.[LOCALE] ??
                                                        document.file_name}
                                                </p>
                                                <p
                                                    dir="ltr"
                                                    className="truncate text-xs text-muted-foreground"
                                                >
                                                    {document.type_label} ·{' '}
                                                    {fileSize(
                                                        document.file_size,
                                                    )}
                                                    {document.issued_at
                                                        ? ` · ${document.issued_at}`
                                                        : ''}
                                                    {' · '}
                                                    {number(
                                                        document.download_count,
                                                    )}
                                                    × diunduh
                                                </p>
                                            </div>
                                        </div>

                                        <div className="flex items-center gap-2">
                                            {!document.is_active && (
                                                <StatusBadge
                                                    label="Nonaktif"
                                                    tone="muted"
                                                />
                                            )}
                                            <Button
                                                asChild
                                                variant="filter"
                                                size="sm"
                                            >
                                                <a
                                                    href={document.url}
                                                    target="_blank"
                                                    rel="noreferrer"
                                                >
                                                    <Download />
                                                    Unduh
                                                </a>
                                            </Button>
                                        </div>
                                    </li>
                                ))}
                            </ul>
                        )}
                    </Panel>
                </div>

                <div className="space-y-5">
                    <Panel title="Status">
                        <div className="grid gap-3">
                            <div className="flex flex-wrap items-center gap-2">
                                <StatusBadge
                                    label={
                                        product.is_active ? 'Aktif' : 'Nonaktif'
                                    }
                                    tone={
                                        (product.is_active
                                            ? 'success'
                                            : 'muted') as StatusTone
                                    }
                                />
                                {product.is_featured && (
                                    <StatusBadge
                                        label="Unggulan"
                                        tone="warning"
                                    />
                                )}
                                <StatusBadge
                                    label={
                                        product.is_calculable
                                            ? 'Siap kalkulator'
                                            : 'Data carton kurang'
                                    }
                                    tone={
                                        (product.is_calculable
                                            ? 'info'
                                            : 'warning') as StatusTone
                                    }
                                />
                            </div>

                            <div className="space-y-1">
                                <p className="text-xs text-muted-foreground">
                                    Kelengkapan terjemahan
                                </p>
                                <TranslationIndicator
                                    completeness={product.completeness}
                                />
                            </div>
                        </div>
                    </Panel>

                    <Panel title="Gambar">
                        {product.cover_url ? (
                            <img
                                src={product.cover_url}
                                alt={`Sampul ${name}`}
                                className="aspect-4/3 w-full rounded-[var(--radius)] border border-border object-cover"
                            />
                        ) : (
                            <div className="flex aspect-4/3 w-full items-center justify-center rounded-[var(--radius)] border border-dashed border-border text-muted-foreground">
                                <ImageOff aria-hidden className="size-6" />
                                <span className="sr-only">
                                    Belum ada gambar sampul
                                </span>
                            </div>
                        )}

                        {product.gallery.length > 0 && (
                            <ul className="mt-3 grid grid-cols-3 gap-2">
                                {product.gallery.map((image) => (
                                    <li key={image.id}>
                                        <img
                                            src={image.url}
                                            alt={image.name}
                                            className="aspect-square w-full rounded-[var(--radius)] border border-border object-cover"
                                        />
                                    </li>
                                ))}
                            </ul>
                        )}
                    </Panel>

                    <Panel title="Opsi Kemasan">
                        {packagingOptions.length === 0 ? (
                            <p className="text-sm text-muted-foreground">
                                Belum ada opsi kemasan yang dipilih.
                            </p>
                        ) : (
                            <ul className="flex flex-wrap gap-2">
                                {packagingOptions.map((option) => (
                                    <li
                                        key={option.id}
                                        className="inline-flex items-center gap-1.5 rounded-full border border-border px-2.5 py-1 text-[13px]"
                                    >
                                        <Box
                                            aria-hidden
                                            className="size-3.5 text-muted-foreground"
                                        />
                                        {option.name}
                                    </li>
                                ))}
                            </ul>
                        )}
                    </Panel>

                    <Panel
                        title="Permintaan Terkait"
                        description={
                            rfqTotal === 0
                                ? 'Belum ada RFQ yang menyebut produk ini.'
                                : `${number(rfqTotal)} RFQ menyebut produk ini.`
                        }
                    >
                        {rfqs.length === 0 ? (
                            <p className="flex items-center gap-2 text-sm text-muted-foreground">
                                <Inbox aria-hidden className="size-4" />
                                Belum ada permintaan masuk.
                            </p>
                        ) : (
                            <ul className="grid gap-2">
                                {rfqs.map((rfq) => (
                                    <li key={rfq.id}>
                                        <Link
                                            href={rfqShow(rfq.id)}
                                            className="flex items-center justify-between gap-3 rounded-[var(--radius)] border border-border px-3 py-2 transition-colors hover:bg-muted"
                                        >
                                            <span className="min-w-0 leading-tight">
                                                <span className="block truncate text-sm font-medium">
                                                    {rfq.company_name}
                                                </span>
                                                <span
                                                    dir="ltr"
                                                    className="block truncate text-xs text-muted-foreground"
                                                >
                                                    {rfq.reference_no}
                                                </span>
                                            </span>
                                            <StatusBadge
                                                label={rfq.status_label}
                                                tone={
                                                    rfq.status_color as StatusTone
                                                }
                                            />
                                        </Link>
                                    </li>
                                ))}
                            </ul>
                        )}
                    </Panel>

                    <Panel title="Metadata">
                        <FieldList fields={metadata} />
                    </Panel>
                </div>
            </div>
        </>
    );
}
