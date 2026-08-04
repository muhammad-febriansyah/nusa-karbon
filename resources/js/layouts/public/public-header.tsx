import { Link, usePage } from '@inertiajs/react';
import { MessageSquareQuote } from 'lucide-react';
import { useState } from 'react';
import BrandMark from '@/components/brand-mark';
import { publicNavGroups } from '@/components/public/public-nav-items';
import {
    isCurrentNavItem,
    MobileNav,
    MobileNavHeader,
    MobileNavMenu,
    MobileNavToggle,
    NavBody,
    NavItems,
    Navbar,
    NavbarSlot,
} from '@/components/public/resizable-navbar';
import { Button } from '@/components/ui/button';
import LanguageSwitcher from '@/layouts/public/language-switcher';
import { cn, toUrl } from '@/lib/utils';
import { contact, home } from '@/routes/public';

export default function PublicHeader({
    onDarkPlate = false,
}: {
    onDarkPlate?: boolean;
}) {
    const [menuOpen, setMenuOpen] = useState(false);
    const currentUrl = usePage().url;

    return (
        <Navbar onDarkPlate={onDarkPlate}>
            <NavBody>
                <NavbarSlot className="shrink-0">
                    <Link href={home()} aria-label="Beranda">
                        <BrandMark
                            imageClassName="h-8"
                            labelClassName="text-lg font-bold tracking-tight"
                        />
                    </Link>
                </NavbarSlot>

                <NavItems groups={publicNavGroups} />

                <NavbarSlot className="shrink-0 gap-2">
                    <LanguageSwitcher />
                    <Button asChild variant="create" size="sm">
                        <Link href={contact()}>
                            <MessageSquareQuote />
                            Minta Penawaran
                        </Link>
                    </Button>
                </NavbarSlot>
            </NavBody>

            <MobileNav expanded={menuOpen}>
                <MobileNavHeader>
                    <Link href={home()} aria-label="Beranda">
                        <BrandMark
                            imageClassName="h-7"
                            labelClassName="text-base font-bold tracking-tight"
                        />
                    </Link>

                    <div className="flex items-center gap-1">
                        <LanguageSwitcher />
                        <MobileNavToggle
                            isOpen={menuOpen}
                            onClick={() => setMenuOpen((open) => !open)}
                        />
                    </div>
                </MobileNavHeader>

                <MobileNavMenu isOpen={menuOpen}>
                    {/*
                     * Kelompoknya diberi judul, bukan garis seperti di layar
                     * lebar. Pada daftar menurun garis hanya terbaca sebagai
                     * jeda; judul menyebutkan isinya.
                     */}
                    {publicNavGroups.map((group) => (
                        <div key={group.name} className="mb-1 last:mb-0">
                            <p className="px-4 pt-3 pb-1 text-xs font-medium text-muted-foreground">
                                {group.name}
                            </p>

                            {group.items.map((item) => {
                                /*
                                 * Pencocokan yang sama dengan navigasi desktop:
                                 * berawalan, bukan persis. Membuka detail produk
                                 * harus tetap menyalakan "Produk" di sini juga.
                                 */
                                const isActive = isCurrentNavItem(
                                    item.href,
                                    currentUrl,
                                );

                                return (
                                    <Link
                                        key={toUrl(item.href)}
                                        href={item.href}
                                        onClick={() => setMenuOpen(false)}
                                        aria-current={
                                            isActive ? 'page' : undefined
                                        }
                                        className={cn(
                                            'block rounded-full px-4 py-2.5 text-sm transition-colors',
                                            isActive
                                                ? 'bg-muted font-semibold text-foreground'
                                                : 'font-medium text-muted-foreground hover:bg-muted hover:text-foreground',
                                        )}
                                    >
                                        {item.name}
                                    </Link>
                                );
                            })}
                        </div>
                    ))}

                    <Button asChild variant="create" className="mt-2 w-full">
                        <Link
                            href={contact()}
                            onClick={() => setMenuOpen(false)}
                        >
                            <MessageSquareQuote />
                            Minta Penawaran
                        </Link>
                    </Button>
                </MobileNavMenu>
            </MobileNav>
        </Navbar>
    );
}
