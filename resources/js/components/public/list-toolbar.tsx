import { X } from 'lucide-react';
import type { ReactNode } from 'react';

type Props = {
    /** Judul daftar yang sedang ditampilkan, ikut berubah saat disaring. */
    heading: string;
    /** Rentang dan jumlah isinya, misal "1–7 dari 28 artikel". */
    summary?: ReactNode;
    /** Deretan penyaring. */
    children?: ReactNode;
    /** Ditampilkan hanya bila ada saringan aktif yang bisa dilepas. */
    onReset?: () => void;
};

/**
 * Bilah alat di atas daftar publik.
 *
 * Penyaringnya duduk di sini, bukan mengambang di kepala halaman. Sebelumnya
 * satu kotak pilihan berdiri sendirian di bawah keterangan halaman dengan
 * setengah lebar layar kosong di sebelahnya, tanpa apa pun yang mensejajarkan
 * dirinya. Di sini ia sebaris dengan judul daftar dan jumlah isinya — ketiganya
 * menjelaskan hal yang sama: apa yang sedang ditampilkan.
 */
export default function ListToolbar({
    heading,
    summary,
    children,
    onReset,
}: Props) {
    return (
        <div className="flex flex-col gap-5 border-b border-border/70 pb-5 lg:flex-row lg:items-end lg:justify-between lg:gap-8">
            <div className="min-w-0">
                <h2 className="font-display text-xl leading-tight font-bold tracking-[-0.02em]">
                    {heading}
                </h2>

                {summary && (
                    <p className="mt-1.5 text-sm text-muted-foreground">
                        {summary}
                    </p>
                )}
            </div>

            {(children || onReset) && (
                <div className="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center">
                    {children}

                    {onReset && (
                        <button
                            type="button"
                            onClick={onReset}
                            className="inline-flex items-center gap-1.5 self-start text-sm text-muted-foreground sm:self-auto underline-offset-4 transition-colors hover:text-foreground hover:underline focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
                        >
                            <X aria-hidden className="size-3.5" />
                            Atur ulang
                        </button>
                    )}
                </div>
            )}
        </div>
    );
}
