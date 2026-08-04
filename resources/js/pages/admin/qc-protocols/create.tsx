import PageHeader from '@/components/shared/page-header';
import { dashboard } from '@/routes/admin';
import {
    create as itemCreate,
    index as itemsIndex,
} from '@/routes/admin/qc-protocols';
import QcProtocolForm from './qc-protocol-form';

export default function QcProtocolCreate() {
    return (
        <>
            <PageHeader
                title="Tambah Protokol QC"
                description="Jelaskan satu pengujian mutu beserta angka hasilnya."
                breadcrumbs={[
                    { title: 'Dashboard', href: dashboard() },
                    { title: 'Protokol QC', href: itemsIndex() },
                    { title: 'Tambah Protokol QC', href: itemCreate() },
                ]}
            />

            <QcProtocolForm item={null} />
        </>
    );
}
