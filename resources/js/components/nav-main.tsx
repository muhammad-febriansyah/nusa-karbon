import { Link, usePage } from '@inertiajs/react';
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuBadge,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/hooks/use-current-url';
import { cn } from '@/lib/utils';
import type { AdminNavGroup } from '@/types';

// Ikon sidebar 20px sesuai docs/05-design-system.md bagian 7.
const menuButtonClasses =
    'h-9 gap-2.5 rounded-[var(--radius)] px-3 text-[13.5px] font-normal text-foreground hover:bg-muted hover:text-foreground data-[active=true]:bg-sidebar-accent data-[active=true]:font-medium data-[active=true]:text-sidebar-accent-foreground [&>svg]:size-5';

export function NavMain({ groups = [] }: { groups: AdminNavGroup[] }) {
    const { isCurrentUrl } = useCurrentUrl();
    const { unread } = usePage().props;

    return (
        <>
            {groups.map((group, groupIndex) => (
                <SidebarGroup
                    key={group.label ?? `group-${groupIndex}`}
                    className="gap-1.5 px-3 py-0"
                >
                    {group.label && (
                        <SidebarGroupLabel className="h-auto px-3 pb-1.5 text-[11px] font-semibold tracking-[0.7px] text-muted-foreground uppercase">
                            {group.label}
                        </SidebarGroupLabel>
                    )}

                    <SidebarMenu className="gap-0.5">
                        {group.items.map((item) => {
                            const isActive = item.href
                                ? isCurrentUrl(item.href)
                                : false;
                            const badge = item.badgeKey
                                ? unread[item.badgeKey]
                                : 0;

                            return (
                                <SidebarMenuItem key={item.title}>
                                    <SidebarMenuButton
                                        asChild={Boolean(item.href)}
                                        isActive={isActive}
                                        aria-disabled={!item.href}
                                        tooltip={{
                                            children: item.href
                                                ? item.title
                                                : `${item.title} (segera hadir)`,
                                        }}
                                        className={cn(
                                            menuButtonClasses,
                                            !item.href &&
                                                'cursor-not-allowed opacity-55',
                                        )}
                                    >
                                        {item.href ? (
                                            <Link href={item.href} prefetch>
                                                <item.icon />
                                                <span>{item.title}</span>
                                            </Link>
                                        ) : (
                                            <>
                                                <item.icon />
                                                <span>{item.title}</span>
                                            </>
                                        )}
                                    </SidebarMenuButton>

                                    {isActive && (
                                        <span
                                            aria-hidden
                                            className="absolute start-0 top-1.5 bottom-1.5 w-[3px] rounded-e-[3px] bg-primary"
                                        />
                                    )}

                                    {badge > 0 && (
                                        // Warna teks dipatok di ketiga keadaan:
                                        // aturan bawaan mengubahnya jadi
                                        // `sidebar-accent-foreground` saat baris
                                        // aktif atau disorot, warna yang sama
                                        // persis dengan latar lencana — angkanya
                                        // jadi tak terlihat.
                                        <SidebarMenuBadge className="h-5 min-w-5 justify-center rounded-full bg-primary px-1.5 text-[11px] font-medium text-primary-foreground tabular-nums peer-hover/menu-button:text-primary-foreground peer-data-[active=true]/menu-button:text-primary-foreground">
                                            <span dir="ltr">{badge}</span>
                                        </SidebarMenuBadge>
                                    )}
                                </SidebarMenuItem>
                            );
                        })}
                    </SidebarMenu>
                </SidebarGroup>
            ))}
        </>
    );
}
