import { Link } from '@inertiajs/react';
import type { ApexOptions } from 'apexcharts';
import {
    AlertTriangle,
    ArrowDownRight,
    ArrowUpRight,
    Download,
    FileText,
    Inbox,
    Plus,
    Trophy,
} from 'lucide-react';
import ApexChart from '@/components/charts/apex-chart';
import PageHeader from '@/components/shared/page-header';
import Panel from '@/components/shared/panel';
import StatusBadge from '@/components/shared/status-badge';
import type { StatusTone } from '@/components/shared/status-badge';
import { Button } from '@/components/ui/button';
import type { ChartToken, ChartTokens } from '@/hooks/use-chart-tokens';
import { cn } from '@/lib/utils';
import { dashboard } from '@/routes/admin';
import { index as activityLogIndex } from '@/routes/admin/activity-log';
import { index as certificatesIndex } from '@/routes/admin/certificates';
import { index as postsIndex } from '@/routes/admin/posts';
import { create as productCreate } from '@/routes/admin/products';
import {
    exportMethod as exportRfqs,
    index as rfqsIndex,
    show as rfqShow,
} from '@/routes/admin/rfqs';
import type { ActivityLogEntry, RfqRequest } from '@/types/models';

type Stat = {
    key: string;
    label: string;
    value: number;
    /** Selisih persen terhadap bulan lalu; null bila tidak bermakna. */
    delta: number | null;
    hint: string;
};

type MonthlyRfq = { month: string; total: number };
type TopCountry = { country_code: string; total: number };
type TopProduct = { name: string; total: number };

type FunnelStage = {
    status: string;
    label: string;
    color: string;
    total: number;
    share: number;
};

type Conversion = {
    won: number;
    lost: number;
    processed: number;
    rate: number;
};

type ContentStat = {
    key: string;
    label: string;
    value: number;
    hint: string;
};

type Attention = {
    key: string;
    label: string;
    total: number;
    tone: string;
};

type Props = {
    stats: Stat[];
    monthlyRfqs: MonthlyRfq[];
    topCountries: TopCountry[];
    topProducts: TopProduct[];
    funnel: FunnelStage[];
    conversion: Conversion;
    content: ContentStat[];
    attention: Attention[];
    recentRfqs: RfqRequest[];
    recentActivities: ActivityLogEntry[];
};

const MONTH_LABELS = [
    'Jan',
    'Feb',
    'Mar',
    'Apr',
    'Mei',
    'Jun',
    'Jul',
    'Agu',
    'Sep',
    'Okt',
    'Nov',
    'Des',
];

/** Nama negara asal buyer yang paling sering muncul. */
const COUNTRY_NAMES: Record<string, string> = {
    AE: 'Uni Emirat Arab',
    SA: 'Arab Saudi',
    QA: 'Qatar',
    KW: 'Kuwait',
    OM: 'Oman',
    TR: 'Turki',
    DE: 'Jerman',
    NL: 'Belanda',
    PL: 'Polandia',
    RU: 'Rusia',
    UA: 'Ukraina',
    GB: 'Inggris',
    US: 'Amerika Serikat',
    EG: 'Mesir',
    JO: 'Yordania',
    IN: 'India',
    CN: 'Tiongkok',
    JP: 'Jepang',
    KR: 'Korea Selatan',
};

const EVENT_LABELS: Record<string, string> = {
    created: 'dibuat',
    updated: 'diubah',
    deleted: 'dihapus',
    restored: 'dipulihkan',
};

const monthLabel = (value: string) => {
    const [, month] = value.split('-');

    return MONTH_LABELS[Number(month) - 1] ?? value;
};

const number = (value: number) => value.toLocaleString('id-ID');

/** Batang tipis dengan ujung membulat — spesifikasi mark di docs/05. */
const BAR = {
    borderRadius: 4,
    borderRadiusApplication: 'end',
    columnWidth: '46%',
    barHeight: '52%',
} as const;

/**
 * Sumbu kategori grafik batang mendatar memotong teks panjang alih-alih
 * memberinya elipsis, jadi nama dipendekkan sendiri sebelum dikirim.
 */
