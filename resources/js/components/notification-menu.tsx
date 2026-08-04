import { Link, usePage } from '@inertiajs/react';
import { Bell, Inbox, MessageSquare } from 'lucide-react';
import { useState } from 'react';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Spinner } from '@/components/ui/spinner';
import { notifications as notificationsRoute } from '@/routes/admin';
import { index as contactMessagesIndex } from '@/routes/admin/contact-messages';
import { index as rfqsIndex } from '@/routes/admin/rfqs';

type Notification = {
    id: string;
    type: 'rfq' | 'contact';
    title: string;
    subtitle: string;
    time: string;
    url: string;
};

/**
 * Lonceng notifikasi: RFQ dan pesan kontak yang belum dibaca.
 *
 * Isinya diambil saat dropdown dibuka, bukan ikut tiap kunjungan halaman —
 * angka pada titik merah sudah cukup untuk keadaan tertutup, dan memuat daftar
 * di setiap request hanya menambah dua query yang jarang dilihat.
 */
export default function NotificationMenu() {
    const { unread } = usePage().props;
    const [items, setItems] = useState<Notification[]>([]);
    const [loading, setLoading] = useState(false);
    const [loaded, setLoaded] = useState(false);

    const total = unread.rfq + unread.contactMessages;

    const load = () => {
        setLoading(true);

        fetch(notificationsRoute().url, {
            headers: { Accept: 'application/json' },
        })
            .then((response) => (response.ok ? response.json() : { items: [] }))
            .then((data: { items: Notification[] }) =>
                setItems(data.items ?? []),
            )
            .catch(() => setItems([]))
            .finally(() => {
                setLoading(false);
                setLoaded(true);
            });
    };

    return (
        <DropdownMenu
            onOpenChange={(open) => {
                if (open && !loaded) {
                    load();
                }
            }}
        >
            <DropdownMenuTrigger className="relative flex text-muted-foreground transition-colors outline-none hover:text-foreground focus-visible:text-foreground">
                <Bell aria-hidden className="size-5" />
                <span className="sr-only">
                    Notifikasi{total > 0 ? `, ${total} belum dibaca` : ''}
                </span>
                {total > 0 && (
                    <span
                        aria-hidden
                        className="absolute -end-px -top-px size-[7px] rounded-full border-[1.5px] border-card bg-destructive"
                    />
                )}
            </DropdownMenuTrigger>

            <DropdownMenuContent
                align="end"
                className="w-80 rounded-[var(--radius)] p-0"
            >
                <div className="flex items-center justify-between border-b border-border px-3.5 py-2.5">
                    <span className="text-sm font-semibold">Notifikasi</span>
                    <span className="text-xs text-muted-foreground tabular-nums">
                        {total} belum dibaca
                    </span>
                </div>

                {loading && (
                    <div className="flex items-center justify-center gap-2 py-6 text-sm text-muted-foreground">
                        <Spinner />
                        Memuat…
                    </div>
                )}

                {!loading && items.length === 0 && (
                    <p className="px-3.5 py-6 text-center text-sm text-muted-foreground">
                        Tidak ada yang belum dibaca.
                    </p>
                )}

                {!loading && items.length > 0 && (
                    <ul className="max-h-80 overflow-y-auto">
                        {items.map((item) => (
                            <li key={item.id}>
                                <Link
                                    href={item.url}
                                    className="flex items-start gap-2.5 border-b border-border px-3.5 py-2.5 transition-colors last:border-b-0 hover:bg-muted"
                                >
                                    {item.type === 'rfq' ? (
                                        <Inbox
                                            aria-hidden
                                            className="mt-0.5 size-4 shrink-0 text-primary"
                                        />
                                    ) : (
                                        <MessageSquare
                                            aria-hidden
                                            className="mt-0.5 size-4 shrink-0 text-info"
                                        />
                                    )}
                                    <span className="flex min-w-0 flex-col leading-tight">
                                        <span className="truncate text-[13px] font-medium">
                                            {item.title}
                                        </span>
                                        <span className="truncate text-xs text-muted-foreground">
                                            {item.subtitle}
                                        </span>
                                        <span className="text-[11px] text-muted-foreground">
                                            {item.time}
                                        </span>
                                    </span>
                                </Link>
                            </li>
                        ))}
                    </ul>
                )}

                <div className="flex items-center justify-between border-t border-border px-3.5 py-2">
                    <Link
                        href={rfqsIndex()}
                        className="text-xs font-medium text-primary hover:underline"
                    >
                        Buka RFQ Inbox
                    </Link>
                    <Link
                        href={contactMessagesIndex()}
                        className="text-xs font-medium text-primary hover:underline"
                    >
                        Pesan Kontak
                    </Link>
                </div>
            </DropdownMenuContent>
        </DropdownMenu>
    );
}
