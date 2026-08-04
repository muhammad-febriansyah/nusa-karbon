import { Link, router } from '@inertiajs/react';
import { Eye } from 'lucide-react';
import { DataTable } from '@/components/data-table/data-table';
import type { DataTableColumn } from '@/components/data-table/data-table';
import PageHeader from '@/components/shared/page-header';
import StatusBadge from '@/components/shared/status-badge';
import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { cn } from '@/lib/utils';
import { dashboard } from '@/routes/admin';
import {
    index as messagesIndex,
    show as messageShow,
} from '@/routes/admin/contact-messages';
import type { ContactMessage } from '@/types/models';
import type { Paginated, TableFilters } from '@/types/pagination';

type Props = {
    messages: Paginated<ContactMessage>;
    unreadCount: number;
    filters: TableFilters;
};

export default function ContactMessageIndex({
    messages,
    unreadCount,
    filters,
}: Props) {
    const applyFilter = (key: string, value: string | undefined) =>
        router.get(
            messagesIndex().url,
            { ...filters, [key]: value, page: 1 },
            { preserveState: true, replace: true },
        );

    const columns: DataTableColumn<ContactMessage>[] = [
        {
            id: 'name',
            header: 'Pengirim',
            sortKey: 'name',
            cell: (message) => (
                <div className="flex flex-col leading-tight">
                    <span className={cn(!message.is_read && 'font-semibold')}>
                        {message.name}
                    </span>
                    <span dir="ltr" className="text-xs text-muted-foreground">
                        {message.email}
                    </span>
                </div>
            ),
        },
        {
            id: 'subject',
            header: 'Subjek',
            cell: (message) => message.subject ?? '—',
        },
        {
            id: 'created_at',
            header: 'Masuk',
            sortKey: 'created_at',
            cell: (message) => (
                <span className="tabular-nums">{message.created_at}</span>
            ),
        },
        {
            id: 'status',
            header: 'Status',
            cell: (message) => (
                <div className="flex items-center gap-2">
                    <StatusBadge
                        label={message.is_read ? 'Dibaca' : 'Baru'}
                        tone={message.is_read ? 'muted' : 'info'}
                    />
                    {message.is_spam && (
                        <StatusBadge label="Spam" tone="destructive" />
                    )}
                </div>
            ),
        },
        {
            id: 'actions',
            header: 'Aksi',
            sticky: true,
            cell: (message) => (
                <Button asChild variant="detail" size="sm">
                    <Link href={messageShow(message.id)}>
                        <Eye />
                        Detail
                    </Link>
                </Button>
            ),
        },
    ];

    return (
        <>
            <PageHeader
                title="Pesan Kontak"
                description="Pesan umum dari form kontak halaman publik."
                breadcrumbs={[
                    { title: 'Dashboard', href: dashboard() },
                    { title: 'Pesan Kontak', href: messagesIndex() },
                ]}
            />

            <DataTable
                rows={messages}
                columns={columns}
                filters={filters}
                rowId={(message) => message.id}
                reloadOnly={['messages', 'unreadCount', 'filters']}
                searchPlaceholder="Cari nama, email, atau subjek…"
                emptyTitle="Belum ada pesan"
                emptyDescription={
                    unreadCount === 0
                        ? 'Pesan dari form kontak akan muncul di sini.'
                        : 'Tidak ada pesan yang cocok dengan filter ini.'
                }
                filterSlot={
                    <>
                        <Select
                            value={filters.unread === '1' ? '1' : 'all'}
                            onValueChange={(value) =>
                                applyFilter(
                                    'unread',
                                    value === 'all' ? undefined : value,
                                )
                            }
                        >
                            <SelectTrigger className="w-full sm:w-40">
                                <SelectValue placeholder="Semua" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">Semua</SelectItem>
                                <SelectItem value="1">Belum dibaca</SelectItem>
                            </SelectContent>
                        </Select>

                        <Select
                            value={
                                filters.is_spam === undefined ||
                                filters.is_spam === ''
                                    ? 'all'
                                    : String(filters.is_spam)
                            }
                            onValueChange={(value) =>
                                applyFilter(
                                    'is_spam',
                                    value === 'all' ? undefined : value,
                                )
                            }
                        >
                            <SelectTrigger className="w-full sm:w-40">
                                <SelectValue placeholder="Semua pesan" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">Semua pesan</SelectItem>
                                <SelectItem value="0">Bukan spam</SelectItem>
                                <SelectItem value="1">Spam</SelectItem>
                            </SelectContent>
                        </Select>
                    </>
                }
            />
        </>
    );
}
