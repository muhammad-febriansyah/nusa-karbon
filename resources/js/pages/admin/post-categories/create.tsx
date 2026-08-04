import PageHeader from '@/components/shared/page-header';
import { dashboard } from '@/routes/admin';
import {
    create as itemCreate,
    index as itemsIndex,
} from '@/routes/admin/post-categories';
import PostCategoryForm from './post-category-form';

export default function PostCategoryCreate() {
    return (
        <>
            <PageHeader
                title="Tambah Kategori Artikel"
                description="Kelompok baru untuk mengorganisir artikel blog."
                breadcrumbs={[
                    { title: 'Dashboard', href: dashboard() },
                    { title: 'Kategori Artikel', href: itemsIndex() },
                    { title: 'Tambah Kategori Artikel', href: itemCreate() },
                ]}
            />

            <PostCategoryForm item={null} />
        </>
    );
}
