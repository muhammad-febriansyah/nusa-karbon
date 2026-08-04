import { Link } from '@inertiajs/react';
import { ChevronDown, MessageSquareQuote, Search } from 'lucide-react';
import { useMemo, useState } from 'react';
import type { ReactNode } from 'react';
import PageHeader from '@/components/public/page-header';
import SeoHead from '@/components/public/seo-head';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { containerCalculator, contact, home } from '@/routes/public';
import type {
    PublicPageBlock,
    PublicQcProtocol,
    PublicShippingPartner,
} from '@/types';

type Faq = {
    id: number;
    category: string;
    question: string | null;
    answer: string | null;
};

type Props = {
    blocks: Record<string, PublicPageBlock>;
    qcProtocols: PublicQcProtocol[];
    faqs: Faq[];
    shippingPartners: PublicShippingPartner[];
};

/**
 * Blok syarat dagang yang ditampilkan berdampingan, mengikuti urutan
 * pertanyaan pembeli: berapa minimalnya, bayarnya bagaimana, kirimnya
 * bagaimana, dokumennya apa.
 */
const TERM_KEYS = ['moq', 'payment_terms', 'shipping_terms', 'documents'];

/**
 * Protokol yang tampil sebelum daftarnya perlu dibuka.
 *
 * Sepuluh teratas pada urutan admin adalah ambang yang benar-benar dicocokkan
 * pembeli dengan COA — kadar air, kadar abu, fixed carbon, nilai kalor, lama
 * bakar. Dua puluh sisanya kontrol proses yang dibaca sekali, bukan diperiksa
 * per pengiriman, dan menampilkan ketiga puluhnya sekaligus membuat sepuluh
 * yang penting tenggelam di tengah dinding baris.
 */
const PROTOCOL_PREVIEW = 10;

type Section = { id: string; name: string };

