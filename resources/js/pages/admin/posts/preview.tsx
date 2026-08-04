import { Link } from '@inertiajs/react';
import { SquarePen } from 'lucide-react';
import PageHeader from '@/components/shared/page-header';
import Panel from '@/components/shared/panel';
import StatusBadge from '@/components/shared/status-badge';
import type { StatusTone } from '@/components/shared/status-badge';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes/admin';
import {
    edit as postEdit,
    index as postsIndex,
    preview as postPreview,
} from '@/routes/admin/posts';
import type { Post } from '@/types/models';

const LOCALE = 'id';

type Props = {
    post: Post;
};

/**
 * Pratinjau isi artikel sebelum terbit. Tata letaknya menyerupai halaman baca
 * publik supaya kesalahan format terlihat sebelum pembaca menemukannya.
 */
export default function PostPreview({ post }: Props) {
    const label = post.title?.[LOCALE] ?? 'Artikel';

    return (
        <>
            <PageHeader
                title="Pratinjau Artikel"
                description="Tampilan isi artikel seperti yang akan dibaca pengunjung."
                breadcrumbs={[
                    { title: 'Dashboard', href: dashboard() },
                    { title: 'Artikel', href: postsIndex() },
                    { title: label, href: postPreview(post.id) },
                ]}
                actions={
                    <Button asChild variant="edit">
                        <Link href={postEdit(post.id)}>
                            <SquarePen />
                            Ubah Artikel
                        </Link>
                    </Button>
                }
            />

            <Panel>
                <article className="mx-auto max-w-3xl">
                    <div className="mb-4 flex flex-wrap items-center gap-2">
                        <StatusBadge
                            label={post.status_label}
                            tone={post.status_color as StatusTone}
                        />
                        {post.category_name && (
                            <StatusBadge
                                label={post.category_name[LOCALE] ?? '—'}
                                tone="info"
                            />
                        )}
                        <span className="text-xs text-muted-foreground">
                            {post.published_at_display ?? 'Belum tayang'}
                            {post.reading_minutes !== null &&
                                ` · ${post.reading_minutes} menit baca`}
                            {post.author_name && ` · ${post.author_name}`}
                        </span>
                    </div>

                    {post.cover_url && (
                        <img
                            src={post.cover_url}
                            alt=""
                            className="mb-6 w-full rounded-[var(--radius)] border border-border object-cover"
                        />
                    )}

                    <h1 className="text-3xl font-semibold tracking-tight">
                        {label}
                    </h1>

                    {post.excerpt?.[LOCALE] && (
                        <p className="mt-3 text-base text-muted-foreground">
                            {post.excerpt[LOCALE]}
                        </p>
                    )}

                    <div
                        className="prose prose-sm mt-6 max-w-none"
                        // Isi sudah dibersihkan Purifier saat disimpan.
                        dangerouslySetInnerHTML={{
                            __html: post.content?.[LOCALE] ?? '',
                        }}
                    />

                    {post.tags && post.tags.length > 0 && (
                        <div className="mt-8 flex flex-wrap gap-1.5">
                            {post.tags.map((tag) => (
                                <span
                                    key={tag}
                                    className="rounded-full bg-muted px-2.5 py-0.5 text-[11px] text-muted-foreground"
                                >
                                    {tag}
                                </span>
                            ))}
                        </div>
                    )}
                </article>
            </Panel>
        </>
    );
}
