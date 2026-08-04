import { Link, usePage } from '@inertiajs/react';
import type { InertiaLinkProps } from '@inertiajs/react';
import { Menu, X } from 'lucide-react';
import {
    AnimatePresence,
    m,
    useMotionValueEvent,
    useScroll,
} from 'motion/react';
import { createContext, useContext, useState } from 'react';
import type { ReactNode } from 'react';
import type { NavGroup } from '@/components/public/public-nav-items';
import { Button } from '@/components/ui/button';
import { cn, toUrl } from '@/lib/utils';

/*
 * Port dari `resizable-navbar` Aceternity UI. Bukan salinan mentah — versi
 * aslinya melanggar empat aturan proyek ini sekaligus:
 *
 *   1. Warna hardcoded (`bg-white`, `text-neutral-600`, `bg-gray-100`) —
 *      di sini seluruhnya token tema agar ikut berubah saat admin ganti tema.
 *   2. Kelas `dark:` — proyek ini tidak punya dark mode; tema publik memang
 *      gelap, tapi lewat palet tetap, bukan varian kelas.
 *   3. Kelas arah fisik (`mr-4`) — diganti logical property demi RTL Arab.
 *   4. `@tabler/icons-react` — proyek memakai `lucide-react`.
 *
 * Perbedaan perilaku yang disengaja: aslinya menyusut ke `width: 40%` dengan
 * `minWidth: 800px`, yang membuat navbar terasa sempit dan menu ekspor
 * berdesakan. Di sini yang dianimasikan `maxWidth` (80rem ke 68rem) sehingga
 * penyusutannya terlihat tapi tetap lapang, dan karena batas atas — bukan
 * batas bawah — navbar tidak pernah meluber di layar sempit.
 *
 * Hanya `maxWidth` yang dianimasikan `motion`. Latar, blur, border, dan bayangan
 * dipindah ke transisi CSS: `box-shadow` dan `backdrop-filter` tidak bisa
 * diinterpolasi dari nilai `var()`, dan menyerahkannya ke CSS menghemat kerja
 * di setiap frame scroll.
 */

/** Jarak scroll sebelum navbar mengambang. */
const SCROLL_THRESHOLD = 80;

const SURFACE_TRANSITION =
    'transition-[background-color,border-color,box-shadow,backdrop-filter] duration-300 ease-out';

const SURFACE_FLOATING =
    'border-border/70 bg-card/75 shadow-navbar backdrop-blur-xl';

const SURFACE_FLAT =
    'border-transparent bg-transparent shadow-none backdrop-blur-none';

const SPRING = { type: 'spring', stiffness: 200, damping: 40 } as const;

const NavbarScrollContext = createContext(false);

/** True begitu halaman digulir melewati ambang, dipakai seluruh sub-komponen. */
export function useNavbarFloating(): boolean {
    return useContext(NavbarScrollContext);
}

/**
 * Kelas tinta terbalik untuk navbar yang sedang mengambang di atas pita gelap.
 *
 * Hanya berlaku selama navbar belum menyusut. Begitu menyusut, permukaannya
 * berubah jadi kaca `bg-card/75` yang terbaca di atas pita gelap maupun kertas
 * terang, jadi warna teks bawaan halaman sudah benar dan pembalikan ini justru
 * akan salah.
 */
function inkClass(onDarkPlate: boolean, floating: boolean): string | false {
    return onDarkPlate && !floating && 'surface-plate-ink';
}

export interface NavbarItem {
    name: string;
    href: NonNullable<InertiaLinkProps['href']>;
}

/**
 * Apakah tautan menunjuk halaman yang sedang dibuka.
 *
 * Cocok persis untuk beranda, cocok berawalan untuk sisanya — membuka
 * `/products/briket-shisha-cube-25-mm` harus tetap menyalakan "Produk". Tanpa
 * pencocokan awalan, penanda posisi padam justru saat pengunjung berada paling
 * dalam di sebuah bagian.
 *
 * Query string dibuang lebih dulu: `/products?kategori=arang-shisha` adalah
 * halaman yang sama dengan `/products`.
 */
