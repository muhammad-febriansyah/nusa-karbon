import {
    LayoutDashboard,
    Package,
    Save,
    SquarePen,
    Trash2,
} from 'lucide-react';

type Props = {
    tokens: Record<string, string>;
};

/**
 * Mini panel admin palsu yang memakai warna dari editor, bukan token global.
 * Warna ditulis inline supaya pratinjau tidak ikut berubah saat admin
 * membatalkan perubahan.
 *
 * @see docs/10-theming.md bagian 5
 */
export default function ThemePreviewCard({ tokens }: Props) {
    const radius = tokens.radius ?? '0.5rem';

    const pill = (background: string, foreground: string, label: string) => (
        <span
            key={label}
            className="inline-flex items-center gap-1 px-2.5 py-1 text-[11px] font-medium"
            style={{
                backgroundColor: background,
                color: foreground,
                borderRadius: radius,
            }}
        >
            {label}
        </span>
    );

    return (
        <div
            className="overflow-hidden border"
            style={{
                backgroundColor: tokens.background,
                borderColor: tokens.border,
                borderRadius: radius,
            }}
        >
            <div className="flex">
                <aside
                    className="hidden w-32 shrink-0 flex-col gap-1 border-e p-3 sm:flex"
                    style={{
                        backgroundColor: tokens.sidebar,
                        borderColor: tokens.sidebar_border,
                        color: tokens.sidebar_foreground,
                    }}
                >
                    <span
                        className="flex items-center gap-1.5 px-2 py-1.5 text-[11px] font-medium"
                        style={{
                            backgroundColor: tokens.sidebar_accent,
                            color: tokens.sidebar_accent_foreground,
                            borderRadius: radius,
                        }}
                    >
                        <LayoutDashboard className="size-3.5" />
                        Dashboard
                    </span>
                    <span className="flex items-center gap-1.5 px-2 py-1.5 text-[11px] opacity-80">
                        <Package className="size-3.5" />
                        Produk
                    </span>
                </aside>

                <div className="flex-1 space-y-3 p-3">
                    <div
                        className="border p-3"
                        style={{
                            backgroundColor: tokens.card,
                            borderColor: tokens.border,
                            color: tokens.card_foreground,
                            borderRadius: radius,
                        }}
                    >
                        <p className="text-[13px] font-semibold">
                            Briket Shisha Cube 26 mm
                        </p>
                        <p
                            className="text-[11px]"
                            style={{ color: tokens.muted_foreground }}
                        >
                            Master carton 20 kg · 0,042 CBM
                        </p>

                        <div className="mt-2 flex flex-wrap items-center gap-1.5">
                            {pill(
                                tokens.success,
                                tokens.success_foreground,
                                'Aktif',
                            )}
                            {pill(tokens.info, tokens.info_foreground, 'Baru')}
                            {pill(
                                tokens.warning,
                                tokens.warning_foreground,
                                'Segera habis',
                            )}
                        </div>

                        <div className="mt-3 flex flex-wrap gap-1.5">
                            <span
                                className="inline-flex items-center gap-1 px-2 py-1 text-[11px] font-medium"
                                style={{
                                    backgroundColor: tokens.warning,
                                    color: tokens.warning_foreground,
                                    borderRadius: radius,
                                }}
                            >
                                Detail
                            </span>
                            <span
                                className="inline-flex items-center gap-1 px-2 py-1 text-[11px] font-medium"
                                style={{
                                    backgroundColor: tokens.success,
                                    color: tokens.success_foreground,
                                    borderRadius: radius,
                                }}
                            >
                                <SquarePen className="size-3" />
                                Ubah
                            </span>
                            <span
                                className="inline-flex items-center gap-1 px-2 py-1 text-[11px] font-medium"
                                style={{
                                    backgroundColor: tokens.destructive,
                                    color: tokens.destructive_foreground,
                                    borderRadius: radius,
                                }}
                            >
                                <Trash2 className="size-3" />
                                Hapus
                            </span>
                        </div>
                    </div>

                    <div
                        className="border p-3"
                        style={{
                            backgroundColor: tokens.card,
                            borderColor: tokens.border,
                            color: tokens.card_foreground,
                            borderRadius: radius,
                        }}
                    >
                        <p className="text-[11px]">Nama produk</p>
                        <div
                            className="mt-1 border px-2 py-1.5 text-[11px]"
                            style={{
                                borderColor: tokens.input,
                                backgroundColor: tokens.background,
                                color: tokens.foreground,
                                borderRadius: radius,
                            }}
                        >
                            Briket Shisha Cube 26 mm
                        </div>

                        <div className="mt-3 flex flex-wrap gap-1.5">
                            {pill(
                                tokens.primary,
                                tokens.primary_foreground,
                                'Tambah',
                            )}
                            <span
                                className="inline-flex items-center gap-1 px-2 py-1 text-[11px] font-medium"
                                style={{
                                    backgroundColor: tokens.primary,
                                    color: tokens.primary_foreground,
                                    borderRadius: radius,
                                }}
                            >
                                <Save className="size-3" />
                                Simpan
                            </span>
                            {pill(
                                tokens.neutral,
                                tokens.neutral_foreground,
                                'Batal',
                            )}
                            {pill(
                                tokens.violet,
                                tokens.violet_foreground,
                                'Duplikat',
                            )}
                            {pill(
                                tokens.teal,
                                tokens.teal_foreground,
                                'Terbitkan',
                            )}
                            {pill(
                                tokens.accent,
                                tokens.accent_foreground,
                                'Aksen',
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}
