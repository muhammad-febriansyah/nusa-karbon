import PageHeader from '@/components/shared/page-header';
import { dashboard } from '@/routes/admin';
import {
    create as itemCreate,
    index as itemsIndex,
} from '@/routes/admin/production-steps';
import ProductionStepForm from './production-step-form';

export default function ProductionStepCreate() {
    return (
        <>
            <PageHeader
                title="Tambah Tahapan Produksi"
                description="Jelaskan satu tahapan proses produksi beserta gambarnya."
                breadcrumbs={[
                    { title: 'Dashboard', href: dashboard() },
                    { title: 'Proses Produksi', href: itemsIndex() },
                    { title: 'Tambah Tahapan Produksi', href: itemCreate() },
                ]}
            />

            <ProductionStepForm item={null} />
        </>
    );
}
