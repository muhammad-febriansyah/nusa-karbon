import { Link, router } from '@inertiajs/react';
import FilterSelect from '@/components/public/filter-select';
import ListToolbar from '@/components/public/list-toolbar';
import PageHeader from '@/components/public/page-header';
import Pagination from '@/components/public/pagination';
import ProductCard from '@/components/public/product-card';
import Rise from '@/components/public/rise';
import SeoHead from '@/components/public/seo-head';
import { home } from '@/routes/public';
import products from '@/routes/public/products';
import type { PublicProductCard } from '@/types';
import type { PaginatedMeta } from '@/types/pagination';

type Category = {
    id: number;
    name: string;
    slug: string;
    /** Jumlah produk aktif di kategori ini. */
    total: number;
};

type Props = {
    products: { data: PublicProductCard[]; meta: PaginatedMeta };
    categories: Category[];
    activeCategory: string | null;
    activeSort: string;
    sorts: { value: string; label: string }[];
    /** Jumlah seluruh produk aktif, dipakai label "Semua kategori". */
    totalActive: number;
};

/** Jeda antar kartu saat muncul, dalam detik. */
const STAGGER = 0.04;

/** Batas jeda supaya kartu terakhir tidak muncul jauh setelah yang pertama. */
const MAX_DELAY = 0.32;

/**
 * Katalog produk.
 *
 * Penyaringnya memakai daftar pilihan, bukan deretan pil. Basis data memuat 21
 * kategori untuk 31 produk, dan lima belas di antaranya hanya berisi satu
 * produk — sebagai pil, itu jadi dinding empat baris yang menutupi katalognya
 * sendiri, dan hampir tiap pilihan berujung satu kartu. Jumlah produk dituliskan
 * di tiap pilihan supaya pembeli tahu isinya sebelum menekan.
 *
 * Urutan ikut disediakan karena halaman ini untuk membandingkan: ukuran dan MOQ
 * adalah dua sumbu yang benar-benar dipakai pembeli untuk menyaring.
 */
export default function ProductIndex({
    products: { data: items, meta },
    categories,
    activeCategory,
    activeSort,
    sorts,
    totalActive,
}: Props) {
    /*
     * Berpindah tanpa memuat ulang seluruh halaman dan tanpa melompat ke atas —
     * yang berubah hanya kisinya. Query string tetap ditulis supaya hasilnya
     * bisa ditautkan dan dibagikan.
     */
    const queryFor = (patch: {
        kategori?: string;
        urut?: string;
        page?: number;
    }): Record<string, string> => {
        const query: Record<string, string> = {};
        const kategori = patch.kategori ?? activeCategory ?? '';
        const urut = patch.urut ?? activeSort;

        if (kategori !== '') {
            query.kategori = kategori;
        }

        if (urut !== 'urutan') {
            query.urut = urut;
        }

        if (patch.page !== undefined && patch.page > 1) {
            query.page = String(patch.page);
        }

        return query;
    };

    /*
     * Mengganti saringan selalu kembali ke halaman pertama. Menahan nomor
     * halaman membuat pengunjung mendarat di halaman 3 dari kategori yang
     * hanya punya satu — dan yang terlihat kisi kosong.
     */
    const apply = (patch: { kategori?: string; urut?: string }) => {
        router.visit(products.index({ query: queryFor(patch) }), {
            preserveScroll: true,
            preserveState: true,
        });
    };

    const activeName =
        categories.find((category) => category.slug === activeCategory)?.name ??
        'Semua produk';

    const isFiltered = activeCategory !== null || activeSort !== 'urutan';

    return (
        <>
            <SeoHead
                title="Produk"
                description="Katalog briket arang batok kelapa siap ekspor beserta ukuran, MOQ, dan data master carton tiap produk."
            />

            <PageHeader
                eyebrow="Produk"
                title="Katalog briket arang batok kelapa"
                breadcrumbs={[{ name: 'Beranda', href: home() }]}
                description="Setiap produk dilengkapi ukuran, MOQ, dan data master carton — sehingga kapasitas kontainernya bisa dihitung sebelum Anda memesan."
            />

            <div className="mx-auto w-full max-w-7xl px-6 py-14">
                {items.length === 0 ? (
                    /*
                     * Katalog kosong hanya mungkin terjadi bila seluruh produk
                     * kategori itu dinonaktifkan admin. Disebutkan apa adanya
                     * beserta jalan keluarnya, bukan dibiarkan sebagai kisi
                     * kosong yang terbaca seperti halaman gagal dimuat.
                     */
                    <p className="text-base text-muted-foreground">
                        Belum ada produk yang ditampilkan untuk kategori ini.{' '}
                        <Link
                            href={products.index()}
                            className="font-medium text-primary underline-offset-4 hover:underline"
                        >
                            Lihat seluruh katalog
                        </Link>
                        .
                    </p>
                ) : (
                    <>
                        <ListToolbar
                            heading={activeName}
                            summary={
                                <>
                                    Menampilkan{' '}
                                    <span className="tabular-nums">
                                        {meta.from ?? 0}–{meta.to ?? 0}
                                    </span>{' '}
                                    dari{' '}
                                    <span className="tabular-nums">
                                        {meta.total}
                                    </span>{' '}
                                    produk
                                </>
                            }
                            onReset={
                                isFiltered
                                    ? () => apply({ kategori: '', urut: 'urutan' })
                                    : undefined
                            }
                        >
                            {categories.length > 0 && (
                                <FilterSelect
                                    id="kategori"
                                    label="Kategori"
                                    value={activeCategory ?? ''}
                                    onChange={(kategori) => apply({ kategori })}
                                    resetLabel={`Semua kategori (${totalActive})`}
                                    options={categories.map((category) => ({
                                        value: category.slug,
                                        label: `${category.name} (${category.total})`,
                                    }))}
                                />
                            )}

                            <FilterSelect
                                id="urut"
                                label="Urutkan"
                                value={activeSort}
                                onChange={(urut) => apply({ urut })}
                                options={sorts.map((sort) => ({
                                    value: sort.value,
                                    label: sort.label,
                                }))}
                            />
                        </ListToolbar>

                        <div className="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            {items.map((product, index) => (
                                <Rise
                                    key={product.id}
                                    delay={Math.min(index * STAGGER, MAX_DELAY)}
                                    className="h-full"
                                >
                                    <ProductCard product={product} />
                                </Rise>
                            ))}
                        </div>

                        <Pagination
                            meta={meta}
                            unit="Halaman produk"
                            hrefFor={(page) =>
                                products.index({ query: queryFor({ page }) })
                            }
                        />
                    </>
                )}
            </div>
        </>
    );
}
