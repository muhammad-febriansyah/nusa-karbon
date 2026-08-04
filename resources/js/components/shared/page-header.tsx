import { Head, Link } from '@inertiajs/react';
import { ChevronRight } from 'lucide-react';
import type { ReactNode } from 'react';
import type { BreadcrumbItem } from '@/types';

type Props = {
    title: string;
    description: string;
    breadcrumbs: BreadcrumbItem[];
    actions?: ReactNode;
};

/**
 * Anatomi wajib tiap halaman admin: breadcrumb, title, description.
 * Referensi: docs/07-admin-ui-standards.md bagian 1.
 */
export default function PageHeader({
    title,
    description,
    breadcrumbs,
    actions,
}: Props) {
    return (
        <>
            <Head title={title} />

            <nav aria-label="Breadcrumb">
                <ol className="flex flex-wrap items-center gap-1.5 text-[13px] text-muted-foreground">
                    {breadcrumbs.map((crumb, index) => {
                        const isLast = index === breadcrumbs.length - 1;

                        return (
                            <li
                                key={`${crumb.title}-${index}`}
                                className="flex items-center gap-1.5"
                            >
                                {isLast ? (
                                    <span className="font-medium text-foreground">
                                        {crumb.title}
                                    </span>
                                ) : (
                                    <Link
                                        href={crumb.href}
                                        className="transition-colors hover:text-foreground"
                                    >
                                        {crumb.title}
                                    </Link>
                                )}
                                {!isLast && (
                                    <ChevronRight
                                        aria-hidden
                                        className="size-3.5 shrink-0 rtl:rotate-180"
                                    />
                                )}
                            </li>
                        );
                    })}
                </ol>
            </nav>

            <div className="flex flex-wrap items-start justify-between gap-4">
                <div className="space-y-1">
                    <h1 className="text-2xl font-semibold tracking-tight">
                        {title}
                    </h1>
                    <p className="text-sm text-muted-foreground">
                        {description}
                    </p>
                </div>
                {actions && (
                    <div className="flex items-center gap-2">{actions}</div>
                )}
            </div>
        </>
    );
}
