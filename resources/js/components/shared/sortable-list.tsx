import type { DragEndEvent } from '@dnd-kit/core';
import {
    DndContext,
    KeyboardSensor,
    PointerSensor,
    closestCenter,
    useSensor,
    useSensors,
} from '@dnd-kit/core';
import {
    SortableContext,
    arrayMove,
    sortableKeyboardCoordinates,
    useSortable,
    verticalListSortingStrategy,
} from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';
import { GripVertical } from 'lucide-react';
import type { ReactNode } from 'react';
import { cn } from '@/lib/utils';

type Props<TItem> = {
    items: TItem[];
    itemId: (item: TItem) => string | number;
    /** Dipanggil dengan urutan baru; kirim ke server dari sini. */
    onReorder: (items: TItem[]) => void;
    children: (item: TItem, index: number) => ReactNode;
    className?: string;
};

function SortableRow({
    id,
    children,
}: {
    id: string | number;
    children: ReactNode;
}) {
    const {
        attributes,
        listeners,
        setNodeRef,
        transform,
        transition,
        isDragging,
    } = useSortable({ id });

    return (
        <li
            ref={setNodeRef}
            style={{ transform: CSS.Transform.toString(transform), transition }}
            className={cn(
                'flex items-center gap-3 border-b border-border bg-card px-4 py-3 last:border-b-0',
                isDragging && 'relative z-10 shadow-dropdown',
            )}
        >
            <button
                type="button"
                {...attributes}
                {...listeners}
                className="cursor-grab text-muted-foreground active:cursor-grabbing"
            >
                <GripVertical aria-hidden className="size-4" />
                <span className="sr-only">Geser untuk mengurutkan</span>
            </button>

            <div className="min-w-0 flex-1">{children}</div>
        </li>
    );
}

/**
 * Daftar yang bisa diurut ulang lewat drag-and-drop maupun keyboard.
 *
 * @see docs/07-admin-ui-standards.md bagian 5.6
 */
export default function SortableList<TItem>({
    items,
    itemId,
    onReorder,
    children,
    className,
}: Props<TItem>) {
    const sensors = useSensors(
        useSensor(PointerSensor, {
            activationConstraint: { distance: 4 },
        }),
        useSensor(KeyboardSensor, {
            coordinateGetter: sortableKeyboardCoordinates,
        }),
    );

    const handleDragEnd = (event: DragEndEvent) => {
        const { active, over } = event;

        if (!over || active.id === over.id) {
            return;
        }

        const from = items.findIndex((item) => itemId(item) === active.id);
        const to = items.findIndex((item) => itemId(item) === over.id);

        if (from === -1 || to === -1) {
            return;
        }

        onReorder(arrayMove(items, from, to));
    };

    return (
        // `id` dipatok: tanpa itu dnd-kit membuat id `aria-describedby` yang
        // berbeda antara render server dan klien, dan React melaporkannya
        // sebagai hydration mismatch di tiap halaman berurut.
        <DndContext
            id="sortable-list"
            sensors={sensors}
            collisionDetection={closestCenter}
            onDragEnd={handleDragEnd}
        >
            <SortableContext
                items={items.map(itemId)}
                strategy={verticalListSortingStrategy}
            >
                <ul
                    className={cn(
                        'overflow-hidden rounded-[var(--radius)] border border-border',
                        className,
                    )}
                >
                    {items.map((item, index) => (
                        <SortableRow key={itemId(item)} id={itemId(item)}>
                            {children(item, index)}
                        </SortableRow>
                    ))}
                </ul>
            </SortableContext>
        </DndContext>
    );
}
