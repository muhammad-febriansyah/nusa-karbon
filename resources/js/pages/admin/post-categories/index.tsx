import SortableContentIndex from '@/components/shared/sortable-content-index';
import {
    create as itemCreate,
    destroy as destroyItem,
    edit as itemEdit,
    index as itemsIndex,
    reorder as reorderItems,
} from '@/routes/admin/post-categories';
import type { PostCategory } from '@/types/models';

const LOCALE = 'id';

type Props = {
    items: PostCategory[];
};

export default function PostCategoryIndex({ items }: Props) {
    return (
        <SortableContentIndex
            items={items.map((item) => ({
                id: item.id,
                title: item.name?.[LOCALE] ?? '—',
                subtitle:
                    item.posts_count === undefined
                        ? (item.slug?.[LOCALE] ?? null)
                        : `${item.posts_count} artikel`,
                isActive: item.is_active,
            }))}
            title="Kategori Artikel"
            description="Kelompok artikel blog beserta urutan tampilnya."
            indexHref={itemsIndex()}
            createHref={itemCreate()}
            editHref={(id) => itemEdit(id)}
            destroyHref={(id) => destroyItem(id)}
            reorderHref={reorderItems()}
            createLabel="Tambah Kategori"
            emptyTitle="Belum ada kategori artikel"
            emptyDescription="Tambahkan kategori agar artikel mudah ditelusuri pembaca."
            deleteDescription="Kategori dipindahkan ke arsip. Artikel di dalamnya tidak ikut terhapus, hanya kehilangan kategorinya."
            itemNoun="kategori"
        />
    );
}
