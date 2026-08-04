import { useId } from 'react';
import { cn } from '@/lib/utils';

/**
 * Sorot cahaya diagonal di belakang hero. Port dari `Spotlight` Aceternity UI.
 *
 * Dua perubahan dari aslinya: warnanya `currentColor` supaya bisa diatur lewat
 * kelas token (`text-primary`) alih-alih putih hardcoded, dan `id` filternya
 * dibangkitkan `useId()` — versi asli memakai id tetap, jadi dua sorot dalam
 * satu halaman akan saling menimpa filternya.
 *
 * Murni SVG dan satu keyframe CSS; tidak menyentuh `motion` sama sekali.
 */
export default function Spotlight({ className }: { className?: string }) {
    const filterId = useId();

    return (
        <svg
            aria-hidden
            className={cn(
                'pointer-events-none absolute z-0 h-[169%] w-[138%] animate-spotlight opacity-0 lg:w-[84%]',
                className,
            )}
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 3787 2842"
            fill="none"
        >
            <g filter={`url(#${filterId})`}>
                <ellipse
                    cx="1924.71"
                    cy="273.501"
                    rx="1924.71"
                    ry="273.501"
                    transform="matrix(-0.822377 -0.568943 -0.568943 0.822377 3631.88 2291.09)"
                    fill="currentColor"
                    fillOpacity="0.2"
                />
            </g>
            <defs>
                <filter
                    id={filterId}
                    x="0.860352"
                    y="0.838989"
                    width="3785.16"
                    height="2840.26"
                    filterUnits="userSpaceOnUse"
                    colorInterpolationFilters="sRGB"
                >
                    <feFlood floodOpacity="0" result="BackgroundImageFix" />
                    <feBlend
                        mode="normal"
                        in="SourceGraphic"
                        in2="BackgroundImageFix"
                        result="shape"
                    />
                    <feGaussianBlur stdDeviation="151" result="blur" />
                </filter>
            </defs>
        </svg>
    );
}
