import { Link } from '@inertiajs/react';
import { ArrowRight, MessageSquareQuote } from 'lucide-react';
import ShapeDrawing from '@/components/public/home/shape-drawing';
import { Button } from '@/components/ui/button';
import { usePublicSettings } from '@/hooks/use-public-settings';
import { cn } from '@/lib/utils';
import { containerCalculator, contact } from '@/routes/public';
import type { PublicPageBlock, PublicSpecSheet } from '@/types';

type Props = {
    block?: PublicPageBlock;
    specSheet: PublicSpecSheet | null;
};

/** Satu keterangan pada baris identitas di atas judul. */
type MetaItem = { label: string; value: string };

/**
 * Lebar pita angka mengikuti jumlah spesifikasi yang dikirim admin. Kelasnya
 * ditulis lengkap karena Tailwind memindai kode sebagai teks — kelas yang
 * dirangkai saat runtime tidak akan pernah ikut ter-generate.
 */
const SPEC_COLUMNS: Record<number, string> = {
    1: 'sm:grid-cols-1',
    2: 'sm:grid-cols-2',
    3: 'sm:grid-cols-3',
    4: 'sm:grid-cols-2 lg:grid-cols-4',
};

/** Jeda kemunculan antar elemen hero saat halaman dibuka, dalam detik. */
const RISE_STEP = 0.07;

/**
 * Latar sementara, dipakai hanya selama blok hero belum punya gambar sendiri.
 *
 * SEMENTARA — hapus konstanta ini begitu admin mengunggah gambar hero; sejak
 * saat itu `block.image_url` yang dipakai dan situs tidak lagi bergantung pada
 * host luar.
 *
 * Yang dipilih tekstur arang dingin, bukan bara menyala. Nilai jual briket
 * batok justru ketiadaan api: sulfur 0,1% max, tanpa aroma. Latar penuh api
 * menjanjikan hal yang berlawanan dengan isi lembar ujinya sendiri.
 *
 * Foto: Unsplash, photo-1703359905448-6ceada4db44e.
 */
const PLACEHOLDER_BACKDROP =
    'https://images.unsplash.com/photo-1703359905448-6ceada4db44e?w=1920&q=70&fm=jpg&auto=format&fit=crop';

/**
 * Hero beranda.
 *
 * Halaman ini dibaca importir yang sedang membandingkan pemasok, dan
 * perbandingan itu selalu dimulai dari angka: kadar karbon, abu, nilai kalor,
 * lama bakar. Karena itu isinya disusun seperti lembar uji — identitas lot,
 * nama barang, lalu keempat angka itu.
 *
 * Yang berubah dari versi sebelumnya hanya permukaannya. Semuanya dulu ditulis
 * huruf besar dengan garis rambut dan sudut siku, dan itu bahasa cetak
 * industrial — tepat, tetapi terbaca tua. Angkanya kini duduk di kartu
 * melengkung yang menumpang di batas pita, dengan huruf normal; substansinya
 * sama persis.
 *
 * Seluruh angkanya berasal dari produk acuan di admin. Tidak ada nilai contoh
 * yang ditanam di kode, jadi apa yang terbaca pengunjung selalu sama dengan
 * yang tersimpan.
 */
