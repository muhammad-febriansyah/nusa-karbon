import PageHeader from '@/components/shared/page-header';
import { dashboard } from '@/routes/admin';
import {
    create as itemCreate,
    index as itemsIndex,
} from '@/routes/admin/faq-categories';
import FaqCategoryForm from './faq-category-form';

export default function FaqCategoryCreate() {
    return (
        <>
            <PageHeader
                title="Tambah Kategori FAQ"
                description="Kelompok baru untuk mengorganisir pertanyaan di halaman FAQ."
                breadcrumbs={[
                    { title: 'Dashboard', href: dashboard() },
                    { title: 'Kategori FAQ', href: itemsIndex() },
                    { title: 'Tambah Kategori FAQ', href: itemCreate() },
                ]}
            />

            <FaqCategoryForm item={null} />
        </>
    );
}
