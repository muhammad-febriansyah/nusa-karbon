import { LazyMotion } from 'motion/react';
import type { ReactNode } from 'react';

const loadMotionFeatures = () =>
    import('@/lib/motion-features').then((features) => features.default);

/**
 * Menyediakan fitur `motion` untuk seluruh halaman publik.
 *
 * `strict` mewajibkan komponen memakai `m.div`, bukan `motion.div`. Tanpa itu,
 * satu impor `motion` yang terlewat akan menarik kembali bundel penuh dan
 * membatalkan seluruh manfaat pemuatan malas ini.
 */
export function MotionProvider({ children }: { children: ReactNode }) {
    return (
        <LazyMotion strict features={loadMotionFeatures}>
            {children}
        </LazyMotion>
    );
}
