import { AppContent } from '@/components/app-content';
import { AppShell } from '@/components/app-shell';
import { AppSidebar } from '@/components/app-sidebar';
import { AppSidebarHeader } from '@/components/app-sidebar-header';
import type { AppLayoutProps } from '@/types';

/**
 * Breadcrumb, title, dan description dirender oleh `PageHeader` di tiap
 * halaman, bukan di layout. Referensi: docs/07-admin-ui-standards.md bagian 1.
 */
export default function AdminSidebarLayout({ children }: AppLayoutProps) {
    return (
        <AppShell variant="sidebar">
            <AppSidebar />
            <AppContent variant="sidebar" className="overflow-x-hidden">
                <AppSidebarHeader />

                <div className="flex flex-1 flex-col gap-5 p-4 md:p-6">
                    {children}
                </div>
            </AppContent>
        </AppShell>
    );
}
