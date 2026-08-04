import { Link } from '@inertiajs/react';
import { adminNavGroups } from '@/components/admin-nav-items';
import AppLogo from '@/components/app-logo';
import { NavMain } from '@/components/nav-main';
import {
    Sidebar,
    SidebarContent,
    SidebarHeader,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes/admin';

export function AppSidebar() {
    return (
        <Sidebar collapsible="icon" className="border-e border-border">
            {/* Padding dikecilkan saat rail menyempit; dengan `px-5` logo hanya
                kebagian beberapa piksel dan menyusut jadi titik. */}
            <SidebarHeader className="h-15 justify-center border-b border-border px-5 group-data-[collapsible=icon]:px-1.5">
                <Link href={dashboard()} prefetch>
                    <AppLogo />
                </Link>
            </SidebarHeader>

            <SidebarContent className="scrollbar-slim gap-3.5 py-3.5">
                <NavMain groups={adminNavGroups} />
            </SidebarContent>
        </Sidebar>
    );
}
