import { Link } from '@inertiajs/react';
import { ArrowRight } from 'lucide-react';
import BlurFade from '@/components/public/blur-fade';
import { containerCalculator } from '@/routes/public';
import type { PublicContainerLoad } from '@/types';

type Props = {
    load: PublicContainerLoad | null;
};

const integer = new Intl.NumberFormat('id-ID');

const decimal = new Intl.NumberFormat('id-ID', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
});

/**
 * Batas yang habis lebih dulu.
 *
 * Ini pertanyaan kedua setiap importir setelah spesifikasi: apakah kontainernya
 * penuh karena berat atau karena ruang. Jawabannya menentukan apakah menambah
 * pesanan masih ada gunanya, jadi ditulis sebagai kalimat, bukan istilah.
 */
const LIMIT_LABEL: Record<PublicContainerLoad['limited_by'], string> = {
    weight: 'batas berat muat tercapai lebih dulu, ruangnya masih tersisa.',
    volume: 'ruang habis lebih dulu, batas berat muat belum tercapai.',
};

function Figure({
    label,
    value,
    unit,
}: {
    label: string;
    value: string;
    unit?: string;
}) {
    return (
        <div>
            <dt className="text-sm text-muted-foreground">{label}</dt>
            {/* Angka besar memakai Archivo; alasannya sama dengan pita hero. */}
            <dd
                dir="ltr"
                className="mt-2 font-display text-2xl leading-none font-semibold tracking-[-0.01em] tabular-nums"
            >
                {value}
                {unit && (
                    <span className="ms-1.5 text-xs font-normal text-muted-foreground">
                        {unit}
                    </span>
                )}
            </dd>
        </div>
    );
}

/**
 * Denah muat satu kontainer.
 *
 * Menyusul hero yang menjawab "barangnya seperti apa", bagian ini menjawab
 * "berapa yang muat" — dihitung dari master carton produk acuan terhadap
 * kontainer pertama yang aktif, bukan angka contoh. Menghilang seluruhnya bila
 * datanya belum lengkap, karena denah kontainer kosong lebih menyesatkan
 * daripada tidak ada denah sama sekali.
 */
export default function ContainerLoadBand({ load }: Props) {
    if (load === null) {
        return null;
    }

    const { container, product } = load;
    const length = container.internal_length_m;

    /*
     * Dibulatkan ke bawah, bukan ke terdekat. Muatan 99,98% yang ditampilkan
     * sebagai "100%" membuat pembeli mengira kontainernya persis penuh dan
     * tidak ada ruang tersisa untuk sample atau dokumen — angka yang sedikit
     * merendah selalu lebih aman daripada yang sedikit melebih.
     */
    const percent = Math.floor(load.utilisation * 100);

    return (
        <section className="border-b border-border/70">
            <div className="mx-auto w-full max-w-7xl px-6 py-16 lg:py-20">
                <BlurFade>
                    <p className="text-sm font-medium text-muted-foreground">
                        Muatan · {container.code}
                    </p>

                    <h2 className="mt-2 max-w-2xl font-display text-3xl leading-[1.1] font-bold tracking-[-0.03em] text-balance sm:text-4xl">
                        Satu {container.name} memuat{' '}
                        <span className="tabular-nums">
                            {integer.format(load.cartons)}
                        </span>{' '}
                        karton.
                    </h2>

                    <p className="mt-4 max-w-xl text-base leading-relaxed text-muted-foreground">
                        {product.name ?? product.code} —{' '}
                        {LIMIT_LABEL[load.limited_by]}
                    </p>
                </BlurFade>

                {/*
                 * Denah dan angkanya berdampingan, bukan bertumpuk. Rasio 20FT
                 * kira-kira 2,5:1 — dibiarkan selebar halaman, denahnya setinggi
                 * satu layar penuh dan menenggelamkan angka di bawahnya, padahal
                 * angka itulah isi bagian ini.
                 */}
                <div className="mt-12 grid gap-10 lg:grid-cols-[minmax(0,1.5fr)_minmax(0,1fr)] lg:items-center lg:gap-14">
                    <div>
                        {/*
                         * Tampak atas ruang kontainer. Rasionya mengikuti panjang
                         * dan lebar dalam yang sebenarnya, jadi bidang ini bukan
                         * ilustrasi bebas — 20FT dan 40FT terlihat berbeda panjang
                         * persis seperti aslinya.
                         */}
                        <div
                            className="rounded-panel relative w-full overflow-hidden border border-border/70 bg-card shadow-card"
                            style={{
                                aspectRatio:
                                    length === null ||
                                    container.internal_width_m === null
                                        ? '5.9 / 2.35'
                                        : `${length} / ${container.internal_width_m}`,
                            }}
                        >
                            <div
                                aria-hidden
                                /*
                                 * Tepi kanannya sengaja tebal dan berwarna merek:
                                 * itulah satu-satunya penanda "sampai sini penuh".
                                 * Arsir di dalamnya boleh samar, garis batas ini
                                 * tidak boleh.
                                 */
                                className="surface-container-fill absolute inset-y-0 start-0 animate-container-fill border-e-2 border-primary ltr:origin-left rtl:origin-right"
                                style={{ width: `${percent}%` }}
                            />
                        </div>

                        {/*
                         * Garis ukuran panjang dalam, memakai konvensi yang sama
                         * dengan gambar teknik di hero: garis tunggal berujung
                         * penanda, angka di tengah.
                         */}
                        {length !== null && (
                            <div
                                className="mt-3 flex items-center gap-3 text-muted-foreground"
                                aria-hidden
                            >
                                <span className="h-2.5 w-px bg-border" />
                                <span className="h-px flex-1 bg-border" />
                                <span
                                    dir="ltr"
                                    className="font-mono text-[11px] tracking-[0.1em] tabular-nums"
                                >
                                    {decimal.format(length)} m
                                </span>
                                <span className="h-px flex-1 bg-border" />
                                <span className="h-2.5 w-px bg-border" />
                            </div>
                        )}
                    </div>

                    <dl className="grid grid-cols-2 gap-x-6 gap-y-8 border-t border-border/70 pt-8 lg:border-s lg:border-t-0 lg:ps-10 lg:pt-0">
                        <Figure
                            label="Karton"
                            value={integer.format(load.cartons)}
                            unit={`× ${integer.format(product.master_carton_weight_kg)} kg`}
                        />
                        <Figure
                            label="Berat bersih"
                            value={integer.format(load.net_weight_kg)}
                            unit="kg"
                        />
                        {/*
                         * Satuannya naik ke label supaya nilai dan pembandingnya
                         * muat sebaris di layar sempit; "/ 33,20 CBM" memaksa
                         * "CBM" turun sendirian ke baris berikutnya.
                         */}
                        <Figure
                            label="Volume · CBM"
                            value={decimal.format(load.volume_cbm)}
                            unit={`/ ${decimal.format(container.max_cbm)}`}
                        />
                        <Figure
                            label="Terpakai"
                            value={`${integer.format(percent)}`}
                            unit="%"
                        />
                    </dl>
                </div>

                <Link
                    href={containerCalculator()}
                    className="group mt-10 inline-flex items-center gap-2 text-sm font-medium underline-offset-8 transition-colors hover:text-primary hover:underline focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
                >
                    Hitung untuk produk dan kontainer lain
                    <ArrowRight className="size-4 transition-transform group-hover:translate-x-0.5" />
                </Link>
            </div>
        </section>
    );
}