export default function HeroSection({ block, specSheet }: Props) {
    const { text, number } = usePublicSettings();

    const foundedYear = number('founded_year');
    const meta: MetaItem[] = [
        { label: 'Ref', value: specSheet?.code ?? null },
        { label: 'HS', value: text('hs_code') },
        { label: 'Asal', value: text('country') },
        {
            label: 'Sejak',
            value: foundedYear === null ? null : String(foundedYear),
        },
    ].filter((item): item is MetaItem => item.value !== null);

    const specs = specSheet?.specs ?? [];

    /*
     * SEMENTARA — placeholder sengaja menang atas gambar dari admin selama arah
     * desain ini masih dinilai. Kembalikan ke baris di bawah begitu foto hero
     * yang benar sudah diunggah:
     *
     *     const backdrop = block?.image_url ?? PLACEHOLDER_BACKDROP;
     */
    const backdrop = PLACEHOLDER_BACKDROP;

    return (
        <section>
            {/*
             * Margin negatif menarik pita naik sampai ke belakang navbar, lalu
             * padding dengan besaran yang sama mengembalikan isinya ke bawah
             * navbar. Padding bawahnya dilebihkan untuk memberi ruang kartu
             * angka yang menumpang di batas pita.
             */}
            <div className="surface-plate-ink surface-hero-plate relative -mt-[var(--public-header-height)] overflow-hidden pt-[var(--public-header-height)] pb-32 lg:pb-36">
                <img
                    src={backdrop}
                    alt=""
                    aria-hidden
                    /*
                     * Elemen terbesar di layar pertama, jadi didahulukan di
                     * antrean unduh. `absolute` di dalam pembungkus yang
                     * tingginya ditentukan teks membuatnya tidak pernah
                     * menggeser tata letak saat selesai dimuat.
                     *
                     * Hanya `grayscale`. Di atas pita gelap fotonya tidak perlu
                     * dimanipulasi sama sekali — yang dibuang cuma semburat
                     * cokelat kayu bakarnya, karena bertabrakan dengan oranye
                     * merek yang dipakai tepat satu kali di halaman ini.
                     */
                    fetchPriority="high"
                    decoding="async"
                    className="absolute inset-0 size-full object-cover grayscale"
                />

                {/*
                 * Dua lapis peredup, keduanya memakai warna pita — bukan warna
                 * halaman. Yang rata menjaga kontras teks di seluruh bidang;
                 * yang bergradien menggelapkan sisi teks lebih jauh sehingga
                 * judul tetap terbaca sementara sisi kanan menyisakan foto.
                 */}
                <div
                    aria-hidden
                    className="absolute inset-0 bg-background/45"
                />
                <div
                    aria-hidden
                    className="absolute inset-0 from-background via-background/80 to-background/15 ltr:bg-linear-to-r rtl:bg-linear-to-l"
                />

                <div className="relative mx-auto grid w-full max-w-7xl gap-12 px-6 pt-10 lg:grid-cols-[minmax(0,1.5fr)_minmax(0,1fr)] lg:items-center lg:gap-16 lg:pt-16">
                    <div>
                        {/*
                         * Baris identitas dibungkus kaca. Foto di belakangnya
                         * ikut buram tepat di bawah teksnya saja, jadi data lot
                         * terbaca tanpa perlu menggelapkan seluruh bidang.
                         */}
                        {meta.length > 0 && (
                            <dl
                                className="flex w-fit animate-rise flex-wrap items-center gap-x-5 gap-y-1 rounded-full border border-border/70 bg-background/30 px-4 py-2 text-[13px] backdrop-blur-md"
                                style={{ animationDelay: '0s' }}
                            >
                                {meta.map((item) => (
                                    <div
                                        key={item.label}
                                        className="flex items-baseline gap-1.5"
                                    >
                                        <dt className="text-muted-foreground">
                                            {item.label}
                                        </dt>
                                        <dd
                                            className="font-mono text-xs tabular-nums"
                                            dir="ltr"
                                        >
                                            {item.value}
                                        </dd>
                                    </div>
                                ))}
                            </dl>
                        )}

                        <h1
                            className="mt-7 animate-rise font-display text-[clamp(2.5rem,5.4vw,4.25rem)] leading-[1.02] font-bold tracking-[-0.035em] text-balance"
                            style={{ animationDelay: `${RISE_STEP}s` }}
                        >
                            {block?.title ?? text('company_name')}
                        </h1>

                        {block?.content && (
                            <p
                                className="mt-6 max-w-lg animate-rise text-lg leading-relaxed text-muted-foreground"
                                style={{ animationDelay: `${RISE_STEP * 2}s` }}
                            >
                                {block.content}
                            </p>
                        )}

                        <div
                            className="mt-9 flex animate-rise flex-wrap items-center gap-x-7 gap-y-4"
                            style={{ animationDelay: `${RISE_STEP * 3}s` }}
                        >
                            <Button asChild variant="create" size="lg">
                                <Link href={contact()}>
                                    <MessageSquareQuote />
                                    Minta sampel &amp; COA
                                </Link>
                            </Button>

                            {/*
                             * Kalkulator adalah halaman lain, bukan aksi — jadi
                             * bentuknya tautan, bukan tombol kedua berwarna
                             * beda. Dua tombol penuh warna bersebelahan membuat
                             * pengunjung menimbang dua hal sekaligus dan
                             * akhirnya tidak menekan keduanya.
                             */}
                            <Link
                                href={containerCalculator()}
                                className="group inline-flex items-center gap-2 text-sm font-medium underline-offset-8 transition-colors hover:text-primary hover:underline focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
                            >
                                Hitung muatan kontainer
                                <ArrowRight className="size-4 transition-transform group-hover:translate-x-0.5" />
                            </Link>
                        </div>
                    </div>

                    {specSheet && (
                        <figure
                            className="rounded-panel animate-rise border border-border/70 bg-background/25 p-7 backdrop-blur-md"
                            style={{ animationDelay: `${RISE_STEP * 4}s` }}
                        >
                            <ShapeDrawing
                                shape={specSheet.shape}
                                sizeMm={specSheet.size_mm}
                                className="mx-auto max-w-[15rem] text-foreground"
                            />

                            <figcaption className="mt-4 border-t border-border/70 pt-4 text-sm">
                                <span className="block font-medium">
                                    {specSheet.name ?? specSheet.code}
                                </span>
                                <span
                                    className="mt-0.5 block font-mono text-xs text-muted-foreground"
                                    dir="ltr"
                                >
                                    {[
                                        specSheet.shape,
                                        specSheet.size_mm === null
                                            ? null
                                            : `${specSheet.size_mm} mm`,
                                    ]
                                        .filter(
                                            (part): part is string =>
                                                part !== null,
                                        )
                                        .join(' · ')}
                                </span>
                            </figcaption>
                        </figure>
                    )}
                </div>
            </div>

            {/*
             * Kartu angka menumpang di batas antara pita dan kertas. Tumpangan
             * inilah yang menyatukan keduanya — tanpa itu, halaman terbaca
             * sebagai dua potongan yang kebetulan bersebelahan.
             */}
            {specs.length > 0 && (
                <div className="relative z-10 -mt-20 pb-4 lg:-mt-24 lg:pb-6">
                    <dl
                        className={cn(
                            'mx-auto grid w-full max-w-7xl grid-cols-1 gap-4 px-6',
                            SPEC_COLUMNS[specs.length] ??
                                'sm:grid-cols-2 lg:grid-cols-4',
                        )}
                    >
                        {specs.map((spec, index) => (
                            <div
                                key={spec.parameter}
                                /*
                                 * Bayangan versi kertas, bukan versi pita.
                                 * Kartunya memang menumpang di batas keduanya,
                                 * tetapi bagian bawahnya yang menempel di
                                 * kertas — bayangan pekat di sana akan terlihat
                                 * seperti noda.
                                 */
                                className="rounded-panel border border-border/70 bg-card p-6 shadow-card"
                            >
                                <dt className="text-sm font-medium text-muted-foreground">
                                    {spec.parameter}
                                </dt>
                                <dd
                                    dir="ltr"
                                    className={cn(
                                        /*
                                         * Angka besarnya memakai Archivo, bukan
                                         * mono. Pada mono setiap tanda menempati
                                         * satu sel penuh, sehingga "7.400"
                                         * terbaca "7 . 400" — titik ribuannya
                                         * memisah alih-alih mengelompokkan.
                                         * `tabular-nums` tetap menjaga lebar
                                         * digitnya seragam antar kartu.
                                         */
                                        'mt-3 font-display text-[clamp(2rem,3.6vw,2.75rem)] leading-none font-semibold tracking-[-0.02em] tabular-nums',
                                        /*
                                         * Warna aksen dipakai tepat satu kali di
                                         * halaman ini, pada angka yang paling
                                         * dulu ditandai admin. Begitu dipakai
                                         * pada empat angka sekaligus, tidak ada
                                         * lagi yang menonjol.
                                         */
                                        index === 0
                                            ? 'text-primary'
                                            : 'text-foreground',
                                    )}
                                >
                                    {spec.value}
                                </dd>
                                {spec.unit && (
                                    <p
                                        dir="ltr"
                                        className="mt-2 font-mono text-xs text-muted-foreground"
                                    >
                                        {spec.unit}
                                    </p>
                                )}
                            </div>
                        ))}
                    </dl>
                </div>
            )}
        </section>
    );
}
