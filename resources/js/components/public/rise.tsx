import type { ReactNode } from 'react';
import { cn } from '@/lib/utils';

type Props = {
    children: ReactNode;
    /** Jeda sebelum animasi mulai, dalam detik. Dipakai untuk efek berjenjang. */
    delay?: number;
    className?: string;
};

/**
 * Muncul naik begitu dipasang, bukan saat masuk viewport.
 *
 * Bedanya dengan `BlurFade` menentukan pada daftar berhalaman: `preserveScroll`
 * menahan posisi baca, jadi kartu halaman berikutnya lahir di atas layar dan
 * pengamat viewport tidak pernah menyalakannya — pengunjung mendarat di kisi
 * yang seluruh isinya `opacity: 0`. Animasi CSS biasa tidak punya syarat itu.
 *
 * `prefers-reduced-motion` tetap dihormati lewat aturan di `@layer base`.
 */
export default function Rise({ children, delay = 0, className }: Props) {
    return (
        <div
            className={cn('animate-rise', className)}
            style={{ animationDelay: `${delay}s` }}
        >
            {children}
        </div>
    );
}
