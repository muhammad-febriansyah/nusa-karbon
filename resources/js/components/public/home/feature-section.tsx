import CardCarousel from '@/components/public/card-carousel';
import FeatureIcon from '@/components/public/feature-icon';
import type { PublicFeature } from '@/types';

/**
 * Alasan memilih perusahaan ini.
 *
 * Tiga puluh butir yang ditumpuk dalam kisi memakan sekitar 4.100 piksel —
 * bagian terpanjang di seluruh beranda. Deret geser memuat jumlah yang sama
 * dalam sekitar 800 piksel, dan tidak ada satu butir pun yang hilang.
 *
 * Riwayat dua susunan sebelumnya, supaya tidak diulang:
 *
 * 1. Pola bento — tiap kartu ketiga melebar dua kolom. Pada tiga puluh butir
 *    itu tidak terbaca sebagai pola, hanya sebagai ukuran acak, dan gambar di
 *    kartu lebar ikut melar jadi pita memanjang.
 *
 * 2. Gambar di bawah teks dengan tinggi tetap. Karena panjang keterangan
 *    berbeda-beda, letak gambarnya jadi berbeda di tiap kartu dan tidak ada
 *    satu pun garis yang sejajar.
 *
 * Sekarang lebar kartunya seragam dan gambarnya di atas dengan rasio yang sama,
 * jadi seluruh gambar dan seluruh judul berada di garis yang sama.
 */
export default function FeatureSection({ items }: { items: PublicFeature[] }) {
    if (items.length === 0) {
        return null;
    }

    return (
        <div className="border-y border-border/70 bg-muted/40">
            <CardCarousel
                eyebrow="Kenapa Kami"
                title="Alasan buyer bertahan setelah kontainer pertama"
                description="Bukan janji, melainkan hal-hal yang bisa Anda periksa sendiri sebelum membayar."
                unit="Alasan"
            >
                {items.map((feature) => (
                    <article
                        key={feature.id}
                        className="rounded-panel flex w-[15rem] shrink-0 snap-start flex-col overflow-hidden border border-border/70 bg-card sm:w-[17rem]"
                    >
                        {/*
                         * Bidang gambar tetap dirender meski fotonya belum
                         * diunggah. Kartu tanpa bidang ini akan lebih pendek
                         * daripada tetangganya dan merusak barisan.
                         */}
                        <div className="aspect-4/3 shrink-0 overflow-hidden border-b border-border/70 bg-muted">
                            {feature.image_url ? (
                                <img
                                    src={feature.image_url}
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

                        <div className="flex flex-1 gap-3 p-5">
                            {/*
                             * Ikonnya sama untuk ketiga puluh butir, jadi ia
                             * tidak membedakan apa pun — perannya penanda butir,
                             * bukan lambang. Karena itu dibuat kecil saja, bukan
                             * lencana berwarna merek seukuran 44 piksel yang
                             * diulang tiga puluh kali.
                             */}
                            <FeatureIcon
                                name={feature.icon}
                                className="mt-0.5 size-4 shrink-0 text-primary"
                            />

                            <div>
                                <h3 className="text-[15px] leading-snug font-semibold tracking-tight">
                                    {feature.title}
                                </h3>

                                {feature.description && (
                                    <p className="mt-1.5 text-sm leading-relaxed text-muted-foreground">
                                        {feature.description}
                                    </p>
                                )}
                            </div>
                        </div>
                    </article>
                ))}
            </CardCarousel>
        </div>
    );
}
