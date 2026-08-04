import SortableContentIndex from '@/components/shared/sortable-content-index';
import {
    create as itemCreate,
    destroy as destroyItem,
    edit as itemEdit,
    index as itemsIndex,
    reorder as reorderItems,
} from '@/routes/admin/qc-protocols';
import type { QcProtocol } from '@/types/models';

const LOCALE = 'id';

type Props = {
    items: QcProtocol[];
};

export default function QcProtocolIndex({ items }: Props) {
    return (
        <SortableContentIndex
            items={items.map((item) => ({
                id: item.id,
                title: item.title?.[LOCALE] ?? '—',
                subtitle: item.result_label?.[LOCALE] ?? null,
                imageUrl: item.image_url,
                badge: item.result_value,
                isActive: item.is_active,
            }))}
            title="Protokol QC"
            description="Pengujian mutu yang dijalankan beserta hasil yang dijamin."
            indexHref={itemsIndex()}
            createHref={itemCreate()}
            editHref={(id) => itemEdit(id)}
            destroyHref={(id) => destroyItem(id)}
            reorderHref={reorderItems()}
            createLabel="Tambah Protokol"
            emptyTitle="Belum ada protokol QC"
            emptyDescription="Tambahkan protokol agar buyer tahu pengujian apa saja yang dijalankan."
            deleteDescription="Protokol hilang dari halaman publik. Datanya dipindahkan ke arsip, bukan dihapus permanen."
            itemNoun="protokol"
        />
    );
}
