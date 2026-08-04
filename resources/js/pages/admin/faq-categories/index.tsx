import SortableContentIndex from '@/components/shared/sortable-content-index';
import {
    create as itemCreate,
    destroy as destroyItem,
    edit as itemEdit,
    index as itemsIndex,
    reorder as reorderItems,
} from '@/routes/admin/faq-categories';
import type { FaqCategory } from '@/types/models';

const LOCALE = 'id';

type Props = {
    items: FaqCategory[];
};

export default function FaqCategoryIndex({ items }: Props) {
    return (
        <SortableContentIndex
            items={items.map((item) => ({
                id: item.id,
                title: item.name?.[LOCALE] ?? '—',
                subtitle:
                    item.faqs_count === undefined
                        ? null
                        : `${item.faqs_count} pertanyaan`,
                isActive: item.is_active,
            }))}
            title="Kategori FAQ"
            description="Kelompok pertanyaan pada halaman FAQ publik."
            indexHref={itemsIndex()}
            createHref={itemCreate()}
            editHref={(id) => itemEdit(id)}
            destroyHref={(id) => destroyItem(id)}
            reorderHref={reorderItems()}
            createLabel="Tambah Kategori"
            emptyTitle="Belum ada kategori FAQ"
            emptyDescription="Tambahkan kategori agar pertanyaan tidak menumpuk dalam satu daftar panjang."
            deleteDescription="Kategori dipindahkan ke arsip. Pertanyaan di dalamnya tidak ikut terhapus — semuanya berpindah ke kelompok Lainnya."
            itemNoun="kategori"
        />
    );
}
