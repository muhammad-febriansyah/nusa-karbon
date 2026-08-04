import SortableContentIndex from '@/components/shared/sortable-content-index';
import {
    create as itemCreate,
    destroy as destroyItem,
    edit as itemEdit,
    index as itemsIndex,
    reorder as reorderItems,
} from '@/routes/admin/production-steps';
import type { ProductionStep } from '@/types/models';

const LOCALE = 'id';

type Props = {
    items: ProductionStep[];
};

export default function ProductionStepIndex({ items }: Props) {
    return (
        <SortableContentIndex
            items={items.map((item) => ({
                id: item.id,
                title: item.title?.[LOCALE] ?? '—',
                subtitle: item.description?.[LOCALE] ?? null,
                imageUrl: item.image_url,
                isActive: item.is_active,
            }))}
            title="Proses Produksi"
            description="Tahapan produksi yang ditampilkan berurutan di halaman Export Info."
            indexHref={itemsIndex()}
            createHref={itemCreate()}
            editHref={(id) => itemEdit(id)}
            destroyHref={(id) => destroyItem(id)}
            reorderHref={reorderItems()}
            createLabel="Tambah Tahapan"
            emptyTitle="Belum ada tahapan produksi"
            emptyDescription="Tambahkan tahapan agar buyer paham bagaimana briket dibuat."
            deleteDescription="Tahapan hilang dari halaman Export Info. Datanya dipindahkan ke arsip, bukan dihapus permanen."
            itemNoun="tahapan"
        />
    );
}