export function isCurrentNavItem(
    href: NavbarItem['href'],
    url: string,
): boolean {
    const target = toUrl(href);
    const current = url.split('?')[0] ?? '/';

    if (target === '/') {
        return current === '/';
    }

    return current === target || current.startsWith(`${target}/`);
}

export function Navbar({
    children,
    className,
    onDarkPlate = false,
}: {
    children: ReactNode;
    className?: string;
    /**
     * Halaman ini dibuka dengan pita gelap yang menembus sampai ke belakang
     * navbar. Tanpa penanda ini navbar tetap memakai warna teks halaman terang
     * dan menghilang di atas pitanya.
     */
    onDarkPlate?: boolean;
}) {
    const { scrollY } = useScroll();
    const [floating, setFloating] = useState(false);

    useMotionValueEvent(scrollY, 'change', (latest) => {
        setFloating(latest > SCROLL_THRESHOLD);
    });

    return (
        <NavbarScrollContext.Provider value={floating}>
            <header
                className={cn(
                    'sticky inset-x-0 top-0 z-50 w-full px-4 py-3',
                    'transition-colors duration-300 ease-out',
                    inkClass(onDarkPlate, floating),
                    className,
                )}
            >
                {children}
            </header>
        </NavbarScrollContext.Provider>
    );
}

/** Baris navbar desktop. Tersembunyi di bawah 1024px. */
export function NavBody({
    children,
    className,
}: {
    children: ReactNode;
    className?: string;
}) {
    const floating = useNavbarFloating();

    return (
        <m.div
            animate={{ maxWidth: floating ? '68rem' : '80rem' }}
            transition={SPRING}
            className={cn(
                'relative mx-auto hidden w-full flex-row items-center',
                'justify-between rounded-full border px-3 py-2 lg:flex',
                SURFACE_TRANSITION,
                floating ? SURFACE_FLOATING : SURFACE_FLAT,
                className,
            )}
        >
            {children}
        </m.div>
    );
}

/**
 * Tautan tengah navbar dengan pil hover yang meluncur antar item.
 *
 * Pil memakai `layoutId`, jadi `LazyMotion` di halaman publik wajib memuat
 * `domMax` — `domAnimation` tidak menyertakan animasi layout.
 */
export function NavItems({
    groups,
    className,
    onItemClick,
}: {
    groups: NavGroup[];
    className?: string;
    onItemClick?: () => void;
}) {
    const [hovered, setHovered] = useState<string | null>(null);
    const url = usePage().url;

    /*
     * Berada di alur normal, bukan `absolute inset-0`. Terpusat pada pil
     * membuat barisnya condong ke kanan: logo di kiri selebar ±200px, sementara
     * pemilih bahasa dan tombol penawaran di kanan ±300px. Dengan `flex-1` ia
     * terpusat di antara kedua kluster itu — yang memang jaraknya dilihat mata.
     */
    return (
        <nav
            onMouseLeave={() => setHovered(null)}
            className={cn(
                'hidden min-w-0 flex-1 flex-row items-center',
                'justify-center gap-1 text-sm font-medium lg:flex',
                className,
            )}
        >
            {groups.flatMap((group, groupIndex) => {
                const separator =
                    groupIndex === 0
                        ? []
                        : [
                              <span
                                  key={`pemisah-${group.name}`}
                                  aria-hidden
                                  className="mx-2 h-5 w-px shrink-0 bg-border"
                              />,
                          ];

                return [
                    ...separator,
                    ...group.items.map((item) => {
                        const key = toUrl(item.href);
                        const current = isCurrentNavItem(item.href, url);

                        return (
                            <Link
                                key={key}
                                href={item.href}
                                onMouseEnter={() => setHovered(key)}
                                onClick={onItemClick}
                                aria-current={current ? 'page' : undefined}
                                className={cn(
                                    'relative rounded-full px-4 py-2 whitespace-nowrap transition-colors',
                                    'focus-visible:ring-2 focus-visible:ring-ring',
                                    'focus-visible:outline-none',
                                    current
                                        ? 'font-semibold text-foreground'
                                        : 'text-muted-foreground hover:text-foreground',
                                )}
                            >
                                {/*
                                 * Halaman aktif memakai pil tetap, dan pil
                                 * hover disembunyikan di atasnya. Keduanya
                                 * berbentuk dan berwarna sama, jadi saat
                                 * tetikus masuk ke item aktif pil yang meluncur
                                 * lenyap tepat di posisi pil tetap — yang
                                 * terlihat pilnya berhenti, bukan berkedip.
                                 */}
                                {current && (
                                    <span
                                        aria-hidden
                                        className="absolute inset-0 rounded-full bg-muted"
                                    />
                                )}

                                {hovered === key && !current && (
                                    <m.span
                                        layoutId="public-nav-hover"
                                        aria-hidden
                                        className="absolute inset-0 rounded-full bg-muted"
                                        transition={{
                                            type: 'spring',
                                            stiffness: 380,
                                            damping: 32,
                                        }}
                                    />
                                )}
                                <span className="relative z-10">
                                    {item.name}
                                </span>
                            </Link>
                        );
                    }),
                ];
            })}
        </nav>
    );
}

