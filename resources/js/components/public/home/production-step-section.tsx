import { useState } from 'react';
import CardCarousel from '@/components/public/card-carousel';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import type { PublicProductionStep } from '@/types';

/**
 * Nomor tahap diturunkan dari posisi, bukan dari kolom tersendiri.
 *
 * Urutannya sudah dipegang `sort_order` di admin. Menyimpan nomornya lagi
 * sebagai data berarti ada dua sumber yang bisa saling bertentangan begitu
 * admin menyisipkan satu tahap di tengah.
 */
function stepNumber(index: number): string {
    return String(index + 1).padStart(2, '0');
}

function StepCard({
    step,
    index,
    onOpen,
}: {
    step: PublicProductionStep;
    index: number;
    onOpen: () => void;
}) {
    return (
        <button
            type="button"
            onClick={onOpen}
            className="rounded-panel group relative w-[16rem] shrink-0 snap-start overflow-hidden border border-border/70 bg-muted text-start focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none sm:w-[19rem]"
        >
            <div className="relative aspect-3/4 overflow-hidden">
                {step.image_url ? (
                    <img
                        src={step.image_url}
                        alt=""
                        loading="lazy"
                        decoding="async"
                        className="size-full object-cover transition-transform duration-500 group-hover:scale-[1.04]"
                    />
                ) : (
                    <div className="flex size-full items-center justify-center text-sm text-muted-foreground">
                        Belum ada gambar
                    </div>
                )}

                {/*
                 * Peredup hanya di bagian bawah, tempat teksnya berada. Meredam
                 * seluruh gambar akan mematikan fotonya demi dua baris teks.
                 */}
                <div
                    aria-hidden
                    className="absolute inset-0 bg-linear-to-t from-black/85 via-black/25 to-transparent"
                />

                <div className="absolute inset-x-0 bottom-0 p-5 text-white">
                    <span className="font-mono text-xs tracking-[0.1em] text-white/70">
                        Tahap {stepNumber(index)}
                    </span>
                    <span className="mt-1.5 block font-display text-lg leading-snug font-semibold text-balance">
                        {step.title}
                    </span>
                </div>
            </div>
        </button>
    );
}

/**
 * Tahapan produksi sebagai deret kartu yang bisa digeser.
 *
 * Bentuk geser dipilih karena isinya memang berurutan: satu tahap menghasilkan
 * bahan untuk tahap berikutnya, dan urutan itu informasi. Kisi biasa memutus
 * urutannya jadi beberapa baris, dan pembaca kehilangan alurnya.
 *
 * Keterangan panjang tiap tahap dibuka di dialog, bukan dijejalkan ke kartu.
 * Dialognya memakai primitif proyek sehingga jebakan fokus, tombol Escape, dan
 * scrim-nya sudah benar tanpa ditulis ulang.
 */
export default function ProductionStepSection({
    items,
}: {
    items: PublicProductionStep[];
}) {
    const [openIndex, setOpenIndex] = useState<number | null>(null);

    if (items.length === 0) {
        return null;
    }

    const active = openIndex === null ? null : items[openIndex];

    return (
        <>
            <CardCarousel
                eyebrow="Proses"
                title="Dari batok kelapa sampai masuk kontainer"
                description="Setiap tahap dikerjakan dan dicatat sendiri, bukan diserahkan ke pihak ketiga."
                unit="Tahap"
            >
                {items.map((step, index) => (
                    <StepCard
                        key={step.id}
                        step={step}
                        index={index}
                        onOpen={() => setOpenIndex(index)}
                    />
                ))}
            </CardCarousel>

            <Dialog
                open={active !== null}
                onOpenChange={(open) => {
                    if (!open) {
                        setOpenIndex(null);
                    }
                }}
            >
                {/*
                 * `sm:max-w-2xl`, bukan `max-w-2xl`. Lebar bawaan DialogContent
                 * ditulis sebagai varian `sm:max-w-lg`, dan kelas tanpa varian
                 * tidak pernah menang melawannya di layar selebar itu.
                 */}
                <DialogContent className="sm:max-w-2xl">
                    {active && openIndex !== null && (
                        <>
                            <DialogHeader>
                                <p className="font-mono text-xs tracking-[0.1em] text-muted-foreground">
                                    Tahap {stepNumber(openIndex)} dari{' '}
                                    {String(items.length).padStart(2, '0')}
                                </p>
                                <DialogTitle className="font-display text-2xl leading-tight font-bold tracking-[-0.02em]">
                                    {active.title}
                                </DialogTitle>
                                {active.description && (
                                    <DialogDescription className="text-base leading-relaxed">
                                        {active.description}
                                    </DialogDescription>
                                )}
                            </DialogHeader>

                            {active.image_url && (
                                <img
                                    src={active.image_url}
                                    alt=""
                                    loading="lazy"
                                    decoding="async"
                                    className="rounded-card aspect-16/9 w-full object-cover"
                                />
                            )}
                        </>
                    )}
                </DialogContent>
            </Dialog>
        </>
    );
}
