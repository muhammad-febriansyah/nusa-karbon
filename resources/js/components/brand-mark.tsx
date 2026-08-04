import { usePage } from '@inertiajs/react';
import { cn } from '@/lib/utils';

type Props = {
    className?: string;
    imageClassName?: string;
    labelClassName?: string;
    accentClassName?: string;
    showLabel?: boolean;
};

/** Dipakai selama Pengaturan Situs belum punya logo yang diunggah. */
const FALLBACK_LOGO = '/logo-nusacarbon-mark.png';

/**
 * Logo dan nama perusahaan, keduanya dibaca dari Pengaturan Situs.
 *
 * Suku kata "carbon"/"karbon" di akhir nama diberi warna aksen; nama lain
 * ditampilkan utuh tanpa pemisahan yang dipaksakan.
 */
export default function BrandMark({
    className,
    imageClassName,
    labelClassName,
    accentClassName = 'text-primary',
    showLabel = true,
}: Props) {
    const brand = usePage().props.brand;
    const name = brand?.name ?? 'Nusacarbon';
    const match = /^(.*?)(carbon|karbon)$/i.exec(name);
    const head = match ? match[1] : name;
    const accent = match ? match[2] : '';

    return (
        <div className={cn('flex items-center gap-2.5', className)}>
            <img
                src={brand?.logo ?? FALLBACK_LOGO}
                alt={name}
                className={cn(
                    'block h-6 w-auto object-contain',
                    imageClassName,
                )}
            />
            {showLabel && (
                <span
                    className={cn(
                        'text-base font-semibold tracking-tight',
                        labelClassName,
                    )}
                >
                    {head}
                    {accent !== '' && (
                        <span className={accentClassName}>{accent}</span>
                    )}
                </span>
            )}
        </div>
    );
}
