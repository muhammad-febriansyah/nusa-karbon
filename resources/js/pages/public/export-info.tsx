import { Link } from '@inertiajs/react';
import { ChevronDown, MessageSquareQuote } from 'lucide-react';
import type { ReactNode } from 'react';
import PageHeader from '@/components/public/page-header';
import SeoHead from '@/components/public/seo-head';
import { Button } from '@/components/ui/button';
import { containerCalculator, contact, home } from '@/routes/public';
import type {
    PublicPageBlock,
    PublicQcProtocol,
    PublicShippingPartner,
} from '@/types';

type FaqGroup = {
    category: string;
    items: { id: number; question: string | null; answer: string | null }[];
};

type Props = {
    blocks: Record<string, PublicPageBlock>;
    qcProtocols: PublicQcProtocol[];
    faqGroups: FaqGroup[];
    shippingPartners: PublicShippingPartner[];
};

/**
 * Blok syarat dagang yang ditampilkan berdampingan, mengikuti urutan
 * pertanyaan pembeli: berapa minimalnya, bayarnya bagaimana, kirimnya
 * bagaimana, dokumennya apa.
 */
const TERM_KEYS = [
    'moq',
    'payment_terms',
    'shipping_terms',
    'documents',
] as const;

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
 * Satu pertanyaan yang bisa dibuka.
 *
 * Memakai `details`/`summary` bawaan peramban, bukan akordeon yang digerakkan
 * state. Pengunjung bisa membukanya sebelum JavaScript selesai dimuat, isinya
 * ikut terjaring pencarian teks peramban meski sedang tertutup, dan navigasi
 * papan ketik sudah benar tanpa satu baris pun kode aksesibilitas.
 */
function FaqItem({
    question,
    answer,
}: {
    question: string;
    answer: string | null;
}) {
    return (
        <details className="group rounded-panel border border-border/70 bg-card">
            <summary className="flex cursor-pointer list-none items-start justify-between gap-4 p-5 text-start font-medium focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none [&::-webkit-details-marker]:hidden">
                <span>{question}</span>
                <ChevronDown
                    aria-hidden
                    className="mt-0.5 size-4 shrink-0 text-muted-foreground transition-transform duration-200 group-open:rotate-180"
                />
            </summary>

            {answer && (
                <div className="border-t border-border/70 px-5 py-4 text-sm leading-relaxed text-muted-foreground">
                    {answer}
                </div>
            )}
        </details>
    );
}

/**
 * Info ekspor.
 *
 * Menjawab pertanyaan yang muncul setelah pembeli puas dengan produknya: MOQ,
 * termin bayar, termin kirim, dokumen, ambang mutu, lalu sisa pertanyaan yang
 * paling sering berulang. Seluruh isinya dari admin.
 */
export default function ExportInfo({
    blocks,
    qcProtocols,
    faqGroups,
    shippingPartners,
}: Props) {
    const terms = TERM_KEYS.map((key) => blocks[key]).filter(
        (block): block is PublicPageBlock => block !== undefined,
    );

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

            <div className="mx-auto w-full max-w-7xl px-6">
                {terms.length > 0 && (
                    <div className="grid gap-4 py-12 sm:grid-cols-2 lg:grid-cols-4">
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
                )}

                {qcProtocols.length > 0 && (
                    <SectionBlock
                        title="Standar mutu"
                        description="Ambang yang dijanjikan tiap batch. Hasil uji per pengiriman disertakan dalam COA, jadi angkanya bisa dicocokkan sendiri."
                    >
                        {/*
                         * Tabel data selalu punya lebar minimum yang tidak bisa
                         * dikompres. Digulir di dalam wadahnya sendiri, jadi
                         * badan halaman tetap tidak pernah bergulir mendatar.
                         */}
                        <div className="rounded-panel overflow-x-auto border border-border/70 bg-card">
                            <table className="w-full">
                                <thead className="border-b border-border/70">
                                    <tr>
                                        <th
                                            scope="col"
                                            className="px-5 py-3 text-start text-xs font-medium whitespace-nowrap text-muted-foreground"
                                        >
                                            Parameter
                                        </th>
                                        <th
                                            scope="col"
                                            className="px-5 py-3 text-start text-xs font-medium whitespace-nowrap text-muted-foreground"
                                        >
                                            Dasar
                                        </th>
                                        <th
                                            scope="col"
                                            className="px-5 py-3 text-end text-xs font-medium whitespace-nowrap text-muted-foreground"
                                        >
                                            Ambang
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-border/70">
                                    {qcProtocols.map((protocol) => (
                                        <tr key={protocol.id}>
                                            <td className="px-5 py-3 text-sm font-medium">
                                                {protocol.title}
                                                {protocol.description && (
                                                    <span className="mt-0.5 block text-xs font-normal text-muted-foreground">
                                                        {protocol.description}
                                                    </span>
                                                )}
                                            </td>
                                            <td className="px-5 py-3 text-sm whitespace-nowrap text-muted-foreground">
                                                {protocol.result_label ?? '—'}
                                            </td>
                                            <td
                                                dir="ltr"
                                                className="px-5 py-3 text-end font-mono text-sm font-medium whitespace-nowrap tabular-nums"
                                            >
                                                {protocol.result_value ?? '—'}
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </SectionBlock>
                )}

                {faqGroups.length > 0 && (
                    <SectionBlock
                        title="Pertanyaan yang sering diajukan"
                        description="Dikelompokkan menurut tahap: dari memilih produk sampai dokumen keberangkatan."
                    >
                        <div className="space-y-10">
                            {faqGroups.map((group) => (
                                <div key={group.category}>
                                    <h3 className="text-sm font-semibold tracking-tight text-muted-foreground">
                                        {group.category}
                                    </h3>

                                    <div className="mt-3 space-y-3">
                                        {group.items.map(
                                            (item) =>
                                                item.question && (
                                                    <FaqItem
                                                        key={item.id}
                                                        question={item.question}
                                                        answer={item.answer}
                                                    />
                                                ),
                                        )}
                                    </div>
                                </div>
                            ))}
                        </div>
                    </SectionBlock>
                )}

                {shippingPartners.length > 0 && (
                    <SectionBlock
                        title="Mitra pelayaran"
                        description="Jalur yang biasa dipakai untuk keberangkatan dari pelabuhan Surabaya dan Jakarta."
                    >
                        <ul className="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
                            {shippingPartners.map((partner) => (
                                <li
                                    key={partner.id}
                                    className="rounded-panel flex items-center gap-3 border border-border/70 bg-card p-4"
                                >
                                    {partner.logo_url && (
                                        <img
                                            src={partner.logo_url}
                                            alt=""
                                            loading="lazy"
                                            decoding="async"
                                            className="h-6 w-auto shrink-0 object-contain"
                                        />
                                    )}
                                    <span className="min-w-0 truncate text-sm">
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
