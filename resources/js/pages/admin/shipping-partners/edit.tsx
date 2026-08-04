import PageHeader from '@/components/shared/page-header';
import { dashboard } from '@/routes/admin';
import {
    edit as itemEdit,
    index as itemsIndex,
} from '@/routes/admin/shipping-partners';
import type { ShippingPartner } from '@/types/models';
import ShippingPartnerForm from './shipping-partner-form';

type Props = {
    item: ShippingPartner;
};

export default function ShippingPartnerEdit({ item }: Props) {
    const label = item.name;

    return (
        <>
            <PageHeader
                title={`Ubah ${label}`}
                description="Perbarui logo dan alamat situs mitra ini."
                breadcrumbs={[
                    { title: 'Dashboard', href: dashboard() },
                    { title: 'Mitra Pelayaran', href: itemsIndex() },
                    { title: label, href: itemEdit(item.id) },
                ]}
            />

            <ShippingPartnerForm item={item} />
        </>
    );
}
