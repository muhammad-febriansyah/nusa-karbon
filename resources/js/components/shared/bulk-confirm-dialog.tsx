import type { DialogTone } from './confirm-dialog';
import ConfirmDialog from './confirm-dialog';

type Props = {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    tone?: DialogTone;
    /** Kata benda tunggal, misalnya "produk". */
    entity: string;
    /** Nama objek terpilih. Lima pertama disebutkan. */
    names: string[];
    /** Kalimat penjelas dampaknya, misalnya "Seluruhnya dipindahkan ke arsip." */
    consequence: string;
    confirmLabel: string;
    loadingLabel?: string;
    onConfirm: () => void;
    loading?: boolean;
};

/**
 * Dialog aksi massal: menyebut jumlahnya dan mendaftarkan maksimal 5 nama.
 *
 * @see docs/05-design-system.md bagian 4.3
 */
export default function BulkConfirmDialog({
    open,
    onOpenChange,
    tone = 'danger',
    entity,
    names,
    consequence,
    confirmLabel,
    loadingLabel,
    onConfirm,
    loading = false,
}: Props) {
    const shown = names.slice(0, 5);
    const rest = names.length - shown.length;

    const list = shown.map((name) => `"${name}"`).join(', ');
    const suffix = rest > 0 ? `, dan ${rest} lainnya` : '';

    return (
        <ConfirmDialog
            open={open}
            onOpenChange={onOpenChange}
            tone={tone}
            title={`${confirmLabel} ${names.length} ${entity} terpilih?`}
            description={
                names.length === 0
                    ? consequence
                    : `Termasuk ${list}${suffix}. ${consequence}`
            }
            confirmLabel={confirmLabel}
            loadingLabel={loadingLabel}
            onConfirm={onConfirm}
            loading={loading}
        />
    );
}
