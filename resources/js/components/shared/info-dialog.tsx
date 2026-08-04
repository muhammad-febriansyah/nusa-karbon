import type { LucideIcon } from 'lucide-react';
import { Info } from 'lucide-react';
import type { ReactNode } from 'react';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogTitle,
} from '@/components/ui/dialog';
import { cn } from '@/lib/utils';
import type { DialogTone } from './confirm-dialog';

const toneClasses: Record<DialogTone, string> = {
    danger: 'bg-destructive/12 text-destructive',
    warning: 'bg-warning/12 text-warning',
    info: 'bg-info/12 text-info',
    success: 'bg-success/12 text-success',
};

type Props = {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    tone?: DialogTone;
    icon?: LucideIcon;
    title: string;
    description: ReactNode;
    /** Aksi tambahan, misalnya "Lihat Produk Terkait". */
    action?: ReactNode;
    closeLabel?: string;
};

/**
 * Dipakai bila objek tidak dapat dihapus karena masih dipakai. Bukan
 * konfirmasi — hanya satu tombol tutup.
 *
 * @see docs/05-design-system.md bagian 4.4
 */
export default function InfoDialog({
    open,
    onOpenChange,
    tone = 'warning',
    icon,
    title,
    description,
    action,
    closeLabel = 'Tutup',
}: Props) {
    const Icon = icon ?? Info;

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent>
                <span
                    aria-hidden
                    className={cn(
                        'flex size-11 items-center justify-center rounded-full',
                        toneClasses[tone],
                    )}
                >
                    <Icon className="size-5" />
                </span>

                <DialogTitle>{title}</DialogTitle>
                <DialogDescription>{description}</DialogDescription>

                <DialogFooter className="gap-2">
                    {action}
                    <Button
                        type="button"
                        variant="cancel"
                        onClick={() => onOpenChange(false)}
                    >
                        {closeLabel}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
