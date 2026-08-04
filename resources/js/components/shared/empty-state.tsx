import type { LucideIcon } from 'lucide-react';
import { FilterX, Inbox } from 'lucide-react';
import type { ReactNode } from 'react';

type Props = {
    /**
     * `empty` bila memang belum ada data, `filtered` bila filter tidak
     * menemukan hasil. Keduanya wajib dibedakan.
     *
     * @see docs/05-design-system.md bagian 10
     */
    variant?: 'empty' | 'filtered';
    icon?: LucideIcon;
    title?: string;
    description?: string;
    action?: ReactNode;
};

export default function EmptyState({
    variant = 'empty',
    icon,
    title,
    description,
    action,
}: Props) {
    const Icon = icon ?? (variant === 'filtered' ? FilterX : Inbox);

    const defaultTitle =
        variant === 'filtered' ? 'Tidak ada hasil' : 'Belum ada data';

    const defaultDescription =
        variant === 'filtered'
            ? 'Tidak ada data yang cocok dengan filter dan pencarian Anda.'
            : 'Data pertama akan muncul di sini setelah ditambahkan.';

    return (
        <div className="flex flex-col items-center justify-center gap-2 px-6 py-16 text-center">
            <Icon
                aria-hidden
                strokeWidth={1.5}
                className="size-10 text-muted-foreground"
            />
            <p className="font-medium">{title ?? defaultTitle}</p>
            <p className="max-w-md text-sm text-muted-foreground">
                {description ?? defaultDescription}
            </p>
            {action && <div className="mt-3">{action}</div>}
        </div>
    );
}
