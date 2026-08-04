import { router, usePage } from '@inertiajs/react';
import { Check, ChevronDown } from 'lucide-react';
import LocaleFlag from '@/components/public/locale-flag';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { localizedPath } from '@/lib/locale-url';

/**
 * Pemilih bahasa website publik.
 *
 * Dua daftar, dua perlakuan. `supported` bisa dipilih dan mengganti prefix URL;
 * `planned` hanya tampil sebagai pilihan mati karena rutenya belum ada.
 *
 * Keduanya dibaca dari `config/locales.php`, jadi berkas ini tidak perlu
 * disentuh saat Fase 6 memindahkan satu entri dari `planned` ke `supported` —
 * bahasa itu akan berpindah sendiri dari bagian mati ke bagian yang bisa
 * dipilih.
 */
export default function LanguageSwitcher() {
    const page = usePage();
    const { current, supported, planned } = page.props.locale;

    if (supported.length === 0) {
        return null;
    }

    const active = supported.find((locale) => locale.code === current);

    return (
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <Button
                    variant="control"
                    size="sm"
                    aria-label="Ganti bahasa"
                    className="gap-1.5 px-2"
                >
                    <LocaleFlag code={active?.flag ?? current} />
                    <span className="font-mono text-[11px] uppercase" dir="ltr">
                        {active?.code ?? current}
                    </span>
                    <ChevronDown className="size-3.5 opacity-60" />
                </Button>
            </DropdownMenuTrigger>

            <DropdownMenuContent align="end" className="min-w-52">
                <DropdownMenuLabel className="font-mono text-[10px] tracking-[0.14em] text-muted-foreground uppercase">
                    Bahasa
                </DropdownMenuLabel>

                {supported.map((locale) => (
                    <DropdownMenuItem
                        key={locale.code}
                        className="gap-2.5"
                        onSelect={() => {
                            router.visit(
                                localizedPath(page.url, locale, supported),
                            );
                        }}
                    >
                        <LocaleFlag code={locale.flag} />
                        {/*
                         * Arah kanan-ke-kiri dipasang pada teksnya saja, bukan
                         * pada barisnya. Membalik seluruh baris memindahkan
                         * bendera ke sisi seberang, dan satu baris yang
                         * susunannya berbeda sendiri membuat daftarnya terbaca
                         * rusak. Nama dalam aksara Arab tetap tampil benar
                         * karena arahnya melekat pada hurufnya.
                         */}
                        <span className="flex-1" dir={locale.dir}>
                            {locale.native}
                        </span>
                        {locale.code === current && (
                            <Check className="size-4 text-primary" />
                        )}
                    </DropdownMenuItem>
                ))}

                {/*
                 * Bahasa Fase 6 tampil, tetapi dinonaktifkan.
                 *
                 * Rute berprefix locale belum ada, jadi memilih salah satunya
                 * akan berakhir di 404. Menampilkannya sebagai pilihan mati
                 * memberi tahu pengunjung bahwa situs ini menuju multibahasa
                 * tanpa menjanjikan sesuatu yang belum bisa dilakukan — dan
                 * `disabled` juga menutupnya dari pemakai papan ketik dan
                 * pembaca layar, bukan sekadar meredupkan warnanya.
                 */}
                {planned.length > 0 && (
                    <>
                        <DropdownMenuSeparator />

                        {planned.map((locale) => (
                            <DropdownMenuItem
                                key={locale.code}
                                disabled
                                className="gap-2.5"
                            >
                                <LocaleFlag code={locale.flag} />
                                <span className="flex-1" dir={locale.dir}>
                                    {locale.native}
                                </span>
                                <span className="font-mono text-[10px] tracking-wide uppercase">
                                    Segera
                                </span>
                            </DropdownMenuItem>
                        ))}
                    </>
                )}
            </DropdownMenuContent>
        </DropdownMenu>
    );
}
