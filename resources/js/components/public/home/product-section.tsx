import { Link } from '@inertiajs/react';
import { ArrowRight } from 'lucide-react';
import BlurFade from '@/components/public/blur-fade';
import ProductCard from '@/components/public/product-card';
import Section from '@/components/public/section';
import products from '@/routes/public/products';
import type { PublicProductCard } from '@/types';

/** Jeda antar kartu saat muncul, dalam detik. */
const STAGGER = 0.06;

/**
 * Etalase produk di beranda.
 *
 * Sengaja tetap berbentuk kisi, bukan deret geser seperti proses dan kemasan:
 * ukuran dan MOQ harus sejajar supaya bisa dibandingkan sekilas, dan deret
 * geser justru menyembunyikan sebagian. Kartunya sendiri dipakai bersama
 * halaman katalog agar susunannya tidak pernah berbeda.
 */
export default function ProductSection({
    items,
}: {
    items: PublicProductCard[];
}) {
    if (items.length === 0) {
        return null;
    }

    return (
        <Section
            eyebrow="Produk"
            title="Briket arang batok kelapa siap ekspor"
            description="Setiap produk dilengkapi data master carton, sehingga kapasitas kontainernya bisa dihitung sebelum Anda memesan."
            action={
                <Link
                    href={products.index()}
                    className="group inline-flex items-center gap-2 text-sm font-medium underline-offset-8 transition-colors hover:text-primary hover:underline focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
                >
                    Lihat semua produk
                    <ArrowRight className="size-4 transition-transform group-hover:translate-x-0.5" />
                </Link>
            }
        >
            <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                {items.map((product, index) => (
                    <BlurFade
                        key={product.id}
                        delay={index * STAGGER}
                        className="h-full"
                    >
                        <ProductCard product={product} />
                    </BlurFade>
                ))}
            </div>
        </Section>
    );
}
