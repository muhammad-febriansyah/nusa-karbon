import PageHeader from '@/components/public/page-header';
import PostCard from '@/components/public/post-card';
import SeoHead from '@/components/public/seo-head';
import { home } from '@/routes/public';
import blog from '@/routes/public/blog';
import type { PublicPostCard, PublicPostDetail } from '@/types';

type Props = {
    post: PublicPostDetail;
    related: PublicPostCard[];
};

const dateFormat = new Intl.DateTimeFormat('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
});

/**
 * Satu artikel.
 *
 * Lebar bacanya dibatasi `max-w-2xl` — sekitar 70 karakter per baris. Teks
 * panjang selebar layar membuat mata kehilangan awal baris berikutnya, dan itu
 * kesalahan paling mahal di halaman yang isinya memang untuk dibaca.
 */
export default function BlogShow({ post, related }: Props) {
    const publishedAt =
        post.published_at === null ? null : new Date(post.published_at);

    return (
        <>
            <SeoHead
                title={post.title ?? 'Artikel'}
                description={post.excerpt}
            />

            <PageHeader
                eyebrow={post.category ?? undefined}
                title={post.title ?? 'Artikel'}
                description={post.excerpt}
                breadcrumbs={[
                    { name: 'Beranda', href: home() },
                    { name: 'Blog', href: blog.index() },
                ]}
            >
                <div className="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-muted-foreground">
                    {post.author && <span>{post.author}</span>}
                    {publishedAt && (
                        <time dateTime={post.published_at ?? undefined}>
                            {dateFormat.format(publishedAt)}
                        </time>
                    )}
                    {post.reading_minutes !== null && (
                        <span dir="ltr" className="tabular-nums">
                            {post.reading_minutes} menit baca
                        </span>
                    )}
                </div>
            </PageHeader>

            <article className="mx-auto w-full max-w-7xl px-6 py-12">
                {post.cover_url && (
                    <img
                        src={post.cover_url}
                        alt=""
                        fetchPriority="high"
                        decoding="async"
                        className="rounded-panel mx-auto aspect-16/9 w-full max-w-4xl object-cover"
                    />
                )}

                {post.content && (
                    <div
                        className="article-body mx-auto mt-10 max-w-2xl text-base"
                        /*
                         * HTML-nya sudah dibersihkan Purifier saat disimpan
                         * admin — lihat pipeline penyimpanan artikel. Isi yang
                         * belum lewat pembersihan itu tidak boleh sampai ke
                         * sini.
                         */
                        dangerouslySetInnerHTML={{ __html: post.content }}
                    />
                )}

                {post.tags.length > 0 && (
                    <ul className="mx-auto mt-10 flex max-w-2xl flex-wrap gap-2">
                        {post.tags.map((tag) => (
                            <li
                                key={tag}
                                className="rounded-full border border-border/70 bg-card px-3 py-1 text-xs text-muted-foreground"
                            >
                                {tag}
                            </li>
                        ))}
                    </ul>
                )}
            </article>

            {related.length > 0 && (
                <section className="border-t border-border/70">
                    <div className="mx-auto w-full max-w-7xl px-6 py-12">
                        <h2 className="font-display text-2xl leading-tight font-bold tracking-[-0.02em]">
                            Artikel lain
                        </h2>

                        <div className="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            {related.map((item) => (
                                <PostCard key={item.id} post={item} />
                            ))}
                        </div>
                    </div>
                </section>
            )}
        </>
    );
}
