import { Lock } from 'lucide-react';
import { EditButton } from '@/components/shared/action-buttons';
import PageHeader from '@/components/shared/page-header';
import Panel from '@/components/shared/panel';
import StatusBadge from '@/components/shared/status-badge';
import { dashboard } from '@/routes/admin';
import {
    edit as blockEdit,
    index as blocksIndex,
} from '@/routes/admin/page-blocks';
import type { PageBlock } from '@/types/models';

const LOCALE = 'id';

/** Judul halaman yang dikenali admin, bukan kunci teknisnya. */
const PAGE_LABELS: Record<string, string> = {
    home: 'Beranda',
    about: 'Tentang Kami',
    export_info: 'Informasi Ekspor',
};

type Props = {
    blocks: PageBlock[];
};

export default function PageBlockIndex({ blocks }: Props) {
    const pages = [...new Set(blocks.map((block) => block.page_key))];

    return (
        <>
            <PageHeader
                title="Blok Halaman"
                description="Isi halaman statis. Susunan bloknya tetap; yang bisa diubah hanya isinya."
                breadcrumbs={[
                    { title: 'Dashboard', href: dashboard() },
                    { title: 'Blok Halaman', href: blocksIndex() },
                ]}
            />

            <div className="flex items-start gap-2.5 rounded-[var(--radius)] border border-s-[3px] border-info/25 border-s-info bg-info/10 px-3.5 py-2.5">
                <Lock
                    aria-hidden
                    className="mt-0.5 size-4 shrink-0 text-info"
                />
                <p className="text-[13px] text-info">
                    Kunci blok ditentukan dari kode dan tidak bisa ditambah atau
                    dihapus di sini. Halaman publik memanggil blok lewat kunci
                    itu — mengubahnya akan mengosongkan bagian halaman.
                </p>
            </div>

            {pages.map((pageKey) => (
                <Panel
                    key={pageKey}
                    title={PAGE_LABELS[pageKey] ?? pageKey}
                    description={`Kunci halaman: ${pageKey}`}
                >
                    <ul className="overflow-hidden rounded-[var(--radius)] border border-border">
                        {blocks
                            .filter((block) => block.page_key === pageKey)
                            .map((block) => (
                                <li
                                    key={block.id}
                                    className="flex flex-wrap items-center justify-between gap-3 border-b border-border px-4 py-3 last:border-b-0"
                                >
                                    <div className="flex min-w-0 flex-col leading-tight">
                                        <span className="truncate font-medium">
                                            {block.title?.[LOCALE] ??
                                                block.block_key}
                                        </span>
                                        <span
                                            dir="ltr"
                                            className="truncate text-xs text-muted-foreground"
                                        >
                                            {block.block_key}
                                        </span>
                                    </div>

                                    <div className="flex items-center gap-2">
                                        <StatusBadge
                                            label={block.type_label}
                                            tone="info"
                                        />
                                        <StatusBadge
                                            label={
                                                block.is_active
                                                    ? 'Aktif'
                                                    : 'Nonaktif'
                                            }
                                            tone={
                                                block.is_active
                                                    ? 'success'
                                                    : 'muted'
                                            }
                                        />
                                        <EditButton
                                            href={blockEdit(block.id)}
                                        />
                                    </div>
                                </li>
                            ))}
                    </ul>
                </Panel>
            ))}
        </>
    );
}
