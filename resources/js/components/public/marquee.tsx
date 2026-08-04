import type { CSSProperties, ReactNode } from 'react';
import { cn } from '@/lib/utils';

type Props = {
    children: ReactNode;
    /** Durasi satu putaran penuh. Makin panjang isinya, makin besar nilainya. */
    durationSeconds?: number;
    /** Jumlah salinan isi. Dua sudah cukup selama isinya melebihi lebar layar. */
    repeat?: number;
    pauseOnHover?: boolean;
    className?: string;
};

/**
 * Baris berjalan tanpa henti. Port dari `Marquee` Magic UI.
 *
 * CSS murni — tidak memakai `motion`. Isinya digandakan `repeat` kali supaya
 * sambungan antar putaran tidak terlihat.
 *
 * Duplikatnya `aria-hidden` dan seluruh komponen ditandai `role="marquee"`
 * agar pembaca layar tidak membacakan daftar yang sama dua kali.
 *
 * Animasinya mati sendiri saat `prefers-reduced-motion` menyala; isinya tetap
 * terbaca, hanya berhenti bergerak.
 */
export default function Marquee({
    children,
    durationSeconds = 40,
    repeat = 2,
    pauseOnHover = true,
    className,
}: Props) {
    return (
        <div
            role="marquee"
            style={
                {
                    '--marquee-duration': `${durationSeconds}s`,
                    '--marquee-gap': '3rem',
                } as CSSProperties
            }
            className={cn(
                'group flex w-full [gap:var(--marquee-gap)] overflow-hidden',
                '[mask-image:linear-gradient(to_right,transparent,black_8%,black_92%,transparent)]',
                className,
            )}
        >
            {Array.from({ length: repeat }, (_, index) => (
                <div
                    key={index}
                    aria-hidden={index > 0}
                    className={cn(
                        'flex shrink-0 animate-marquee items-center justify-around',
                        '[gap:var(--marquee-gap)]',
                        pauseOnHover &&
                            'group-hover:[animation-play-state:paused]',
                    )}
                >
                    {children}
                </div>
            ))}
        </div>
    );
}