/**
 * Pembungkus logo dan area aksi.
 *
 * `z-20` menjaga logo dan tombol tetap berada di atas pil hover yang meluncur
 * di baris tautan tengah.
 */
export function NavbarSlot({
    children,
    className,
}: {
    children: ReactNode;
    className?: string;
}) {
    return (
        <div className={cn('relative z-20 flex items-center', className)}>
            {children}
        </div>
    );
}

/**
 * Baris navbar mobile. Tersembunyi mulai 1024px.
 *
 * `expanded` memaksa permukaan kaca meski halaman belum digulir: menu yang
 * terbuka di atas latar transparan menyatu dengan isi halaman di belakangnya
 * dan batasnya tidak terbaca.
 */
export function MobileNav({
    children,
    expanded = false,
    className,
}: {
    children: ReactNode;
    expanded?: boolean;
    className?: string;
}) {
    const floating = useNavbarFloating() || expanded;

    return (
        <m.div
            animate={{ maxWidth: floating ? '44rem' : '80rem' }}
            transition={SPRING}
            className={cn(
                'relative mx-auto flex w-full flex-col rounded-2xl border',
                'px-3 py-2 lg:hidden',
                SURFACE_TRANSITION,
                floating ? SURFACE_FLOATING : SURFACE_FLAT,
                className,
            )}
        >
            {children}
        </m.div>
    );
}

export function MobileNavHeader({
    children,
    className,
}: {
    children: ReactNode;
    className?: string;
}) {
    return (
        <div
            className={cn(
                'flex w-full flex-row items-center justify-between',
                className,
            )}
        >
            {children}
        </div>
    );
}

export function MobileNavMenu({
    children,
    className,
    isOpen,
}: {
    children: ReactNode;
    className?: string;
    isOpen: boolean;
}) {
    return (
        <AnimatePresence initial={false}>
            {isOpen && (
                <m.div
                    initial={{ opacity: 0, height: 0 }}
                    animate={{ opacity: 1, height: 'auto' }}
                    exit={{ opacity: 0, height: 0 }}
                    transition={{ duration: 0.22, ease: 'easeOut' }}
                    className="w-full overflow-hidden"
                >
                    <div
                        className={cn(
                            'mt-3 flex w-full flex-col items-stretch gap-1',
                            'border-t border-border/70 pt-3',
                            className,
                        )}
                    >
                        {children}
                    </div>
                </m.div>
            )}
        </AnimatePresence>
    );
}

/**
 * Tombol buka/tutup menu mobile.
 *
 * Memakai varian `control` — satu-satunya varian tanpa latar penuh, memang
 * disediakan untuk kontrol di dalam komponen, bukan untuk tombol aksi. Teksnya
 * ada di `aria-label` karena ikon hamburger sudah dikenali universal dan
 * menaruh label terlihat di sini akan memakan lebar navbar mobile.
 */
export function MobileNavToggle({
    isOpen,
    onClick,
}: {
    isOpen: boolean;
    onClick: () => void;
}) {
    return (
        <Button
            type="button"
            variant="control"
            size="icon"
            onClick={onClick}
            aria-expanded={isOpen}
            aria-label={isOpen ? 'Tutup menu navigasi' : 'Buka menu navigasi'}
        >
            {isOpen ? <X /> : <Menu />}
        </Button>
    );
}
