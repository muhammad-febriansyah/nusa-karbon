import { ChevronLeft, ChevronRight } from 'lucide-react';
import { useCallback, useEffect, useRef, useState } from 'react';
import type { ReactNode } from 'react';
import Section from '@/components/public/section';
import { Button } from '@/components/ui/button';

/** Seberapa jauh satu tekanan panah menggeser, sebagai bagian dari lebar rel. */
const SCROLL_RATIO = 0.8;

/** Toleransi piksel saat menentukan rel sudah mentok. */
const EDGE_SLACK = 4;

type Props = {
    eyebrow: string;
    title: string;
    description?: string;
    /**
     * Dipakai pada label tombol panah, misalnya "tahap" menghasilkan
     * "Tahap sebelumnya". Pembaca layar mendengar banyak tombol panah di satu
     * halaman; tanpa ini semuanya berbunyi sama.
     */
    unit: string;
    children: ReactNode;
};

/**
 * Pengunjung yang meminta pengurangan gerak tetap harus bisa berpindah kartu —
 * yang dihilangkan geseran halusnya, bukan perpindahannya.
 */
function scrollBehavior(): ScrollBehavior {
    return window.matchMedia('(prefers-reduced-motion: reduce)').matches
        ? 'auto'
        : 'smooth';
}

/**
 * Satu bagian halaman berisi deret kartu yang digeser mendatar.
 *
 * Alasannya bukan gaya, melainkan tinggi halaman. Daftar tiga puluh butir yang
 * ditumpuk dalam kisi memakan sekitar 4.100 piksel; deret yang sama sebagai
 * rel geser memakan sekitar 800. Beranda ini punya tiga daftar sepanjang itu,
 * dan menumpuk semuanya membuat halaman perlu enam belas layar penuh untuk
 * dilewati.
 *
 * Dipakai hanya untuk isi yang dijelajahi, bukan yang dibandingkan. Kartu
 * produk sengaja tetap kisi: ukuran dan MOQ-nya harus sejajar supaya bisa
 * dibandingkan sekilas, dan rel geser justru menyembunyikan sebagian.
 */
export default function CardCarousel({
    eyebrow,
    title,
    description,
    unit,
    children,
}: Props) {
    const trackRef = useRef<HTMLDivElement>(null);
    const [atStart, setAtStart] = useState(true);
    const [atEnd, setAtEnd] = useState(false);

    const syncEdges = useCallback(() => {
        const track = trackRef.current;

        if (track === null) {
            return;
        }

        /*
         * `scrollLeft` bernilai negatif pada arah kanan-ke-kiri di sebagian
         * peramban. Nilai mutlaknya membuat perhitungan tepi berlaku sama untuk
         * kedua arah.
         */
        const offset = Math.abs(track.scrollLeft);
        const maximum = track.scrollWidth - track.clientWidth;

        setAtStart(offset <= EDGE_SLACK);
        setAtEnd(offset >= maximum - EDGE_SLACK);
    }, []);

    useEffect(() => {
        syncEdges();

        const track = trackRef.current;

        if (track === null) {
            return;
        }

        const observer = new ResizeObserver(syncEdges);
        observer.observe(track);

        return () => observer.disconnect();
    }, [syncEdges, children]);

    const scrollByPage = useCallback((direction: 1 | -1) => {
        const track = trackRef.current;

        if (track === null) {
            return;
        }

        const rtl = getComputedStyle(track).direction === 'rtl';
        const distance = track.clientWidth * SCROLL_RATIO * (rtl ? -1 : 1);

        track.scrollBy({
            left: distance * direction,
            behavior: scrollBehavior(),
        });
    }, []);

    return (
        <Section
            eyebrow={eyebrow}
            title={title}
            description={description}
            action={
                <div className="flex items-center gap-2">
                    <Button
                        type="button"
                        variant="control"
                        size="icon"
                        aria-label={`${unit} sebelumnya`}
                        disabled={atStart}
                        onClick={() => scrollByPage(-1)}
                        className="rounded-full border border-border/70 rtl:rotate-180"
                    >
                        <ChevronLeft />
                    </Button>
                    <Button
                        type="button"
                        variant="control"
                        size="icon"
                        aria-label={`${unit} berikutnya`}
                        disabled={atEnd}
                        onClick={() => scrollByPage(1)}
                        className="rounded-full border border-border/70 rtl:rotate-180"
                    >
                        <ChevronRight />
                    </Button>
                </div>
            }
        >
            {/*
             * Rel digeser sampai ke tepi layar lewat margin negatif, lalu
             * padding mengembalikan kartu pertama sejajar dengan judul. Tanpa
             * itu, kartu terakhir terpotong rapi di batas kolom dan tidak ada
             * isyarat bahwa deretnya masih berlanjut.
             */}
            <div
                ref={trackRef}
                onScroll={syncEdges}
                className="-mx-6 flex snap-x snap-mandatory [scrollbar-width:none] gap-4 overflow-x-auto px-6 pb-2 [&::-webkit-scrollbar]:hidden"
            >
                {children}
            </div>
        </Section>
    );
}
