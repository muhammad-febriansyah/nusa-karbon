import type { ApexOptions } from 'apexcharts';
// Varian `core` hanya memuat jenis grafik yang benar-benar dipakai; impor penuh
// menambah ~500 kB ke bundel halaman.
import 'apexcharts/bar';
import 'apexcharts/column';
import 'apexcharts/radialBar';
import ReactApexChart from 'react-apexcharts/core';
import type { ChartTokens } from '@/hooks/use-chart-tokens';
import { useChartTokens } from '@/hooks/use-chart-tokens';
import { cn } from '@/lib/utils';

type Series = ApexOptions['series'];

type Props = {
    type: 'bar' | 'line' | 'area' | 'radialBar' | 'donut';
    /** Fungsi bila datanya perlu warna tema, misalnya `fillColor` per titik. */
    series: Series | ((tokens: ChartTokens) => Series);
    /**
     * Opsi khusus grafik. Menerima token tema supaya warna sumbu, grid, dan
     * seri tidak ditulis sebagai hex tetap di halaman.
     */
    options: (tokens: ChartTokens) => ApexOptions;
    height: number;
    className?: string;
};

/**
 * Dasar bersama semua grafik admin: sumbu dan grid dibuat samar, toolbar mati,
 * tooltip mengikuti tema. Halaman hanya mengatur bagian yang khas grafiknya.
 */
function baseOptions(tokens: ChartTokens): ApexOptions {
    const label = {
        colors: tokens['muted-foreground'],
        fontSize: '11px',
        fontFamily: 'inherit',
    };

    return {
        chart: {
            fontFamily: 'inherit',
            foreColor: tokens['muted-foreground'],
            toolbar: { show: false },
            zoom: { enabled: false },
            animations: { enabled: true, speed: 320 },
            parentHeightOffset: 0,
        },
        grid: {
            borderColor: tokens.border,
            strokeDashArray: 4,
            padding: { top: 0, right: 0, bottom: 0, left: 4 },
            xaxis: { lines: { show: false } },
            yaxis: { lines: { show: true } },
        },
        dataLabels: { enabled: false },
        legend: { show: false },
        states: {
            hover: { filter: { type: 'lighten' } },
            active: { filter: { type: 'none' } },
        },
        tooltip: {
            theme: 'light',
            style: { fontSize: '12px', fontFamily: 'inherit' },
            marker: { show: true },
        },
        xaxis: {
            axisBorder: { show: false },
            axisTicks: { show: false },
            labels: { style: label },
            crosshairs: { show: false },
        },
        yaxis: {
            labels: { style: label },
        },
    };
}

/** Menggabungkan opsi dasar dengan opsi halaman, satu tingkat per kunci. */
function merge(base: ApexOptions, extra: ApexOptions): ApexOptions {
    const result: Record<string, unknown> = { ...base };

    for (const [key, value] of Object.entries(extra)) {
        const current = result[key];

        result[key] =
            current !== null &&
            typeof current === 'object' &&
            !Array.isArray(current) &&
            value !== null &&
            typeof value === 'object' &&
            !Array.isArray(value)
                ? { ...current, ...value }
                : value;
    }

    return result as ApexOptions;
}

export default function ApexChart({
    type,
    series,
    options,
    height,
    className,
}: Props) {
    const tokens = useChartTokens();

    return (
        <div className={cn('w-full', className)}>
            <ReactApexChart
                type={type}
                series={typeof series === 'function' ? series(tokens) : series}
                height={height}
                options={merge(baseOptions(tokens), options(tokens))}
            />
        </div>
    );
}
