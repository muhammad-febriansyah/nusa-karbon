import { useId } from 'react';
import { cn } from '@/lib/utils';

type Props = {
    /** Sisi satu sel kisi dalam piksel. */
    size?: number;
    className?: string;
};

/**
 * Latar kisi tipis. Port dari `GridPattern` Magic UI.
 *
 * Warna garis mengikuti `currentColor`, jadi ketebalannya diatur lewat kelas
 * (`text-border/60`) dan ikut berubah saat admin mengganti tema. Aslinya memakai
 * `stroke-gray-400/30` yang mustahil diselaraskan dengan tema.
 */
export default function GridPattern({ size = 48, className }: Props) {
    const patternId = useId();

    return (
        <svg
            aria-hidden
            className={cn(
                'pointer-events-none absolute inset-0 size-full',
                className,
            )}
        >
            <defs>
                <pattern
                    id={patternId}
                    width={size}
                    height={size}
                    patternUnits="userSpaceOnUse"
                >
                    <path
                        d={`M ${size} 0 L 0 0 0 ${size}`}
                        fill="none"
                        stroke="currentColor"
                        strokeWidth="1"
                    />
                </pattern>
            </defs>
            <rect width="100%" height="100%" fill={`url(#${patternId})`} />
        </svg>
    );
}
