import PageHeader from '@/components/shared/page-header';
import { dashboard } from '@/routes/admin';
import {
    edit as itemEdit,
    index as itemsIndex,
} from '@/routes/admin/faq-categories';
import type { FaqCategory } from '@/types/models';
import FaqCategoryForm from './faq-category-form';

const LOCALE = 'id';

type Props = {
    item: FaqCategory;
};

export default function FaqCategoryEdit({ item }: Props) {
    const label = item.name?.[LOCALE] ?? 'Kategori';

    return (
        <>
            <PageHeader
                title={`Ubah ${label}`}
                description="Perbarui nama dan status kategori FAQ ini."
                breadcrumbs={[
                    { title: 'Dashboard', href: dashboard() },
                    { title: 'Kategori FAQ', href: itemsIndex() },
                    { title: label, href: itemEdit(item.id) },
                ]}
            />

            <FaqCategoryForm item={item} />
        </>
    );
}
