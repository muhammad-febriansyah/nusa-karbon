import { Link } from '@inertiajs/react';
import type { InertiaLinkProps } from '@inertiajs/react';
import { Eye, SquarePen, Trash2 } from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
import { Button } from '@/components/ui/button';

type Size = 'sm' | 'default';

type Variant = 'detail' | 'edit' | 'delete';

/**
 * Aksi bisa berupa tautan ke halaman terpisah (`href`) atau membuka dialog
 * (`onClick`) — CRUD berbasis dialog dipakai untuk modul berfield sedikit.
 */
type ActionProps = {
    href?: NonNullable<InertiaLinkProps['href']>;
    onClick?: () => void;
    size?: Size;
    disabled?: boolean;
};

/**
 * Aksi baris tabel wajib memakai komponen ini, bukan menyusun <Button> manual —
 * di situlah inkonsistensi warna dan ikon muncul.
 *
 * @see docs/05-design-system.md bagian 3.4
 */
function ActionButton({
    variant,
    icon: Icon,
    label,
    href,
    onClick,
    size = 'sm',
    disabled,
}: ActionProps & { variant: Variant; icon: LucideIcon; label: string }) {
    if (href && !disabled) {
        return (
            <Button asChild variant={variant} size={size}>
                <Link href={href}>
                    <Icon /> {label}
                </Link>
            </Button>
        );
    }

    return (
        <Button
            type="button"
            variant={variant}
            size={size}
            onClick={onClick}
            disabled={disabled}
        >
            <Icon /> {label}
        </Button>
    );
}

export function DetailButton(props: ActionProps) {
    return (
        <ActionButton {...props} variant="detail" icon={Eye} label="Detail" />
    );
}

export function EditButton(props: ActionProps) {
    return (
        <ActionButton {...props} variant="edit" icon={SquarePen} label="Ubah" />
    );
}

export function DeleteButton(props: ActionProps) {
    return (
        <ActionButton {...props} variant="delete" icon={Trash2} label="Hapus" />
    );
}
