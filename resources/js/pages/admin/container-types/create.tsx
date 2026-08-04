import PageHeader from '@/components/shared/page-header';
import { dashboard } from '@/routes/admin';
import {
    create as containerTypeCreate,
    index as containerTypesIndex,
} from '@/routes/admin/container-types';
import ContainerTypeForm from './container-type-form';

export default function ContainerTypeCreate() {
    return (
        <>
            <PageHeader
                title="Tambah Tipe Kontainer"
                description="Isi kode, nama, dan kapasitas muat kontainer yang dipakai Container Calculator."
                breadcrumbs={[
                    { title: 'Dashboard', href: dashboard() },
                    { title: 'Tipe Kontainer', href: containerTypesIndex() },
                    {
                        title: 'Tambah Tipe Kontainer',
                        href: containerTypeCreate(),
                    },
                ]}
            />

            <ContainerTypeForm containerType={null} />
        </>
    );
}
