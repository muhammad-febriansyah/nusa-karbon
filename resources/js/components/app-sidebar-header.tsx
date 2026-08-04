import { usePage } from '@inertiajs/react';
import { ChevronDown } from 'lucide-react';
import GlobalSearch from '@/components/global-search';
import NotificationMenu from '@/components/notification-menu';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { SidebarTrigger } from '@/components/ui/sidebar';
import { UserMenuContent } from '@/components/user-menu-content';
import { useInitials } from '@/hooks/use-initials';

export function AppSidebarHeader() {
    const { auth } = usePage().props;
    const getInitials = useInitials();

    return (
        <header className="sticky top-0 z-20 flex h-15 shrink-0 items-center justify-between border-b border-border bg-card px-6">
            <SidebarTrigger className="size-8 border border-border text-muted-foreground hover:text-foreground" />

            <div className="flex items-center gap-4">
                <GlobalSearch />

                <NotificationMenu />

                <span aria-hidden className="h-6.5 w-px bg-border" />

                {auth.user && (
                    <DropdownMenu>
                        <DropdownMenuTrigger
                            className="flex items-center gap-2.5 outline-none focus-visible:ring-2 focus-visible:ring-ring"
                            data-test="sidebar-menu-button"
                        >
                            <span className="flex size-8 items-center justify-center overflow-hidden rounded-full bg-linear-135 from-primary to-accent text-xs font-medium text-primary-foreground">
                                {auth.user.avatar ? (
                                    <img
                                        src={auth.user.avatar}
                                        alt=""
                                        className="size-full object-cover"
                                    />
                                ) : (
                                    getInitials(auth.user.name)
                                )}
                            </span>
                            <span className="hidden flex-col text-start leading-tight sm:flex">
                                <span className="text-[13px] font-medium">
                                    {auth.user.name}
                                </span>
                                <span
                                    dir="ltr"
                                    className="text-[11px] text-muted-foreground"
                                >
                                    {auth.user.email}
                                </span>
                            </span>
                            <ChevronDown
                                aria-hidden
                                className="size-4 text-muted-foreground"
                            />
                        </DropdownMenuTrigger>
                        <DropdownMenuContent
                            className="min-w-56 rounded-[var(--radius)]"
                            align="end"
                        >
                            <UserMenuContent user={auth.user} />
                        </DropdownMenuContent>
                    </DropdownMenu>
                )}
            </div>
        </header>
    );
}