function SectionBlock({
    id,
    title,
    description,
    children,
}: {
    id: string;
    title: string;
    description?: string;
    children: ReactNode;
}) {
    return (
        <section
            id={id}
            className="scroll-mt-32 border-t border-border/70 py-12"
        >
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
 * Satu pertanyaan yang bisa dibuka.
 *
 * Memakai `details`/`summary` bawaan peramban, bukan akordeon yang digerakkan
 * state. Pengunjung bisa membukanya sebelum JavaScript selesai dimuat, isinya
 * ikut terjaring pencarian teks peramban meski sedang tertutup, dan navigasi
 * papan ketik sudah benar tanpa satu baris pun kode aksesibilitas.
 */
function FaqItem({ faq }: { faq: Faq }) {
    return (
        <details className="group rounded-panel border border-border/70 bg-card">
            <summary className="flex cursor-pointer list-none items-start justify-between gap-4 p-5 text-start focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none [&::-webkit-details-marker]:hidden">
                <span className="min-w-0">
                    {/*
                     * Kategorinya jadi keterangan di atas pertanyaan, bukan
                     * judul kelompok tersendiri. Tiga puluh kategori untuk tiga
                     * puluh tujuh pertanyaan berarti hampir tiap judul hanya
                     * menaungi satu jawaban.
                     */}
                    <span className="block text-xs text-muted-foreground">
                        {faq.category}
                    </span>
                    <span className="mt-0.5 block font-medium">
                        {faq.question}
                    </span>
                </span>

                <ChevronDown
                    aria-hidden
                    className="mt-1 size-4 shrink-0 text-muted-foreground transition-transform duration-200 group-open:rotate-180"
                />
            </summary>

            {faq.answer && (
                <div className="border-t border-border/70 px-5 py-4 text-sm leading-relaxed text-muted-foreground">
                    {faq.answer}
                </div>
            )}
        </details>
    );
}

function ProtocolRow({
    protocol,
    showBasis,
}: {
    protocol: PublicQcProtocol;
    showBasis: boolean;
}) {
    return (
        <tr>
            <td className="px-5 py-3 text-sm font-medium">
                {protocol.title}
                {protocol.description && (
                    <span className="mt-0.5 block text-xs font-normal text-muted-foreground">
                        {protocol.description}
                    </span>
                )}
            </td>

            {showBasis && (
                <td className="px-5 py-3 text-sm whitespace-nowrap text-muted-foreground">
                    {protocol.result_label ?? '—'}
                </td>
            )}

            {/*
             * Boleh melipat di ponsel. Dipaksa satu baris, "Min 7.000 kcal/kg"
             * mendorong kolomnya keluar layar dan angkanya — satu-satunya isi
             * yang dicari pembaca di tabel ini — jadi harus digulir mendatar
             * dulu untuk dilihat.
             */}
            <td
                dir="ltr"
                className="px-5 py-3 text-end font-mono text-sm font-medium tabular-nums md:whitespace-nowrap"
            >
                {protocol.result_value ?? '—'}
            </td>
        </tr>
    );
}

/**
 * Info ekspor.
 *
 * Menjawab pertanyaan yang muncul setelah pembeli puas dengan produknya: MOQ,
 * termin bayar, termin kirim, dokumen, ambang mutu, lalu sisa pertanyaan yang
 * paling sering berulang. Seluruh isinya dari admin.
 *
 * Halaman ini yang paling panjang di situs — tiga puluh protokol mutu, tiga
 * puluh tujuh pertanyaan, tiga puluh mitra pelayaran. Karena itu ia punya
 * bilah lompat yang menempel di atas, daftar protokol yang menampilkan sepuluh
 * dulu, dan kotak cari di bagian pertanyaan. Tidak ada isi yang disembunyikan;
 * yang diubah hanya berapa banyak yang harus dilewati sebelum menemukan satu
 * hal yang dicari.
 */
export default function ExportInfo({
    blocks,
    qcProtocols,
    faqs,
    shippingPartners,
}: Props) {
    const [showAllProtocols, setShowAllProtocols] = useState(false);
    const [query, setQuery] = useState('');

    const terms = TERM_KEYS.map((key) => blocks[key]).filter(
        (block): block is PublicPageBlock => block !== undefined,
    );

    /*
     * Kolom "Dasar" hanya berguna kalau isinya berbeda antar baris. Pada data
     * sekarang ketiga puluh barisnya bertuliskan hal yang sama, dan kolom yang
     * mengulang satu kata tiga puluh kali memakan sepertiga lebar tabel tanpa
     * menambah satu pun keterangan. Kalau admin mulai memakai dasar yang
     * berbeda, kolomnya kembali dengan sendirinya.
     */
    const basisValues = new Set(
        qcProtocols.map((protocol) => protocol.result_label ?? ''),
    );
    const sharedBasis =
        basisValues.size === 1 ? [...basisValues][0] || null : null;
    const showBasis = sharedBasis === null && basisValues.size > 0;

    const visibleProtocols = showAllProtocols
        ? qcProtocols
        : qcProtocols.slice(0, PROTOCOL_PREVIEW);
    const hiddenProtocolCount = qcProtocols.length - visibleProtocols.length;

    const matchedFaqs = useMemo(() => {
        const needle = query.trim().toLowerCase();

        if (needle === '') {
            return faqs;
        }

        return faqs.filter((faq) =>
            [faq.question, faq.answer, faq.category]
                .filter((part): part is string => part !== null)
                .some((part) => part.toLowerCase().includes(needle)),
        );
    }, [faqs, query]);

    const sections: Section[] = [
        terms.length > 0 ? { id: 'syarat', name: 'Syarat dagang' } : null,
        qcProtocols.length > 0 ? { id: 'mutu', name: 'Standar mutu' } : null,
        faqs.length > 0 ? { id: 'tanya', name: 'Pertanyaan' } : null,
        shippingPartners.length > 0
            ? { id: 'mitra', name: 'Mitra pelayaran' }
            : null,
    ].filter((section): section is Section => section !== null);

    return (
        <>
            <SeoHead
                title="Info Ekspor"
                description={
                    blocks.intro?.content ??
                    'Syarat pemesanan, termin pembayaran dan pengiriman, dokumen ekspor, serta standar mutu briket arang batok kelapa.'
                }
            />

            <PageHeader
                eyebrow="Info Ekspor"
                title={blocks.intro?.title ?? 'Informasi ekspor'}
                breadcrumbs={[{ name: 'Beranda', href: home() }]}
                description={blocks.intro?.content}
            />

            {/*
             * Bilah lompat menempel di bawah navbar mengambang. Halaman ini
             * sepuluh layar tingginya; tanpa penanda posisi, satu-satunya cara
             * mencapai bagian pertanyaan adalah menggulir melewati tiga puluh
             * baris protokol setiap kali.
             */}
            {sections.length > 1 && (
                <nav
                    aria-label="Bagian halaman"
                    className="sticky top-[var(--public-header-height)] z-30 border-b border-border/70 bg-background/85 backdrop-blur"
                >
                    <ul className="mx-auto flex w-full max-w-7xl gap-1 overflow-x-auto px-6 py-2.5">
                        {sections.map((section) => (
                            <li key={section.id}>
                                <a
                                    href={`#${section.id}`}
                                    className="block rounded-full px-4 py-1.5 text-sm font-medium whitespace-nowrap text-muted-foreground transition-colors hover:bg-muted hover:text-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                >
                                    {section.name}
                                </a>
                            </li>
                        ))}
                    </ul>
                </nav>
            )}

            <div className="mx-auto w-full max-w-7xl px-6">
                {terms.length > 0 && (
                    <section id="syarat" className="scroll-mt-32 py-12">
                        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                            {terms.map((block) => (
                                <div
                                    key={block.key}
                                    className="rounded-panel border border-border/70 bg-card p-5"
                                >
                                    <h2 className="text-sm font-semibold tracking-tight">
                                        {block.title}
                                    </h2>
                                    {block.content && (
                                        <p className="mt-2 text-sm leading-relaxed text-muted-foreground">
                                            {block.content}
                                        </p>
                                    )}
                                </div>
                            ))}
                        </div>
                    </section>
                )}

                {qcProtocols.length > 0 && (
                    <SectionBlock
                        id="mutu"
                        title="Standar mutu"
                        description={
                            sharedBasis === null
                                ? 'Ambang yang dijanjikan tiap batch. Hasil uji per pengiriman disertakan dalam COA, jadi angkanya bisa dicocokkan sendiri.'
                                : `Ambang yang dijanjikan tiap batch, seluruhnya menurut ${sharedBasis.toLowerCase()}. Hasil uji per pengiriman disertakan dalam COA, jadi angkanya bisa dicocokkan sendiri.`
                        }
                    >
                        {/*
                         * Tabel data selalu punya lebar minimum yang tidak bisa
                         * dikompres. Digulir di dalam wadahnya sendiri, jadi
                         * badan halaman tetap tidak pernah bergulir mendatar.
                         */}
                        <div className="rounded-panel overflow-x-auto border border-border/70 bg-card">
                            <table className="w-full table-fixed md:table-auto">
                                <thead className="border-b border-border/70">
                                    <tr>
                                        <th
                                            scope="col"
                                            className="w-3/5 px-5 py-3 text-start text-xs font-medium whitespace-nowrap text-muted-foreground md:w-auto"
                                        >
                                            Parameter
                                        </th>
                                        {showBasis && (
                                            <th
                                                scope="col"
                                                className="px-5 py-3 text-start text-xs font-medium whitespace-nowrap text-muted-foreground"
                                            >
                                                Dasar
                                            </th>
                                        )}
                                        <th
                                            scope="col"
                                            className="w-2/5 px-5 py-3 text-end text-xs font-medium whitespace-nowrap text-muted-foreground md:w-auto"
                                        >
                                            Ambang
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-border/70">
                                    {visibleProtocols.map((protocol) => (
                                        <ProtocolRow
                                            key={protocol.id}
                                            protocol={protocol}
                                            showBasis={showBasis}
                                        />
                                    ))}
                                </tbody>
                            </table>
                        </div>

                        {hiddenProtocolCount > 0 && (
                            <button
                                type="button"
                                onClick={() => setShowAllProtocols(true)}
                                className="mt-4 inline-flex items-center gap-2 text-sm font-medium text-primary underline-offset-4 transition-colors hover:underline focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
                            >
                                Tampilkan{' '}
                                <span className="tabular-nums">
                                    {hiddenProtocolCount}
                                </span>{' '}
                                protokol lainnya
                                <ChevronDown aria-hidden className="size-4" />
                            </button>
                        )}
                    </SectionBlock>
                )}

                {faqs.length > 0 && (
                    <SectionBlock
                        id="tanya"
                        title="Pertanyaan yang sering diajukan"
                        description="Ketik kata kuncinya — misalnya “dokumen”, “sampel”, atau nama pasar tujuan."
                    >
                        <div className="max-w-md space-y-2">
                            <Label htmlFor="cari-faq">Cari pertanyaan</Label>
                            <div className="relative">
                                <Search
                                    aria-hidden
                                    className="pointer-events-none absolute start-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground"
                                />
                                <Input
                                    id="cari-faq"
                                    type="search"
                                    value={query}
                                    onChange={(event) =>
                                        setQuery(event.target.value)
                                    }
                                    placeholder="dokumen ekspor"
                                    className="h-11 ps-9"
                                />
                            </div>
                        </div>

                        <p className="mt-4 text-sm text-muted-foreground">
                            <span className="tabular-nums">
                                {matchedFaqs.length}
                            </span>{' '}
                            dari{' '}
                            <span className="tabular-nums">{faqs.length}</span>{' '}
                            pertanyaan
                        </p>

                        {matchedFaqs.length === 0 ? (
                            <p className="rounded-panel mt-4 border border-border/70 bg-card p-6 text-sm text-muted-foreground">
                                Tidak ada pertanyaan yang cocok.{' '}
                                <Link
                                    href={contact()}
                                    className="font-medium text-primary underline-offset-4 hover:underline"
                                >
                                    Tanyakan langsung
                                </Link>{' '}
                                — pertanyaan yang berulang kami tambahkan ke
                                daftar ini.
                            </p>
                        ) : (
                            <div className="mt-4 space-y-3">
                                {matchedFaqs.map((faq) => (
                                    <FaqItem key={faq.id} faq={faq} />
                                ))}
                            </div>
                        )}
                    </SectionBlock>
                )}

                {shippingPartners.length > 0 && (
                    <SectionBlock
                        id="mitra"
                        title="Mitra pelayaran"
                        description="Jalur yang biasa dipakai untuk keberangkatan dari pelabuhan Surabaya dan Jakarta."
                    >
                        {/*
                         * Sebagai kartu berkisi, tiga puluh mitra jadi enam
                         * baris kotak yang tingginya menyaingi seluruh bagian
                         * standar mutu — padahal isinya hanya nama. Sebagai
                         * deretan lencana yang membungkus, keseluruhannya muat
                         * dalam beberapa baris dan tetap terbaca satu per satu.
                         */}
                        <ul className="flex flex-wrap gap-2">
                            {shippingPartners.map((partner) => (
                                <li
                                    key={partner.id}
                                    className="flex items-center gap-2 rounded-full border border-border/70 bg-card py-2 ps-3 pe-4 text-sm"
                                >
                                    {partner.logo_url && (
                                        <img
                                            src={partner.logo_url}
                                            alt=""
                                            loading="lazy"
                                            decoding="async"
                                            className="h-5 w-auto shrink-0 object-contain"
                                        />
                                    )}
                                    <span className="whitespace-nowrap">
                                        {partner.name}
                                    </span>
                                </li>
                            ))}
                        </ul>
                    </SectionBlock>
                )}

                <section className="border-t border-border/70 py-12">
                    <div className="flex flex-wrap items-center gap-x-7 gap-y-4">
                        <Button asChild variant="create" size="lg">
                            <Link href={contact()}>
                                <MessageSquareQuote />
                                Minta penawaran
                            </Link>
                        </Button>

                        <Link
                            href={containerCalculator()}
                            className="text-sm font-medium underline-offset-8 transition-colors hover:text-primary hover:underline"
                        >
                            Hitung muatan kontainer dulu
                        </Link>
                    </div>
                </section>
            </div>
        </>
    );
}