const shorten = (value: string, limit = 15) =>
    value.length > limit ? `${value.slice(0, limit - 1).trimEnd()}…` : value;

/**
 * Batang mendatar dengan angka di ujung kanan batang. `position: 'top'` pada
 * orientasi mendatar berarti ujung batang, bukan atasnya.
 */
const horizontalBar = (tokens: ChartTokens, max: number): ApexOptions => ({
    chart: { type: 'bar' },
    plotOptions: {
        bar: { ...BAR, horizontal: true, dataLabels: { position: 'top' } },
    },
    // Angka memakai warna teks, bukan warna seri — identitas sudah dibawa
    // batangnya sendiri.
    dataLabels: {
        enabled: true,
        textAnchor: 'start',
        offsetX: 13,
        offsetY: 1,
        style: {
            fontSize: '12px',
            fontWeight: 500,
            colors: [tokens.foreground],
        },
    },
    // Ruang ekstra di kanan supaya label ujung batang terpanjang tidak terpotong.
    // ApexCharts menahan label di dalam area gambar, jadi batang terpanjang
    // harus berhenti jauh sebelum tepi kanan — kalau tidak, labelnya menindih.
    xaxis: { max: Math.max(1, max) * 1.45, labels: { show: false } },
    // Lebar nama kategori ditahan supaya area batang tidak tersisa sedikit.
    yaxis: { labels: { maxWidth: 124 } },
    // `padding.left` melebarkan jalur nama kategori; tanpa itu nama terpanjang
    // terpotong di tepi kiri panel yang sempit.
    grid: {
        show: false,
        padding: { top: -12, right: 0, bottom: -8, left: 18 },
    },
    tooltip: {
        y: { formatter: (value) => `${number(value)} permintaan` },
    },
});

