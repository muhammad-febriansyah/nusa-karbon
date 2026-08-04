import SortableContentIndex from '@/components/shared/sortable-content-index';
import {
    create as itemCreate,
    destroy as destroyItem,
    edit as itemEdit,
    index as itemsIndex,
    reorder as reorderItems,
} from '@/routes/admin/features';
import type { Feature } from '@/types/models';

const LOCALE = 'id';

type Props = {
    items: Feature[];
};

export default function FeatureIndex({ items }: Props) {
    return (
        <SortableContentIndex
            items={items.map((item) => ({
                id: item.id,
                title: item.title?.[LOCALE] ?? '—',
                subtitle: item.description?.[LOCALE] ?? null,
                imageUrl: item.image_url,
                badge: item.icon,
                isActive: item.is_active,
            }))}
            title="Keunggulan"
            description="Poin “Why Choose Us” yang tampil di beranda."
            indexHref={itemsIndex()}
            createHref={itemCreate()}
            editHref={(id) => itemEdit(id)}
            destroyHref={(id) => destroyItem(id)}
            reorderHref={reorderItems()}
            createLabel="Tambah Keunggulan"
            emptyTitle="Belum ada keunggulan"
            emptyDescription="Tambahkan poin keunggulan agar beranda punya alasan konkret untuk buyer."
            deleteDescription="Keunggulan hilang dari beranda. Datanya dipindahkan ke arsip, bukan dihapus permanen."
            itemNoun="keunggulan"
        />
    );
}
