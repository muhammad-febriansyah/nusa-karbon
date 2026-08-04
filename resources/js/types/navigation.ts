import type { InertiaLinkProps } from '@inertiajs/react';
import type { LucideIcon } from 'lucide-react';

export type BreadcrumbItem = {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
};

export type NavItem = {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    icon?: LucideIcon | null;
    isActive?: boolean;
};

/**
 * A sidebar entry. `href` is null while the destination screen is not built
 * yet, which renders the entry as a disabled placeholder instead of a link.
 */
export type AdminNavItem = {
    title: string;
    href: NonNullable<InertiaLinkProps['href']> | null;
    icon: LucideIcon;
    /** Kunci penghitung belum dibaca pada shared prop `unread`. */
    badgeKey?: 'rfq' | 'contactMessages';
};

export type AdminNavGroup = {
    label?: string;
    items: AdminNavItem[];
};
