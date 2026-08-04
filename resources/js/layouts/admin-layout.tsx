import AdminSidebarLayout from '@/layouts/admin/admin-sidebar-layout';

export default function AdminLayout({
    children,
}: {
    children: React.ReactNode;
}) {
    return <AdminSidebarLayout>{children}</AdminSidebarLayout>;
}
