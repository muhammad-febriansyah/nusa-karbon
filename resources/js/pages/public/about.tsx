import { Link } from '@inertiajs/react';
import {
    ArrowDown,
    ChevronLeft,
    ChevronRight,
    Linkedin,
    MessageSquareQuote,
} from 'lucide-react';
import type { ReactNode } from 'react';
import PageHeader from '@/components/public/page-header';
import SeoHead from '@/components/public/seo-head';
import { Button } from '@/components/ui/button';
import { usePublicSettings } from '@/hooks/use-public-settings';
import { cn } from '@/lib/utils';
import { about, contact, home, verifyRepresentative } from '@/routes/public';
import type {
    PublicCertificate,
    PublicPageBlock,
    PublicProductionStep,
    PublicTeamMember,
} from '@/types';
import type { PaginatedMeta } from '@/types/pagination';

type Props = {
    blocks: Record<string, PublicPageBlock>;
    team: { data: PublicTeamMember[]; meta: PaginatedMeta };
    certificates: PublicCertificate[];
    productionSteps: PublicProductionStep[];
};

/**
 * Tiap klaim di kepala halaman ditautkan ke bagian yang membuktikannya.
 *
 * Tiga kotak berisi kalimat yang saling mirip adalah tiga pernyataan yang tidak
 * bisa diperiksa siapa pun. Menempelkan satu angka nyata dari halaman ini
 * sendiri — dan tautan langsung ke daftarnya — mengubahnya dari klaim jadi
 * ringkasan bukti.
 */
const CLAIM_EVIDENCE: Record<string, { anchor: string; unit: string }> = {
    supply_chain_position: {
        anchor: 'proses',
        unit: 'tahap dikerjakan sendiri',
    },
    why_trader: { anchor: 'sertifikasi', unit: 'sertifikat & dokumen diurus' },
    commitment: { anchor: 'tim', unit: 'orang yang namanya bisa dicek' },
};

/**
 * Blok naratif, berurutan menjawab pertanyaan pembeli: kalian ini siapa, di
 * posisi mana dalam rantai pasok, dan apa yang kalian janjikan.
 */
const NARRATIVE_KEYS = [
    'supply_chain_position',
    'why_trader',
    'commitment',
] as const;

const yearFormat = new Intl.DateTimeFormat('id-ID', {
    month: 'short',
    year: 'numeric',
});

function formatDate(value: string | null): string | null {
    return value === null ? null : yearFormat.format(new Date(value));
}

function Section({
    id,
    title,
    description,
    action,
    children,
}: {
    /** Jangkar tautan dari ringkasan bukti di kepala halaman. */
    id: string;
    title: string;
    description?: string;
    action?: ReactNode;
    children: ReactNode;
}) {
    return (
        /*
         * `scroll-mt` menyisakan ruang setinggi navbar yang menempel di atas.
         * Tanpa itu, melompat ke jangkar menaruh judul bagian tepat di bawah
         * navbar — tertutup persis oleh elemen yang selalu ada.
         */
        <section
            id={id}
            className="scroll-mt-24 border-t border-border/70 py-12"
        >
            <div className="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div className="max-w-2xl">
                    <h2 className="font-display text-2xl leading-tight font-bold tracking-[-0.02em]">
                        {title}
                    </h2>
                    {description && (
                        <p className="mt-2 text-sm leading-relaxed text-muted-foreground">
                            {description}
                        </p>
                    )}
                </div>
                {action && <div className="shrink-0">{action}</div>}
            </div>
            <div className="mt-6">{children}</div>
        </section>
    );
}

/**
 * Tentang kami, disusun sebagai berkas perusahaan.
 *
 * Pembacanya importir yang sedang memutuskan apakah aman mengirim uang ke
 * perusahaan yang belum pernah ditemuinya. Tugas halaman ini satu: membuktikan
 * perusahaan ini nyata dan bisa dimintai pertanggungjawaban.
 *
 * Karena itu tiap bagian berakhir di kolom yang bisa dicek ke pihak luar —
 * sertifikat punya nomor dan masa berlaku, orang punya jabatan dan tautan
 * LinkedIn, tahapan punya posisi dalam urutan. Susunan sebelumnya memakai tiga
 * kisi kartu yang sama persis, dan kartu justru menyembunyikan kolom-kolom itu.
 */
