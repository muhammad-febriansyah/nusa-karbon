import type { InertiaLinkProps } from '@inertiajs/react';
import { Link } from '@inertiajs/react';
import { MoreHorizontal } from 'lucide-react';
import type { ReactNode } from 'react';
import {
    DeleteButton,
    DetailButton,
    EditButton,
} from '@/components/shared/action-buttons';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

export type ExtraAction = {
    label: string;
    icon?: ReactNode;
    href?: NonNullable<InertiaLinkProps['href']>;
    onSelect?: () => void;
};

type Props = {
    detailHref?: NonNullable<InertiaLinkProps['href']>;
    onDetail?: () => void;
    editHref?: NonNullable<InertiaLinkProps['href']>;
    /** Dipakai modul yang formnya berupa dialog, bukan halaman terpisah. */
    onEdit?: () => void;
    onDelete?: () => void;
    /** Duplikat, Arsipkan, Aktifkan — masuk dropdown "Aksi lain". */
    extraActions?: ExtraAction[];
};

/**
 * Tiga tombol berdampingan dengan urutan tetap: Detail, Ubah, Hapus.
 * Dilarang mengubahnya jadi dropdown ikon; aksi di luar ketiganya masuk
 * dropdown "Aksi lain" setelah tombol Hapus.
 *
 * @see docs/07-admin-ui-standards.md bagian 3
 */
export function DataTableRowActions({
    detailHref,
    onDetail,
    editHref,
    onEdit,
    onDelete,
    extraActions = [],
}: Props) {
    return (
        <div className="flex items-center justify-end gap-2">
            {(detailHref || onDetail) && (
                <DetailButton href={detailHref} onClick={onDetail} />
            )}
            {(editHref || onEdit) && (
                <EditButton href={editHref} onClick={onEdit} />
            )}
            {onDelete && <DeleteButton onClick={onDelete} />}

            {extraActions.length > 0 && (
                <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                        <Button type="button" variant="cancel" size="sm">
                            <MoreHorizontal />
                            <span className="sr-only">Aksi lain</span>
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end" className="w-44">
                        <DropdownMenuLabel>Aksi lain</DropdownMenuLabel>
                        <DropdownMenuSeparator />
                        {extraActions.map((action) => (
                            <DropdownMenuItem
                                key={action.label}
                                onSelect={action.onSelect}
                                asChild={Boolean(action.href)}
                            >
                                {action.href ? (
                                    <Link href={action.href}>
                                        {action.icon}
                                        {action.label}
                                    </Link>
                                ) : (
                                    <>
                                        {action.icon}
                                        {action.label}
                                    </>
                                )}
                            </DropdownMenuItem>
                        ))}
                    </DropdownMenuContent>
                </DropdownMenu>
            )}
        </div>
    );
}
