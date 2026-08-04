import { Link, router } from '@inertiajs/react';
import PageHeader from '@/components/public/page-header';
import Pagination from '@/components/public/pagination';
import PostCard from '@/components/public/post-card';
import Rise from '@/components/public/rise';
import SeoHead from '@/components/public/seo-head';
import { Label } from '@/components/ui/label';
import { home } from '@/routes/public';
import blog from '@/routes/public/blog';
import type { PublicPostCard } from '@/types';
import type { PaginatedMeta } from '@/types/pagination';

type Category = {
    id: number;
    name: string;
    slug: string;
    /** Jumlah artikel terbit di kategori ini. */
    total: number;
};

type Props = {
    posts: { data: PublicPostCard[]; meta: PaginatedMeta };
    categories: Category[];
    /** Slug kategori yang sedang menyaring, atau `null` untuk seluruh arsip. */
    activeCategory: string | null;
    /** Jumlah seluruh artikel terbit, dipakai label "Semua artikel". */
    totalPublished: number;
};

/** Jeda antar kartu saat muncul, dalam detik. */
const STAGGER = 0.05;

/** Batas jeda supaya kartu terakhir tidak muncul jauh setelah yang pertama. */
const MAX_DELAY = 0.3;

/**
 * Jumlah kartu minimum sebelum yang pertama ditampilkan besar.
 *
 * Di bawah ambang ini kartu besar berdiri di atas satu-dua kartu kecil dan
 * terbaca sebagai tata letak yang gagal, bukan sebagai pilihan.
 */
const LEAD_THRESHOLD = 4;

const selectClass =
    'h-10 w-full rounded-[var(--radius)] border border-input bg-card px-3 text-sm focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none sm:w-auto';

/**
 * Arsip blog.
 *
 * Artikel terbaru tiap halaman ditampilkan besar, sisanya menyusul dalam kisi
 * tiga kolom — arsip yang seluruhnya berkartu sama besar tidak memberi tahu
 * pembaca harus mulai dari mana.
 *
 * Penyaringnya memakai daftar pilihan, bukan deretan pil: basis data memuat 21
 * kategori, dan sebagai pil itu jadi dinding empat baris yang menutupi
 * artikelnya sendiri. Jumlah artikel dituliskan di tiap pilihan supaya pembaca
 * tahu isinya sebelum menekan.
 */
export default function BlogIndex({
    posts: { data: items, meta },
    categories,
    activeCategory,
    totalPublished,
}: Props) {
    /*
     * Saringan kategori selalu kembali ke halaman pertama; nomor halaman hanya
     * ikut saat berpindah halaman. Menahan keduanya sekaligus membuat
     * pengunjung mendarat di halaman 3 dari kategori yang hanya punya satu
     * artikel — dan yang terlihat kisi kosong.
     */
    const queryFor = (patch: {
        kategori?: string;
        page?: number;
    }): Record<string, string> => {
        const query: Record<string, string> = {};
        const kategori = patch.kategori ?? activeCategory ?? '';

        if (kategori !== '') {
            query.kategori = kategori;
        }

        if (patch.page !== undefined && patch.page > 1) {
            query.page = String(patch.page);
        }

        return query;
    };

    const apply = (kategori: string) => {
        router.visit(blog.index({ query: queryFor({ kategori }) }), {
            preserveScroll: true,
            preserveState: true,
        });
    };

    const activeName =
        categories.find((category) => category.slug === activeCategory)?.name ??
        'Semua artikel';

    const hasLead = items.length >= LEAD_THRESHOLD;
    const lead = hasLead ? items[0] : undefined;
    const rest = hasLead ? items.slice(1) : items;

    return (
        <>
            <SeoHead
                title="Blog"
                description="Catatan lapangan seputar standar mutu briket arang batok kelapa, dokumen ekspor, dan hal teknis yang sering ditanyakan pembeli."
            />

            <PageHeader
                eyebrow="Blog"
                title="Catatan dari lapangan"
                breadcrumbs={[{ name: 'Beranda', href: home() }]}
                description="Standar mutu, dokumen ekspor, dan hal-hal teknis yang sering ditanyakan pembeli sebelum kontainer pertama."
            >
                {categories.length > 0 && (
                    <div className="space-y-2">
                        <Label htmlFor="kategori">Kategori</Label>
                        <select
                            id="kategori"
                            value={activeCategory ?? ''}
                            onChange={(event) => apply(event.target.value)}
                            className={selectClass}
                        >
                            <option value="">
                                Semua artikel ({totalPublished})
                            </option>
                            {categories.map((category) => (
                                <option key={category.id} value={category.slug}>
                                    {category.name} ({category.total})
                                </option>
                            ))}
                        </select>
                    </div>
                )}
            </PageHeader>

            <div className="mx-auto w-full max-w-7xl px-6 py-14">
                {items.length === 0 ? (
                    /*
                     * Kategori kosong hanya mungkin bila artikel terakhirnya
                     * dicabut admin. Disebutkan apa adanya beserta jalan
                     * keluarnya, bukan dibiarkan sebagai kisi kosong yang
                     * terbaca seperti halaman gagal dimuat.
                     */
                    <p className="rounded-panel border border-border/70 bg-card p-8 text-base text-muted-foreground">
                        Belum ada artikel untuk kategori ini.{' '}
                        <Link
                            href={blog.index()}
                            className="font-medium text-primary underline-offset-4 hover:underline"
                        >
                            Lihat seluruh arsip
                        </Link>
                        .
                    </p>
                ) : (
                    <>
                        <div className="flex flex-wrap items-baseline justify-between gap-x-6 gap-y-2 border-b border-border/70 pb-4">
                            <h2 className="font-display text-xl leading-tight font-bold tracking-[-0.02em]">
                                {activeName}
                            </h2>

                            <p className="text-sm text-muted-foreground">
                                Menampilkan{' '}
                                <span className="tabular-nums">
                                    {meta.from ?? 0}–{meta.to ?? 0}
                                </span>{' '}
                                dari{' '}
                                <span className="tabular-nums">
                                    {meta.total}
                                </span>{' '}
                                artikel
                            </p>
                        </div>

                        {lead && (
                            <Rise className="mt-8 block">
                                <PostCard post={lead} variant="lead" />
                            </Rise>
                        )}

                        <div className="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            {rest.map((post, index) => (
                                <Rise
                                    key={post.id}
                                    delay={Math.min(index * STAGGER, MAX_DELAY)}
                                    className="h-full"
                                >
                                    <PostCard post={post} />
                                </Rise>
                            ))}
                        </div>

                        <Pagination
                            meta={meta}
                            unit="Halaman artikel"
                            hrefFor={(page) =>
                                blog.index({ query: queryFor({ page }) })
                            }
                        />
                    </>
                )}
            </div>
        </>
    );
}
