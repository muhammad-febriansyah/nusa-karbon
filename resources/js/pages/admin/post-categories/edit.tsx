import PageHeader from '@/components/shared/page-header';
import { dashboard } from '@/routes/admin';
import {
    edit as itemEdit,
    index as itemsIndex,
} from '@/routes/admin/post-categories';
import type { PostCategory } from '@/types/models';
import PostCategoryForm from './post-category-form';

const LOCALE = 'id';

type Props = {
    item: PostCategory;
};

export default function PostCategoryEdit({ item }: Props) {
    const label = item.name?.[LOCALE] ?? 'Kategori';

    return (
        <>
            <PageHeader
                title={`Ubah ${label}`}
                description="Perbarui nama, slug, dan deskripsi kategori artikel ini."
                breadcrumbs={[
                    { title: 'Dashboard', href: dashboard() },
                    { title: 'Kategori Artikel', href: itemsIndex() },
                    { title: label, href: itemEdit(item.id) },
                ]}
            />

            <PostCategoryForm item={item} />
        </>
    );
}