export default function About({
    blocks,
    team,
    certificates,
    productionSteps,
}: Props) {
    const { text, number } = usePublicSettings();

    const narrative = NARRATIVE_KEYS.map((key) => blocks[key]).filter(
        (block): block is PublicPageBlock => block !== undefined,
    );

    /*
     * Dihitung dari isi yang sedang ditampilkan, bukan dari angka yang ditulis
     * terpisah. Begitu admin menambah satu tahap produksi, angka di kepala
     * halaman ikut berubah tanpa ada yang perlu menyuntingnya.
     */
    const evidenceCount: Record<string, number> = {
        proses: productionSteps.length,
        sertifikasi: certificates.length,
        tim: team.meta.total,
    };

    const foundedYear = number('founded_year');
    const record = [
        { label: 'Badan hukum', value: text('legal_name') },
        {
            label: 'Beroperasi sejak',
            value: foundedYear === null ? null : String(foundedYear),
        },
        { label: 'HS Code', value: text('hs_code') },
        { label: 'Asal barang', value: text('country') },
    ].filter(
        (item): item is { label: string; value: string } => item.value !== null,
    );

    return (
        <>
            <SeoHead
                title="Tentang Kami"
                description={
                    blocks.company_profile?.content ?? text('about_short')
                }
            />

            <PageHeader
                eyebrow="Tentang Kami"
                title={blocks.company_profile?.title ?? 'Tentang kami'}
                description={
                    blocks.company_profile?.content ?? text('about_short')
                }
                breadcrumbs={[{ name: 'Beranda', href: home() }]}
            >
                {/*
                 * Identitas usaha dibuka lebih dulu, sebelum cerita apa pun.
                 * Pembeli yang belum pernah bertransaksi memeriksa hal ini
                 * duluan, dan menguburnya di bawah tiga paragraf berarti
                 * menyuruhnya mencari.
                 */}
                {record.length > 0 && (
                    <dl className="grid grid-cols-2 gap-x-8 gap-y-5 sm:grid-cols-4">
                        {record.map((item) => (
                            <div key={item.label}>
                                <dt className="text-xs text-muted-foreground">
                                    {item.label}
                                </dt>
                                <dd
                                    dir="ltr"
                                    className="mt-1 font-mono text-sm font-medium tabular-nums"
                                >
                                    {item.value}
                                </dd>
                            </div>
                        ))}
                    </dl>
                )}
            </PageHeader>

            <div className="mx-auto w-full max-w-7xl px-6">
                {narrative.length > 0 && (
                    <div className="grid gap-4 py-12 md:grid-cols-3">
                        {narrative.map((block) => {
                            const evidence = CLAIM_EVIDENCE[block.key];
                            const count =
                                evidence === undefined
                                    ? undefined
                                    : evidenceCount[evidence.anchor];

                            return (
                                <div
                                    key={block.key}
                                    className="rounded-panel flex flex-col border border-border/70 bg-card p-6"
                                >
                                    <h2 className="font-display text-lg leading-snug font-bold tracking-tight">
                                        {block.title}
                                    </h2>

                                    {block.content && (
                                        <p className="mt-3 text-sm leading-relaxed text-muted-foreground">
                                            {block.content}
                                        </p>
                                    )}

                                    {/*
                                     * Baris bukti hanya muncul kalau angkanya
                                     * memang ada. Klaim yang bagian bawahnya
                                     * menampilkan nol justru melemahkan dirinya
                                     * sendiri.
                                     */}
                                    {evidence !== undefined &&
                                        count !== undefined &&
                                        count > 0 && (
                                            <a
                                                href={`#${evidence.anchor}`}
                                                className="group mt-6 flex items-baseline gap-2 border-t border-border/70 pt-4 text-sm underline-offset-4 transition-colors focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
                                            >
                                                <span
                                                    dir="ltr"
                                                    className="font-display text-2xl leading-none font-semibold tabular-nums group-hover:text-primary"
                                                >
                                                    {count}
                                                </span>
                                                <span className="flex-1 text-muted-foreground group-hover:text-primary">
                                                    {evidence.unit}
                                                </span>
                                                <ArrowDown
                                                    aria-hidden
                                                    className="size-4 shrink-0 self-center text-muted-foreground transition-transform group-hover:translate-y-0.5 group-hover:text-primary"
                                                />
                                            </a>
                                        )}
                                </div>
                            );
                        })}
                    </div>
                )}

                {productionSteps.length > 0 && (
                    <Section
                        id="proses"
                        title="Yang kami kerjakan sendiri"
                        description="Urutan kerja dari batok mentah sampai kontainer berangkat. Tiap tahap dikerjakan dan dicatat sendiri, bukan diserahkan ke pihak ketiga."
                    >
                        {/*
                         * Rel bernomor satu arah, bukan kisi tiga kolom. Isinya
                         * urutan kerja, dan kisi memaksa mata membaca ke bawah
                         * sementara nomornya berjalan ke samping — urutannya
                         * hilang justru pada isi yang urutannya adalah
                         * informasi.
                         */}
                        <ol className="grid gap-x-10 sm:grid-cols-2">
                            {productionSteps.map((step, index) => (
                                <li
                                    key={step.id}
                                    className="flex gap-4 border-t border-border/70 py-4"
                                >
                                    <span
                                        dir="ltr"
                                        aria-hidden
                                        className="w-6 shrink-0 pt-0.5 font-mono text-xs text-muted-foreground tabular-nums"
                                    >
                                        {String(index + 1).padStart(2, '0')}
                                    </span>
                                    <div className="min-w-0">
                                        <h3 className="text-sm font-semibold tracking-tight">
                                            {step.title}
                                        </h3>
                                        {step.description && (
                                            <p className="mt-1 text-sm leading-relaxed text-muted-foreground">
                                                {step.description}
                                            </p>
                                        )}
                                    </div>
                                </li>
                            ))}
                        </ol>
                    </Section>
                )}

                {certificates.length > 0 && (
                    <Section
                        id="sertifikasi"
                        title="Sertifikasi & kepatuhan"
                        description="Nomor dan masa berlakunya dicantumkan supaya bisa dicocokkan langsung ke penerbitnya. Nomor tanpa masa berlaku tidak bisa diverifikasi."
                    >
                        {/*
                         * Tabel, bukan kartu. Yang dicari pembeli adalah nomor
                         * dan tanggalnya — kartu justru menaruh keduanya paling
                         * bawah dengan huruf terkecil.
                         */}
                        <div className="rounded-panel overflow-x-auto border border-border/70 bg-card">
                            <table className="w-full">
                                <thead className="border-b border-border/70">
                                    <tr>
                                        <th
                                            scope="col"
                                            className="px-5 py-3 text-start text-xs font-medium whitespace-nowrap text-muted-foreground"
                                        >
                                            Sertifikat
                                        </th>
                                        <th
                                            scope="col"
                                            className="px-5 py-3 text-start text-xs font-medium whitespace-nowrap text-muted-foreground"
                                        >
                                            Penerbit
                                        </th>
                                        <th
                                            scope="col"
                                            className="px-5 py-3 text-start text-xs font-medium whitespace-nowrap text-muted-foreground"
                                        >
                                            Nomor
                                        </th>
                                        <th
                                            scope="col"
                                            className="px-5 py-3 text-end text-xs font-medium whitespace-nowrap text-muted-foreground"
                                        >
                                            Berlaku sampai
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-border/70">
                                    {certificates.map((certificate) => (
                                        <tr key={certificate.id}>
                                            <td className="px-5 py-3 text-sm font-medium">
                                                {certificate.name}
                                            </td>
                                            <td className="px-5 py-3 text-sm whitespace-nowrap text-muted-foreground">
                                                {certificate.issuer ?? '—'}
                                            </td>
                                            <td
                                                dir="ltr"
                                                className="px-5 py-3 font-mono text-sm whitespace-nowrap"
                                            >
                                                {certificate.certificate_number ??
                                                    '—'}
                                            </td>
                                            <td
                                                dir="ltr"
                                                className="px-5 py-3 text-end font-mono text-sm whitespace-nowrap text-muted-foreground tabular-nums"
                                            >
                                                {formatDate(
                                                    certificate.expires_at,
                                                ) ?? '—'}
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </Section>
                )}

                {team.data.length > 0 && (
                    <Section
                        id="tim"
                        title="Tim"
                        description="Orang yang benar-benar menangani pesanan Anda. Profil LinkedIn dicantumkan supaya identitasnya bisa diperiksa sebelum uang berpindah."
                        action={<TeamPager meta={team.meta} />}
                    >
                        {/*
                         * Roster baris, bukan dinding wajah. Pembeli datang ke
                         * sini untuk memeriksa satu orang yang menghubunginya,
                         * bukan menelusuri tiga puluh foto — dan baris memuat
                         * jabatan, wilayah, serta tautan verifikasi sekaligus.
                         */}
                        <ul className="rounded-panel divide-y divide-border/70 border border-border/70 bg-card">
                            {team.data.map((member) => (
                                <li
                                    key={member.id}
                                    className="flex flex-wrap items-center gap-x-5 gap-y-3 p-5"
                                >
                                    <div className="size-11 shrink-0 overflow-hidden rounded-full border border-border/70 bg-muted">
                                        {member.photo_url && (
                                            <img
                                                src={member.photo_url}
                                                alt=""
                                                loading="lazy"
                                                decoding="async"
                                                className="size-full object-cover"
                                            />
                                        )}
                                    </div>

                                    <div className="min-w-0 flex-1">
                                        <p className="text-sm font-semibold tracking-tight">
                                            {member.name}
                                        </p>
                                        {member.position && (
                                            <p className="mt-0.5 text-sm text-muted-foreground">
                                                {member.position}
                                            </p>
                                        )}
                                    </div>

                                    {member.linkedin_url ? (
                                        <a
                                            href={member.linkedin_url}
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            className="inline-flex items-center gap-1.5 text-sm font-medium text-primary underline-offset-4 hover:underline focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
                                        >
                                            <Linkedin className="size-4" />
                                            Periksa profil
                                        </a>
                                    ) : (
                                        <span className="text-sm text-muted-foreground">
                                            Tanpa profil publik
                                        </span>
                                    )}
                                </li>
                            ))}
                        </ul>

                        <p className="mt-4 text-sm text-muted-foreground">
                            Menampilkan{' '}
                            <span className="tabular-nums">
                                {team.meta.from}
                            </span>
                            –
                            <span className="tabular-nums">{team.meta.to}</span>{' '}
                            dari{' '}
                            <span className="tabular-nums">
                                {team.meta.total}
                            </span>{' '}
                            orang.{' '}
                            <Link
                                href={verifyRepresentative()}
                                className="font-medium text-primary underline-offset-4 hover:underline"
                            >
                                Cari orang tertentu
                            </Link>
                            .
                        </p>
                    </Section>
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
                            href={verifyRepresentative()}
                            className="text-sm font-medium underline-offset-8 transition-colors hover:text-primary hover:underline"
                        >
                            Verifikasi perwakilan resmi
                        </Link>
                    </div>
                </section>
            </div>
        </>
    );
}

/**
 * Penomoran halaman roster.
 *
 * Memuat ulang hanya prop `team` dan menahan posisi gulir — pengunjung yang
 * sedang membaca daftar tim tidak dilempar kembali ke puncak halaman setiap
 * kali berpindah halaman.
 */
function TeamPager({ meta }: { meta: PaginatedMeta }) {
    if (meta.last_page <= 1) {
        return null;
    }

    const previous = Math.max(1, meta.current_page - 1);
    const next = Math.min(meta.last_page, meta.current_page + 1);

    return (
        <div className="flex items-center gap-2">
            <PagerButton
                href={about({ query: { tim: previous } })}
                label="Halaman sebelumnya"
                disabled={meta.current_page === 1}
            >
                <ChevronLeft className="size-4 rtl:rotate-180" />
            </PagerButton>

            <span
                dir="ltr"
                className="font-mono text-xs text-muted-foreground tabular-nums"
            >
                {meta.current_page} / {meta.last_page}
            </span>

            <PagerButton
                href={about({ query: { tim: next } })}
                label="Halaman berikutnya"
                disabled={meta.current_page === meta.last_page}
            >
                <ChevronRight className="size-4 rtl:rotate-180" />
            </PagerButton>
        </div>
    );
}

function PagerButton({
    href,
    label,
    disabled,
    children,
}: {
    href: ReturnType<typeof about>;
    label: string;
    disabled: boolean;
    children: ReactNode;
}) {
    const shared =
        'flex size-9 items-center justify-center rounded-full border border-border/70';

    if (disabled) {
        return (
            <span
                aria-hidden
                className={cn(shared, 'text-muted-foreground opacity-40')}
            >
                {children}
            </span>
        );
    }

    return (
        <Link
            href={href}
            only={['team']}
            preserveScroll
            preserveState
            aria-label={label}
            className={cn(
                shared,
                'transition-colors hover:border-foreground/20 hover:text-primary',
                'focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none',
            )}
        >
            {children}
        </Link>
    );
}
