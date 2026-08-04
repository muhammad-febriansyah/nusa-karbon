import PageHeader from '@/components/shared/page-header';
import { dashboard } from '@/routes/admin';
import {
    edit as itemEdit,
    index as itemsIndex,
} from '@/routes/admin/production-steps';
import type { ProductionStep } from '@/types/models';
import ProductionStepForm from './production-step-form';

const LOCALE = 'id';

type Props = {
    item: ProductionStep;
};

export default function ProductionStepEdit({ item }: Props) {
    const label = item.title?.[LOCALE] ?? 'Tahapan';

    return (
        <>
            <PageHeader
                title={`Ubah ${label}`}
                description="Perbarui penjelasan dan gambar tahapan ini."
                breadcrumbs={[
                    { title: 'Dashboard', href: dashboard() },
                    { title: 'Proses Produksi', href: itemsIndex() },
                    { title: label, href: itemEdit(item.id) },
                ]}
            />

            <ProductionStepForm item={item} />
        </>
    );
}
