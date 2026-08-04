import type { VariantProps } from 'class-variance-authority';
import type { LucideIcon } from 'lucide-react';
import { AlertTriangle, Info, ShieldCheck, Trash2, X } from 'lucide-react';
import type { ReactNode } from 'react';
import type { buttonVariants } from '@/components/ui/button';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogTitle,
} from '@/components/ui/dialog';
import { Spinner } from '@/components/ui/spinner';
import { cn } from '@/lib/utils';

export type DialogTone = 'danger' | 'warning' | 'info' | 'success';

type ButtonVariant = NonNullable<
    VariantProps<typeof buttonVariants>['variant']
>;

const toneIcon: Record<DialogTone, LucideIcon> = {
    danger: Trash2,
    warning: AlertTriangle,
    info: Info,
    success: ShieldCheck,
};

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
    /** Wajib menyebut nama objeknya, bukan "Hapus data ini?". */
    title: string;
    description: ReactNode;
    /** Kata kerja spesifik, bukan "OK" atau "Ya". */
    confirmLabel: string;
    /** Label saat proses berjalan, misalnya "Menghapus...". */
    loadingLabel?: string;
    confirmVariant?: ButtonVariant;
    cancelLabel?: string;
    onConfirm: () => void;
    loading?: boolean;
};

/**
 * Dialog konfirmasi untuk aksi destruktif dan perubahan status signifikan.
 * Selama proses berjalan dialog tidak dapat ditutup.
 *
 * @see docs/05-design-system.md bagian 4
 */
export default function ConfirmDialog({
    open,
    onOpenChange,
    tone = 'danger',
    icon,
    title,
    description,
    confirmLabel,
    loadingLabel,
    confirmVariant = 'delete',
    cancelLabel = 'Batal',
    onConfirm,
    loading = false,
}: Props) {
    const Icon = icon ?? toneIcon[tone];

    return (
        <Dialog
            open={open}
            onOpenChange={(next) => {
                if (loading) {
                    return;
                }

                onOpenChange(next);
            }}
        >
            <DialogContent showCloseButton={!loading}>
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
                    <Button
                        type="button"
                        variant="cancel"
                        disabled={loading}
                        onClick={() => onOpenChange(false)}
                    >
                        <X />
                        {cancelLabel}
                    </Button>
                    <Button
                        type="button"
                        variant={confirmVariant}
                        disabled={loading}
                        onClick={onConfirm}
                        data-test="confirm-dialog-button"
                    >
                        {loading ? <Spinner /> : <Icon />}
                        {loading
                            ? (loadingLabel ?? 'Memproses...')
                            : confirmLabel}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
