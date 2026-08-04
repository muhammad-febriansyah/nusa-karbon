import type { ReactNode } from 'react';
import { MotionProvider } from '@/components/public/motion-provider';
import PublicFooter from '@/layouts/public/public-footer';
import PublicHeader from '@/layouts/public/public-header';

/**
 * Kerangka seluruh halaman publik.
 *
 * Header memakai `position: sticky`, bukan `fixed`, sehingga tidak perlu ada
 * padding atas palsu di setiap halaman untuk mengimbangi tingginya.
 *
 * @see docs/09-tasks.md bagian 5.1
 */
export default function PublicLayout({
    children,
    heroOnDarkPlate = false,
}: {
    children: ReactNode;
    /**
     * Halaman membuka dengan pita gelap yang menembus ke belakang navbar.
     * Disetel halaman lewat `setLayoutProps`, bukan ditebak dari nama komponen,
     * supaya halaman lain bisa memakai pola yang sama tanpa menyentuh layout.
     */
    heroOnDarkPlate?: boolean;
}) {
    return (
        <MotionProvider>
            <div className="flex min-h-svh flex-col bg-background">
                <PublicHeader onDarkPlate={heroOnDarkPlate} />

                <main id="main" className="flex-1">
                    {children}
                </main>

                <PublicFooter />
            </div>
        </MotionProvider>
    );
}
