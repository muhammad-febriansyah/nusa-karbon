import SortableContentIndex from '@/components/shared/sortable-content-index';
import {
    create as itemCreate,
    destroy as destroyItem,
    edit as itemEdit,
    index as itemsIndex,
    reorder as reorderItems,
} from '@/routes/admin/team-members';
import type { TeamMember } from '@/types/models';

const LOCALE = 'id';

type Props = {
    items: TeamMember[];
};

export default function TeamMemberIndex({ items }: Props) {
    return (
        <SortableContentIndex
            items={items.map((item) => ({
                id: item.id,
                title: item.name,
                subtitle: item.position?.[LOCALE] ?? null,
                imageUrl: item.photo_url,
                isActive: item.is_active,
            }))}
            title="Tim"
            description="Anggota tim yang ditampilkan di halaman About Us."
            indexHref={itemsIndex()}
            createHref={itemCreate()}
            editHref={(id) => itemEdit(id)}
            destroyHref={(id) => destroyItem(id)}
            reorderHref={reorderItems()}
            createLabel="Tambah Anggota"
            emptyTitle="Belum ada anggota tim"
            emptyDescription="Tambahkan anggota tim agar halaman About Us punya wajah, bukan sekadar teks."
            deleteDescription="Anggota hilang dari halaman About Us. Datanya dipindahkan ke arsip, bukan dihapus permanen."
            itemNoun="anggota tim"
        />
    );
}
