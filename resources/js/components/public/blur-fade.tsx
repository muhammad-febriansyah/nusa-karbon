import { m, useInView } from 'motion/react';
import { useRef } from 'react';
import type { ReactNode } from 'react';

type Props = {
    children: ReactNode;
    /** Jeda sebelum animasi mulai, dalam detik. Dipakai untuk efek berjenjang. */
    delay?: number;
    className?: string;
};

/**
 * Muncul perlahan sambil naik saat masuk viewport. Port dari `BlurFade`
 * Magic UI, tanpa blur-nya.
 *
 * Blur dibuang dengan sengaja: `filter: blur()` memaksa kompositor
 * meraster ulang setiap frame, dan pada bagian yang berisi angka spesifikasi
 * hasilnya justru mengganggu keterbacaan. Yang tersisa — opacity dan geser
 * kecil — dijalankan GPU tanpa biaya berarti.
 *
 * `once: true` supaya elemen tidak beranimasi ulang saat pengunjung menggulir
 * balik.
 */
export default function BlurFade({ children, delay = 0, className }: Props) {
    const ref = useRef<HTMLDivElement>(null);
    const inView = useInView(ref, { once: true, margin: '-64px' });

    return (
        <m.div
            ref={ref}
            initial={{ opacity: 0, y: 16 }}
            animate={inView ? { opacity: 1, y: 0 } : { opacity: 0, y: 16 }}
            transition={{ duration: 0.45, delay, ease: 'easeOut' }}
            className={className}
        >
            {children}
        </m.div>
    );
}
