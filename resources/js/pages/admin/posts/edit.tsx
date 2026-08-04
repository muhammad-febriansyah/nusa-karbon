import { Link } from '@inertiajs/react';
import { Eye } from 'lucide-react';
import PageHeader from '@/components/shared/page-header';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes/admin';
import {
    edit as postEdit,
    index as postsIndex,
    preview as postPreview,
} from '@/routes/admin/posts';
import type { Post, PostCategory, SelectOption } from '@/types/models';
import PostForm from './post-form';

const LOCALE = 'id';

type Props = {
    post: Post;
    categories: PostCategory[];
    statuses: SelectOption[];
};

export default function PostEdit({ post, categories, statuses }: Props) {
    const label = post.title?.[LOCALE] ?? 'Artikel';

    return (
        <>
            <PageHeader
                title={`Ubah ${label}`}
                description="Perbarui isi, kategori, dan jadwal tayang artikel ini."
                breadcrumbs={[
                    { title: 'Dashboard', href: dashboard() },
                    { title: 'Artikel', href: postsIndex() },
                    { title: label, href: postEdit(post.id) },
                ]}
                actions={
                    <Button asChild variant="detail">
                        <Link href={postPreview(post.id)}>
                            <Eye />
                            Pratinjau
                        </Link>
                    </Button>
                }
            />

            <PostForm post={post} categories={categories} statuses={statuses} />
        </>
    );
}
