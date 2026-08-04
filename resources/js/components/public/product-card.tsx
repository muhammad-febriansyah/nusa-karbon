import { Link } from '@inertiajs/react';
import products from '@/routes/public/products';
import type { PublicProductCard } from '@/types';

type SpecItem = { label: string; value: string };

/**
 * Satu keping data pada kartu produk.
 *
 * Nilainya dipasang mono dan `tabular-nums` supaya kolom angka tetap lurus dari
 * satu kartu ke kartu berikutnya — itu inti gunanya, karena kartu-kartu ini
 * dibaca sebagai perbandingan, bukan satu per satu.
 *
 * Angka dan satuan selalu `dir="ltr"`: dalam konteks Arab, "26 mm" yang dibalik
 * jadi "mm 26" adalah kesalahan yang paling sering lolos.
 */
function Spec({ label, value }: SpecItem) {
    return (
        <div>
            <dt className="text-xs text-muted-foreground">{label}</dt>
            <dd
                dir="ltr"
                className="mt-1 font-mono text-sm font-medium tabular-nums"
            >
                {value}
            </dd>
        </div>
    );
}

/**
 * Kartu produk untuk kisi.
 *
 * Dipakai bersama beranda dan katalog supaya keduanya tidak pernah berbeda
 * susunan — pengunjung yang membandingkan enam produk di beranda lalu membuka
 * katalog akan membaca kartu yang sama persis.
 */
export default function ProductCard({
    product,
}: {
    product: PublicProductCard;
}) {
    const specs: SpecItem[] = [
        { label: 'Ukuran', value: product.size_mm },
        {
            label: 'MOQ',
            value: product.moq_kg === null ? null : `${product.moq_kg} kg`,
        },
    ].filter((spec): spec is SpecItem => spec.value !== null);

    return (
        <article className="group rounded-panel relative flex h-full flex-col overflow-hidden border border-border/70 bg-card shadow-card transition-[box-shadow,border-color] duration-200 focus-within:border-foreground/20 hover:border-foreground/20 hover:shadow-dropdown">
            <div className="relative aspect-4/3 overflow-hidden border-b border-border/70 bg-muted">
                {product.cover_url ? (
                    <img
                        src={product.cover_url}
                        alt={product.name ?? product.code}
                        loading="lazy"
                        decoding="async"
                        className="size-full object-cover"
                    />
                ) : (
                    <div className="flex size-full items-center justify-center text-sm text-muted-foreground">
                        Belum ada gambar
                    </div>
                )}
            </div>

            <div className="flex flex-1 flex-col p-5">
                {/*
                 * Jarak ke pita spesifikasi dipasang di sini, bukan sebagai
                 * margin pada `dl`. `mt-auto` menyusut jadi nol begitu teksnya
                 * memenuhi kartu, dan garis pemisahnya menempel ke baris
                 * terakhir deskripsi.
                 */}
                <div className="pb-5">
                    <p className="text-xs font-medium text-muted-foreground">
                        {product.category ?? product.code}
                    </p>

                    <h3 className="mt-2 font-display text-lg leading-snug font-bold tracking-tight">
                        {/*
                         * Tanpa slug, halaman detail tidak punya alamat. Itu
                         * berarti terjemahan slug kosong di admin — judulnya
                         * tetap tampil sebagai teks biasa ketimbang menautkan ke
                         * URL yang pasti 404.
                         *
                         * Saat ada, tautannya dilebarkan ke seluruh kartu lewat
                         * `after:`, jadi kartu bisa ditekan di mana saja tetapi
                         * pembaca layar tetap mendengar satu tautan bernama
                         * judul produk, bukan dua tautan menuju halaman sama.
                         */}
                        {product.slug === null ? (
                            (product.name ?? product.code)
                        ) : (
                            <Link
                                href={products.show(product.slug)}
                                className="after:absolute after:inset-0 focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
                            >
                                {product.name ?? product.code}
                            </Link>
                        )}
                    </h3>

                    {product.short_description && (
                        <p className="mt-2 line-clamp-2 text-sm leading-relaxed text-muted-foreground">
                            {product.short_description}
                        </p>
                    )}
                </div>

                {specs.length > 0 && (
                    <dl className="mt-auto grid grid-cols-2 gap-4 border-t border-border/70 pt-5">
                        {specs.map((spec) => (
                            <Spec
                                key={spec.label}
                                label={spec.label}
                                value={spec.value}
                            />
                        ))}
                    </dl>
                )}
            </div>
        </article>
    );
}
