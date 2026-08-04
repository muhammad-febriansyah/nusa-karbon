import PageHeader from '@/components/shared/page-header';
import { dashboard } from '@/routes/admin';
import {
    create as postCreate,
    index as postsIndex,
} from '@/routes/admin/posts';
import type { PostCategory, SelectOption } from '@/types/models';
import PostForm from './post-form';

type Props = {
    categories: PostCategory[];
    statuses: SelectOption[];
};

export default function PostCreate({ categories, statuses }: Props) {
    return (
        <>
            <PageHeader
                title="Tulis Artikel"
                description="Susun artikel baru, atur kategorinya, lalu simpan sebagai draf atau langsung terbitkan."
                breadcrumbs={[
                    { title: 'Dashboard', href: dashboard() },
                    { title: 'Artikel', href: postsIndex() },
                    { title: 'Tulis Artikel', href: postCreate() },
                ]}
            />

            <PostForm post={null} categories={categories} statuses={statuses} />
        </>
    );
}
