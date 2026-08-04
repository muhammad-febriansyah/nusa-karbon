import { Link, router } from '@inertiajs/react';
import { Plus, Tags } from 'lucide-react';
import { useState } from 'react';
import { DeleteButton, EditButton } from '@/components/shared/action-buttons';
import ConfirmDialog from '@/components/shared/confirm-dialog';
import EmptyState from '@/components/shared/empty-state';
import PageHeader from '@/components/shared/page-header';
import Panel from '@/components/shared/panel';
import SortableList from '@/components/shared/sortable-list';
import StatusBadge from '@/components/shared/status-badge';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes/admin';
import { index as faqCategoriesIndex } from '@/routes/admin/faq-categories';
import {
    create as faqCreate,
    destroy as destroyFaq,
    edit as faqEdit,
    index as faqsIndex,
    reorder as reorderFaqs,
} from '@/routes/admin/faqs';
import type { Faq, FaqCategory } from '@/types/models';

const LOCALE = 'id';

type Props = {
    faqs: Faq[];
    categories: FaqCategory[];
};

type Group = {
    key: string;
    label: string;
    faqs: Faq[];
};

/**
 * Pertanyaan dikelompokkan per kategori dan diurutkan di dalam kelompoknya.
 * Menggeser baris hanya mengubah urutan dalam kategori itu — pindah kategori
 * dilakukan dari halaman ubah, supaya urutannya tidak kacau tanpa disadari.
 */
const groupFaqs = (faqs: Faq[], categories: FaqCategory[]): Group[] => {
    const groups: Group[] = categories.map((category) => ({
        key: `category-${category.id}`,
        label: category.name?.[LOCALE] ?? 'Tanpa nama',
        faqs: faqs.filter((faq) => faq.faq_category_id === category.id),
    }));

    const uncategorised = faqs.filter((faq) => faq.faq_category_id === null);

    if (uncategorised.length > 0) {
        groups.push({
            key: 'uncategorised',
            label: 'Lainnya',
            faqs: uncategorised,
        });
    }

    return groups.filter((group) => group.faqs.length > 0);
};

export default function FaqIndex({ faqs, categories }: Props) {
    const [rows, setRows] = useState(faqs);
    const [syncedFaqs, setSyncedFaqs] = useState(faqs);
    const [deleting, setDeleting] = useState<Faq | null>(null);
    const [processing, setProcessing] = useState(false);

    if (syncedFaqs !== faqs) {
        setSyncedFaqs(faqs);
        setRows(faqs);
    }

    const groups = groupFaqs(rows, categories);

    const handleReorder = (group: Group, next: Faq[]) => {
        const previous = rows;
        const reordered = next.map((faq, index) => ({
            ...faq,
            sort_order: index,
        }));
        const untouched = rows.filter(
            (faq) => !group.faqs.some((item) => item.id === faq.id),
        );

        setRows([...untouched, ...reordered]);

        router.post(
            reorderFaqs().url,
            { ids: next.map((faq) => faq.id) },
            {
                preserveScroll: true,
                onError: () => setRows(previous),
            },
        );
    };

    return (
        <>
            <PageHeader
                title="FAQ"
                description="Pertanyaan yang sering diajukan buyer, dikelompokkan per kategori."
                breadcrumbs={[
                    { title: 'Dashboard', href: dashboard() },
                    { title: 'FAQ', href: faqsIndex() },
                ]}
                actions={
                    <>
                        <Button asChild variant="filter">
                            <Link href={faqCategoriesIndex()}>
                                <Tags />
                                Kelola Kategori
                            </Link>
                        </Button>
                        <Button asChild variant="create">
                            <Link href={faqCreate()}>
                                <Plus />
                                Tambah FAQ
                            </Link>
                        </Button>
                    </>
                }
            />

            {groups.length === 0 ? (
                <Panel>
                    <EmptyState
                        title="Belum ada pertanyaan"
                        description="Tambahkan pertanyaan yang paling sering ditanyakan buyer beserta jawabannya."
                        action={
                            <Button asChild variant="create">
                                <Link href={faqCreate()}>
                                    <Plus />
                                    Tambah FAQ
                                </Link>
                            </Button>
                        }
                    />
                </Panel>
            ) : (
                groups.map((group) => (
                    <Panel
                        key={group.key}
                        title={group.label}
                        description={`${group.faqs.length} pertanyaan. Geser baris untuk mengubah urutannya di dalam kategori ini.`}
                    >
                        <SortableList
                            items={group.faqs}
                            itemId={(faq) => faq.id}
                            onReorder={(next) => handleReorder(group, next)}
                        >
                            {(faq) => (
                                <div className="flex flex-wrap items-center justify-between gap-3">
                                    <div className="flex min-w-0 flex-col leading-tight">
                                        <span className="truncate font-medium">
                                            {faq.question?.[LOCALE] ?? '—'}
                                        </span>
                                        <span className="truncate text-xs text-muted-foreground">
                                            {faq.answer?.[LOCALE] ?? ''}
                                        </span>
                                    </div>

                                    <div className="flex items-center gap-2">
                                        <StatusBadge
                                            label={
                                                faq.is_active
                                                    ? 'Aktif'
                                                    : 'Nonaktif'
                                            }
                                            tone={
                                                faq.is_active
                                                    ? 'success'
                                                    : 'muted'
                                            }
                                        />
                                        <EditButton href={faqEdit(faq.id)} />
                                        <DeleteButton
                                            onClick={() => setDeleting(faq)}
                                        />
                                    </div>
                                </div>
                            )}
                        </SortableList>
                    </Panel>
                ))
            )}

            <ConfirmDialog
                open={deleting !== null}
                onOpenChange={(open) => !open && setDeleting(null)}
                loading={processing}
                title={`Hapus pertanyaan "${deleting?.question?.[LOCALE] ?? ''}"?`}
                description="Pertanyaan hilang dari halaman FAQ publik. Datanya dipindahkan ke arsip, bukan dihapus permanen."
                confirmLabel="Hapus FAQ"
                loadingLabel="Menghapus..."
                onConfirm={() => {
                    if (!deleting) {
                        return;
                    }

                    setProcessing(true);
                    router.delete(destroyFaq(deleting.id).url, {
                        preserveScroll: true,
                        onFinish: () => {
                            setProcessing(false);
                            setDeleting(null);
                        },
                    });
                }}
            />
        </>
    );
}
