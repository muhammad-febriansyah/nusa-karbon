import { Weight } from 'lucide-react';
import CardCarousel from '@/components/public/card-carousel';
import type { PublicPackagingOption } from '@/types';

/**
 * Pilihan kemasan.
 *
 * Tiga puluh pilihan yang ditumpuk dalam kisi memakan sekitar 4.100 piksel —
 * hampir sepertiga tinggi beranda untuk satu bagian saja. Deret geser memuat
 * jumlah yang sama dalam sekitar 800 piksel tanpa menyembunyikan satu pun
 * pilihan.
 *
 * Bentuk geser juga cocok dengan isinya: pembeli menelusuri daftar ini mencari
 * kemasan yang sesuai, bukan menyandingkan tiga puluh angka berat bersih.
 */
export default function PackagingSection({
    items,
}: {
    items: PublicPackagingOption[];
}) {
    if (items.length === 0) {
        return null;
    }

    return (
        <CardCarousel
            eyebrow="Kemasan"
            title="Pilihan kemasan yang bisa disesuaikan"
            description="Termasuk kemasan berlabel merek Anda sendiri. Berat bersih per kemasan menentukan isi master carton, jadi angkanya ikut memengaruhi hitungan kontainer."
            unit="Kemasan"
        >
            {items.map((option) => (
                <article
                    key={option.id}
                    className="rounded-panel flex w-[15rem] shrink-0 snap-start flex-col overflow-hidden border border-border/70 bg-card sm:w-[17rem]"
                >
                    <div className="aspect-4/3 shrink-0 overflow-hidden border-b border-border/70 bg-muted">
                        {option.image_url ? (
                            <img
                                src={option.image_url}
                                alt=""
                                loading="lazy"
                                decoding="async"
                                className="size-full object-cover"
                            />
                        ) : (
                            <div className="flex size-full items-center justify-center text-xs text-muted-foreground">
                                Belum ada gambar
                            </div>
                        )}
                    </div>

                    <div className="flex flex-1 flex-col p-5">
                        <h3 className="text-[15px] leading-snug font-semibold tracking-tight">
                            {option.name}
                        </h3>

                        {option.description && (
                            <p className="mt-1.5 line-clamp-2 flex-1 text-sm leading-relaxed text-muted-foreground">
                                {option.description}
                            </p>
                        )}

                        {option.net_weight_kg !== null && (
                            <p className="mt-4 flex items-center gap-2 border-t border-border/70 pt-4 text-sm">
                                <Weight className="size-4 shrink-0 text-primary" />
                                <span className="text-muted-foreground">
                                    Net Weight
                                </span>
                                <span
                                    className="font-mono font-medium tabular-nums"
                                    dir="ltr"
                                >
                                    {option.net_weight_kg} kg
                                </span>
                            </p>
                        )}
                    </div>
                </article>
            ))}
        </CardCarousel>
    );
}
