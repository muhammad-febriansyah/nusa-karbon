import type { CSSProperties } from 'react';
import { cn } from '@/lib/utils';

type Props = {
    /** Panjang ekor cahaya dalam piksel. */
    size?: number;
    durationSeconds?: number;
    className?: string;
};

/**
 * Titik cahaya yang menyusuri tepi kartu. Port dari `BorderBeam` Magic UI.
 *
 * Elemen induk wajib `position: relative` dan punya `border-radius` — lintasan
 * beamnya mewarisi radius itu.
 *
 * CSS murni: geraknya dari `offset-path` + satu keyframe, bukan dari `motion`.
 * Warnanya token tema, bukan gradien biru bawaan Magic UI.
 *
 * Lintasannya `border-beam-track` di `app.css`, bukan `overflow-hidden`. Yang
 * membatasi cahaya ke tepi kartu adalah mask di kelas itu; memotongnya dengan
 * `overflow` justru menampilkan seluruh kotak gradien.
 */
export default function BorderBeam({
    size = 120,
    durationSeconds = 10,
    className,
}: Props) {
    return (
        <div aria-hidden className="border-beam-track">
            <div
                style={
                    {
                        '--beam-duration': `${durationSeconds}s`,
                        offsetPath: `rect(0 auto auto 0 round ${size}px)`,
                        width: size,
                    } as CSSProperties
                }
                className={cn(
                    'border-beam-dot absolute aspect-square animate-border-beam',
                    'bg-gradient-to-l from-primary via-accent to-transparent',
                    className,
                )}
            />
        </div>
    );
}