export default function Dashboard({
    stats,
    monthlyRfqs,
    topCountries,
    topProducts,
    funnel,
    conversion,
    content,
    attention,
    recentRfqs,
    recentActivities,
}: Props) {
    const peak = Math.max(0, ...monthlyRfqs.map((row) => row.total));
    const peakMonth = monthlyRfqs.find((row) => row.total === peak);

    /** Peringkat satu seri: negara & produk, satu warna saja. */
    const rankedBar = (
        rows: { label: string; total: number }[],
        tokens: ChartTokens,
    ): ApexOptions => {
        const base = horizontalBar(
            tokens,
            Math.max(...rows.map((row) => row.total)),
        );

        return {
            ...base,
            colors: [tokens.primary],
            dataLabels: {
                ...base.dataLabels,
                formatter: (value) => number(Number(value)),
            },
            xaxis: {
                ...base.xaxis,
                categories: rows.map((row) => shorten(row.label)),
            },
        };
    };

    return (
        <>
            <PageHeader
                title="Dashboard"
                description="Ringkasan permintaan penawaran, isi situs, dan hal yang perlu ditindaklanjuti."
                breadcrumbs={[{ title: 'Dashboard', href: dashboard() }]}
                actions={
                    <>
                        <Button asChild variant="export">
                            <a href={exportRfqs().url}>
                                <Download />
                                Ekspor RFQ
                            </a>
                        </Button>
                        <Button asChild variant="create">
                            <Link href={productCreate()}>
                                <Plus />
                                Tambah Produk
                            </Link>
                        </Button>
                    </>
                }
            />

            {/* KPI: angka utama, bukan grafik — satu nilai per kartu. */}
            <div className="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
                {stats.map((stat) => (
                    <div
                        key={stat.key}
                        className="rounded-card flex flex-col gap-2 border border-border bg-card p-6"
                    >
                        <span className="text-xs text-muted-foreground">
                            {stat.label}
                        </span>

                        <div className="flex flex-wrap items-baseline gap-2">
                            <span className="text-3xl font-semibold tabular-nums">
                                {number(stat.value)}
                            </span>

                            {stat.delta !== null && (
                                <span
                                    className={cn(
                                        'inline-flex items-center gap-0.5 text-xs font-medium',
                                        stat.delta >= 0
                                            ? 'text-success'
                                            : 'text-destructive',
                                    )}
                                >
                                    {stat.delta >= 0 ? (
                                        <ArrowUpRight
                                            aria-hidden
                                            className="size-3.5"
                                        />
                                    ) : (
                                        <ArrowDownRight
                                            aria-hidden
                                            className="size-3.5"
                                        />
                                    )}
                                    {Math.abs(stat.delta).toLocaleString(
                                        'id-ID',
                                    )}
                                    %
                                </span>
                            )}
                        </div>

                        <span className="text-xs text-muted-foreground">
                            {stat.hint}
                        </span>
                    </div>
                ))}
            </div>

            {attention.length > 0 && (
                <Panel
                    title="Perlu Perhatian"
                    description="Hal yang menghambat halaman publik atau penawaran ke buyer."
                >
                    <ul className="grid gap-2 sm:grid-cols-2">
                        {attention.map((item) => (
                            <li
                                key={item.key}
                                className="flex items-center justify-between gap-3 rounded-[var(--radius)] border border-border px-3.5 py-2.5"
                            >
                                <span className="flex items-center gap-2 text-sm">
                                    <AlertTriangle
                                        aria-hidden
                                        className="size-4 shrink-0 text-warning"
                                    />
                                    {item.label}
                                </span>
                                <StatusBadge
                                    label={number(item.total)}
                                    tone={item.tone as StatusTone}
                                />
                            </li>
                        ))}
                    </ul>
                </Panel>
            )}

            <div className="grid gap-5 lg:grid-cols-3">
                {/* Satu seri, satu warna: tinggi batang sudah menyatakan
                    besarannya, jadi warna tidak perlu ikut mengulang. */}
                <Panel
                    className="lg:col-span-2"
                    title="Permintaan 12 Bulan Terakhir"
                    description={
                        peakMonth && peak > 0
                            ? `Puncak ${peak} permintaan pada ${monthLabel(peakMonth.month)}.`
                            : 'Jumlah RFQ yang masuk per bulan.'
                    }
                >
                    <ApexChart
                        type="bar"
                        height={264}
                        series={[
                            {
                                name: 'Permintaan',
                                data: monthlyRfqs.map((row) => row.total),
                            },
                        ]}
                        options={(tokens) => ({
                            chart: { type: 'bar' },
                            plotOptions: { bar: BAR },
                            colors: [tokens.primary],
                            xaxis: {
                                categories: monthlyRfqs.map((row) =>
                                    monthLabel(row.month),
                                ),
                            },
                            yaxis: {
                                min: 0,
                                forceNiceScale: true,
                                labels: {
                                    formatter: (value) =>
                                        number(Math.round(value)),
                                },
                            },
                            tooltip: {
                                y: {
                                    formatter: (value) =>
                                        `${number(value)} permintaan`,
                                },
                            },
                        })}
                    />
                </Panel>

                <Panel
                    title="Tingkat Konversi"
                    description="Perbandingan menang terhadap permintaan yang diproses. Spam tidak dihitung."
                >
                    <div className="flex flex-col gap-2">
                        {/* Satu rasio terhadap batasnya — meter, bukan pai dua irisan. */}
                        <ApexChart
                            type="radialBar"
                            height={200}
                            series={[Math.min(conversion.rate, 100)]}
                            options={(tokens) => ({
                                chart: {
                                    type: 'radialBar',
                                    sparkline: { enabled: true },
                                },
                                colors: [tokens.success],
                                plotOptions: {
                                    radialBar: {
                                        hollow: { size: '62%' },
                                        track: {
                                            background: tokens.muted,
                                            strokeWidth: '100%',
                                        },
                                        dataLabels: {
                                            name: {
                                                offsetY: 22,
                                                fontSize: '12px',
                                                color: tokens[
                                                    'muted-foreground'
                                                ],
                                            },
                                            value: {
                                                offsetY: -12,
                                                fontSize: '30px',
                                                fontWeight: 600,
                                                color: tokens.foreground,
                                                formatter: (value) =>
                                                    `${Number(value).toLocaleString('id-ID')}%`,
                                            },
                                        },
                                    },
                                },
                                labels: ['Permintaan dimenangkan'],
                                stroke: { lineCap: 'round' },
                            })}
                        />

                        <p className="text-center text-xs text-muted-foreground">
                            {number(conversion.won)} menang dari{' '}
                            {number(conversion.processed)} permintaan diproses
                        </p>

                        <dl className="grid grid-cols-2 gap-3 border-t border-border pt-4 text-sm">
                            <div className="space-y-0.5">
                                <dt className="text-xs text-muted-foreground">
                                    Menang
                                </dt>
                                <dd className="font-medium text-success tabular-nums">
                                    {number(conversion.won)}
                                </dd>
                            </div>
                            <div className="space-y-0.5">
                                <dt className="text-xs text-muted-foreground">
                                    Kalah
                                </dt>
                                <dd className="font-medium text-muted-foreground tabular-nums">
                                    {number(conversion.lost)}
                                </dd>
                            </div>
                        </dl>
                    </div>
                </Panel>
            </div>

            <div className="grid gap-5 lg:grid-cols-3">
                {/* Warna status hanya pelengkap: tiap baris membawa label dan
                    angkanya sendiri, karena sebagian pasangan token berdekatan
                    bagi mata buta warna. */}
                <Panel
                    title="Corong Status RFQ"
                    description="Di tahap mana permintaan berhenti."
                >
                    <ApexChart
                        type="bar"
                        height={276}
                        // Warna per tahap lewat `fillColor`, bukan `distributed`,
                        // karena mode distributed menggeser letak label nilai.
                        series={(tokens) => [
                            {
                                name: 'Permintaan',
                                data: funnel.map((stage) => ({
                                    x: shorten(stage.label),
                                    y: stage.total,
                                    fillColor:
                                        tokens[stage.color as ChartToken],
                                })),
                            },
                        ]}
                        options={(tokens) => {
                            const base = horizontalBar(
                                tokens,
                                Math.max(...funnel.map((stage) => stage.total)),
                            );

                            return {
                                ...base,
                                dataLabels: {
                                    ...base.dataLabels,
                                    formatter: (value) => number(Number(value)),
                                },
                                // Porsi tiap tahap dibaca lewat tooltip; ditulis
                                // di batang, angkanya menindih batang terpendek.
                                tooltip: {
                                    y: {
                                        formatter: (value, opts) =>
                                            `${number(value)} permintaan · ${
                                                funnel[
                                                    opts?.dataPointIndex ?? -1
                                                ]?.share.toLocaleString(
                                                    'id-ID',
                                                ) ?? 0
                                            }% dari total`,
                                    },
                                },
                            };
                        }}
                    />
                </Panel>

                <Panel
                    title="Negara Terbanyak"
                    description="Asal permintaan penawaran."
                >
                    {topCountries.length === 0 ? (
                        <p className="text-sm text-muted-foreground">
                            Belum ada permintaan masuk.
                        </p>
                    ) : (
                        <ApexChart
                            type="bar"
                            height={276}
                            series={[
                                {
                                    name: 'Permintaan',
                                    data: topCountries.map((row) => row.total),
                                },
                            ]}
                            options={(tokens) =>
                                rankedBar(
                                    topCountries.map((row) => ({
                                        label:
                                            COUNTRY_NAMES[row.country_code] ??
                                            row.country_code,
                                        total: row.total,
                                    })),
                                    tokens,
                                )
                            }
                        />
                    )}
                </Panel>

                <Panel
                    title="Produk Paling Diminati"
                    description="Paling sering disebut di RFQ."
                >
                    {topProducts.length === 0 ? (
                        <p className="text-sm text-muted-foreground">
                            Belum ada RFQ yang menyebut produk tertentu.
                        </p>
                    ) : (
                        <>
                            <p className="mb-1 flex items-center gap-1.5 text-[13px] text-muted-foreground">
                                <Trophy
                                    aria-hidden
                                    className="size-3.5 shrink-0 text-warning"
                                />
                                <span className="truncate">
                                    Teratas: {topProducts[0]?.name}
                                </span>
                            </p>
                            <ApexChart
                                type="bar"
                                height={252}
                                series={[
                                    {
                                        name: 'Permintaan',
                                        data: topProducts.map(
                                            (row) => row.total,
                                        ),
                                    },
                                ]}
                                options={(tokens) =>
                                    rankedBar(
                                        topProducts.map((row) => ({
                                            label: row.name,
                                            total: row.total,
                                        })),
                                        tokens,
                                    )
                                }
                            />
                        </>
                    )}
                </Panel>
            </div>

            <div className="grid gap-5 lg:grid-cols-3">
                <Panel
                    className="lg:col-span-2"
                    title="RFQ Terbaru"
                    description="Delapan permintaan terakhir yang masuk."
                    action={
                        <Button asChild variant="filter" size="sm">
                            <Link href={rfqsIndex()}>
                                <Inbox />
                                Buka Inbox
                            </Link>
                        </Button>
                    }
                >
                    {recentRfqs.length === 0 ? (
                        <p className="text-sm text-muted-foreground">
                            Belum ada permintaan penawaran.
                        </p>
                    ) : (
                        <ul className="overflow-hidden rounded-[var(--radius)] border border-border">
                            {recentRfqs.map((rfq) => (
                                <li
                                    key={rfq.id}
                                    className="flex flex-wrap items-center justify-between gap-3 border-b border-border px-4 py-3 last:border-b-0"
                                >
                                    <div className="flex min-w-0 flex-col leading-tight">
                                        <Link
                                            href={rfqShow(rfq.id)}
                                            className={cn(
                                                'truncate hover:underline',
                                                rfq.is_read
                                                    ? 'font-medium'
                                                    : 'font-semibold',
                                            )}
                                        >
                                            {rfq.company_name}
                                        </Link>
                                        <span
                                            dir="ltr"
                                            className="truncate text-xs text-muted-foreground"
                                        >
                                            {rfq.reference_no} ·{' '}
                                            {COUNTRY_NAMES[rfq.country_code] ??
                                                rfq.country_code}
                                        </span>
                                    </div>

                                    <div className="flex items-center gap-2">
                                        <span className="text-xs text-muted-foreground">
                                            {rfq.created_at}
                                        </span>
                                        <StatusBadge
                                            label={rfq.status_label}
                                            tone={
                                                rfq.status_color as StatusTone
                                            }
                                        />
                                    </div>
                                </li>
                            ))}
                        </ul>
                    )}
                </Panel>

                <Panel
                    title="Aktivitas Terakhir"
                    description="Perubahan data yang tercatat."
                    action={
                        <Button asChild variant="filter" size="sm">
                            <Link href={activityLogIndex()}>
                                <FileText />
                                Log
                            </Link>
                        </Button>
                    }
                >
                    {recentActivities.length === 0 ? (
                        <p className="text-sm text-muted-foreground">
                            Belum ada aktivitas tercatat.
                        </p>
                    ) : (
                        <ol className="space-y-3 border-s border-border ps-4">
                            {recentActivities.map((activity) => (
                                <li key={activity.id} className="space-y-0.5">
                                    <p className="text-[13px] leading-tight">
                                        {activity.subject_label ?? 'Objek'}{' '}
                                        {EVENT_LABELS[activity.event ?? ''] ??
                                            activity.event}
                                    </p>
                                    <p className="text-xs text-muted-foreground">
                                        {activity.causer_name} ·{' '}
                                        {activity.created_at}
                                    </p>
                                </li>
                            ))}
                        </ol>
                    )}
                </Panel>
            </div>

            <Panel
                title="Ringkasan Isi Situs"
                description="Konten yang menopang halaman publik."
                action={
                    <div className="flex items-center gap-2">
                        <Button asChild variant="filter" size="sm">
                            <Link href={certificatesIndex()}>Sertifikat</Link>
                        </Button>
                        <Button asChild variant="filter" size="sm">
                            <Link href={postsIndex()}>Artikel</Link>
                        </Button>
                    </div>
                }
            >
                <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    {content.map((item) => (
                        <div
                            key={item.key}
                            className="flex flex-col gap-1 rounded-[var(--radius)] border border-border p-4"
                        >
                            <span className="text-xs text-muted-foreground">
                                {item.label}
                            </span>
                            <span className="text-2xl font-semibold tabular-nums">
                                {number(item.value)}
                            </span>
                            <span className="text-xs text-muted-foreground">
                                {item.hint}
                            </span>
                        </div>
                    ))}
                </div>
            </Panel>
        </>
    );
}
