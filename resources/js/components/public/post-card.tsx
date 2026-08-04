import { Link } from '@inertiajs/react';
import { ArrowRight } from 'lucide-react';
import { cn } from '@/lib/utils';
import blog from '@/routes/public/blog';
import type { PublicPostCard } from '@/types';

const dateFormat = new Intl.DateTimeFormat('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
});

type Props = {
    post: PublicPostCard;
    /**
     * `lead` membalik kartu jadi mendatar dengan gambar besar di samping —
     * dipakai untuk artikel paling depan pada satu halaman arsip.
     */
    variant?: 'default' | 'lead';
};

/**
 * Kartu artikel.
 *
 * Dipakai bersama beranda, daftar blog, dan bagian "artikel lain" di bawah satu
 * tulisan — supaya artikel yang sama tidak pernah tampil berbeda tergantung
 * halaman mana yang sedang dibuka.
 */
export default function PostCard({ post, variant = 'default' }: Props) {
    const publishedAt =
        post.published_at === null ? null : new Date(post.published_at);
    const isLead = variant === 'lead';

    return (
        <article
            className={cn(
                'group rounded-panel relative flex h-full flex-col overflow-hidden border border-border/70 bg-card shadow-card transition-[box-shadow,border-color] duration-200 focus-within:border-foreground/20 hover:border-foreground/20 hover:shadow-dropdown',
                isLead &&
                    'lg:grid lg:min-h-80 lg:grid-cols-[minmax(0,1.15fr)_minmax(0,1fr)] lg:items-stretch',
            )}
        >
            <div
                className={cn(
                    'shrink-0 overflow-hidden border-b border-border/70 bg-muted',
                    isLead
                        ? 'aspect-16/9 sm:aspect-21/9 lg:relative lg:aspect-auto lg:h-full lg:border-e lg:border-b-0'
                        : 'aspect-16/9',
                )}
            >
                {post.cover_url ? (
                    <img
                        src={post.cover_url}
                        alt=""
                        loading={isLead ? 'eager' : 'lazy'}
                        decoding="async"
                        className={cn(
                            'size-full object-cover',
                            /*
                             * Dilepas dari alur agar tinggi asli fotonya tidak
                             * ikut menentukan tinggi baris. Tanpa ini, foto
                             * potret menarik kartu utama sampai satu layar
                             * penuh sementara kolom teks di sebelahnya
                             * mengambang di tengah ruang kosong.
                             */
                            isLead && 'lg:absolute lg:inset-0',
                        )}
                    />
                ) : (
                    <div className="flex size-full items-center justify-center text-sm text-muted-foreground">
                        Belum ada gambar
                    </div>
                )}
            </div>

            <div
                className={cn(
                    'flex flex-1 flex-col p-5',
                    isLead && 'justify-center p-7 lg:p-9',
                )}
            >
                <div className="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-muted-foreground">
                    {post.category && (
                        <span className="font-medium">{post.category}</span>
                    )}
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

                <h3
                    className={cn(
                        'mt-2 font-display leading-snug font-bold tracking-tight',
                        isLead
                            ? 'text-2xl tracking-[-0.02em] text-balance lg:text-3xl'
                            : 'text-lg',
                    )}
                >
                    {/*
                     * Tanpa slug, artikel tidak punya alamat — judulnya tampil
                     * sebagai teks biasa ketimbang menautkan ke URL yang pasti
                     * 404. Tautannya dilebarkan ke seluruh kartu lewat `after:`,
                     * tetapi pembaca layar tetap mendengar satu tautan bernama
                     * judul artikel.
                     */}
                    {post.slug === null ? (
                        post.title
                    ) : (
                        <Link
                            href={blog.show(post.slug)}
                            className="after:absolute after:inset-0 focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
                        >
                            {post.title}
                        </Link>
                    )}
                </h3>

                {post.excerpt && (
                    <p
                        className={cn(
                            'mt-2 line-clamp-3 leading-relaxed text-muted-foreground',
                            isLead ? 'mt-3 text-base' : 'text-sm',
                        )}
                    >
                        {post.excerpt}
                    </p>
                )}

                {/*
                 * Bukan tautan kedua — seluruh kartu sudah satu tautan. Ini
                 * penanda arah supaya kartu besar terbaca sebagai sesuatu yang
                 * bisa ditekan, bukan sekadar gambar berjudul.
                 */}
                {isLead && post.slug !== null && (
                    <span
                        aria-hidden
                        className="mt-6 inline-flex items-center gap-2 text-sm font-medium text-primary"
                    >
                        Baca selengkapnya
                        <ArrowRight className="size-4 transition-transform duration-200 group-hover:translate-x-0.5 rtl:rotate-180 rtl:group-hover:-translate-x-0.5" />
                    </span>
                )}
            </div>
        </article>
    );
}
