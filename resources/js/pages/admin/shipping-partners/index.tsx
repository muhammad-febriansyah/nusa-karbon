import SortableContentIndex from '@/components/shared/sortable-content-index';
import {
    create as itemCreate,
    destroy as destroyItem,
    edit as itemEdit,
    index as itemsIndex,
    reorder as reorderItems,
} from '@/routes/admin/shipping-partners';
import type { ShippingPartner } from '@/types/models';

type Props = {
    items: ShippingPartner[];
};

export default function ShippingPartnerIndex({ items }: Props) {
    return (
        <SortableContentIndex
            items={items.map((item) => ({
                id: item.id,
                title: item.name,
                subtitle: item.website_url,
                imageUrl: item.logo_url,
                isActive: item.is_active,
            }))}
            title="Mitra Pelayaran"
            description="Perusahaan pelayaran yang logonya tampil di halaman Export Info."
            indexHref={itemsIndex()}
            createHref={itemCreate()}
            editHref={(id) => itemEdit(id)}
            destroyHref={(id) => destroyItem(id)}
            reorderHref={reorderItems()}
            createLabel="Tambah Mitra"
            emptyTitle="Belum ada mitra pelayaran"
            emptyDescription="Tambahkan mitra agar buyer melihat jalur pengiriman yang dipakai."
            deleteDescription="Mitra hilang dari halaman Export Info. Datanya dipindahkan ke arsip, bukan dihapus permanen."
            itemNoun="mitra"
            imageFit="contain"
        />
    );
}
