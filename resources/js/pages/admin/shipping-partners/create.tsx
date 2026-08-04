import PageHeader from '@/components/shared/page-header';
import { dashboard } from '@/routes/admin';
import {
    create as itemCreate,
    index as itemsIndex,
} from '@/routes/admin/shipping-partners';
import ShippingPartnerForm from './shipping-partner-form';

export default function ShippingPartnerCreate() {
    return (
        <>
            <PageHeader
                title="Tambah Mitra Pelayaran"
                description="Nama, logo, dan alamat situs mitra pelayaran."
                breadcrumbs={[
                    { title: 'Dashboard', href: dashboard() },
                    { title: 'Mitra Pelayaran', href: itemsIndex() },
                    { title: 'Tambah Mitra Pelayaran', href: itemCreate() },
                ]}
            />

            <ShippingPartnerForm item={null} />
        </>
    );
}
