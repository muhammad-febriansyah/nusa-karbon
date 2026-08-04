import { Link } from '@inertiajs/react';
import { format, parseISO } from 'date-fns';
import { id as idLocale } from 'date-fns/locale';
import { ArrowRight, Clock, Newspaper } from 'lucide-react';
import BlurFade from '@/components/public/blur-fade';
import Section from '@/components/public/section';
import { Button } from '@/components/ui/button';
import blog from '@/routes/public/blog';
import type { PublicPostCard } from '@/types';

function PostCard({ post }: { post: PublicPostCard }) {
    const publishedAt =
        post.published_at === null ? null : parseISO(post.published_at);

    return (
        <article className="group rounded-card flex h-full flex-col overflow-hidden border border-border bg-card transition-[border-color,transform] duration-300 hover:-translate-y-1 hover:border-primary/50">
            <div className="aspect-16/9 overflow-hidden bg-muted">
                {post.cover_url ? (
                    <img
                        src={post.cover_url}
                        alt={post.title ?? ''}
                        loading="lazy"
                        decoding="async"
                        className="size-full object-cover transition-transform duration-500 group-hover:scale-105"
                    />
                ) : (
                    <div className="flex size-full items-center justify-center text-muted-foreground">
                        <Newspaper className="size-8" />
                    </div>
                )}
            </div>

            <div className="flex flex-1 flex-col p-5">
                <div className="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-muted-foreground">
                    {post.category && (
                        <span className="font-medium text-primary">
                            {post.category}
                        </span>
                    )}
                    {publishedAt && (
                        <time dateTime={post.published_at ?? undefined}>
                            {format(publishedAt, 'd MMMM yyyy', {
                                locale: idLocale,
                            })}
                        </time>
                    )}
                    {post.reading_minutes !== null && (
                        <span className="flex items-center gap-1">
                            <Clock className="size-3.5" />
                            <span dir="ltr">{post.reading_minutes} menit</span>
                        </span>
                    )}
                </div>

                <h3 className="mt-3 text-lg leading-snug font-semibold tracking-tight">
                    {post.slug === null ? (
                        post.title
                    ) : (
                        <Link
                            href={blog.show(post.slug)}
                            className="transition-colors hover:text-primary"
                        >
                            {post.title}
                        </Link>
                    )}
                </h3>

                {post.excerpt && (
                    <p className="mt-2 line-clamp-3 flex-1 text-sm leading-relaxed text-muted-foreground">
                        {post.excerpt}
                    </p>
                )}
            </div>
        </article>
    );
}

export default function LatestPostSection({
    items,
}: {
    items: PublicPostCard[];
}) {
    if (items.length === 0) {
        return null;
    }

    return (
        <Section
            eyebrow="Wawasan"
            title="Catatan dari lapangan"
            description="Standar mutu, dokumen ekspor, dan hal-hal teknis yang sering ditanyakan buyer."
            action={
                <Button asChild variant="export">
                    <Link href={blog.index()}>
                        Semua Artikel
                        <ArrowRight />
                    </Link>
                </Button>
            }
        >
            <div className="grid gap-6 md:grid-cols-3">
                {items.map((post, index) => (
                    <BlurFade
                        key={post.id}
                        delay={index * 0.06}
                        className="h-full"
                    >
                        <PostCard post={post} />
                    </BlurFade>
                ))}
            </div>
        </Section>
    );
}
