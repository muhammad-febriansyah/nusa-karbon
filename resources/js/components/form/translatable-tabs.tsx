import { Copy } from 'lucide-react';
import type { ReactNode } from 'react';
import type { TranslationStatus } from '@/components/shared/translation-indicator';
import TranslationIndicator from '@/components/shared/translation-indicator';
import { Button } from '@/components/ui/button';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { cn } from '@/lib/utils';

export const LOCALES = [
    { code: 'en', label: 'English', dir: 'ltr' as const },
    { code: 'ar', label: 'العربية', dir: 'rtl' as const },
    { code: 'ru', label: 'Русский', dir: 'ltr' as const },
];

type Props = {
    /** Kelengkapan terjemahan per locale, dipakai untuk titik indikator. */
    completeness: Record<string, TranslationStatus>;
    /** Locale yang punya error validasi, ditandai di label tabnya. */
    localesWithErrors?: string[];
    /** Isi tab. `dir` sudah disiapkan sesuai arah baca locale. */
    children: (locale: { code: string; dir: 'ltr' | 'rtl' }) => ReactNode;
    /** Menyalin isi Bahasa Inggris ke locale tujuan. */
    onCopyFromEnglish?: (target: string) => void;
};

/**
 * Berpindah tab tidak boleh menghilangkan isian tab lain — seluruh tab berada
 * dalam satu form state, jadi komponen ini hanya menyembunyikan tampilannya.
 *
 * @see docs/07-admin-ui-standards.md bagian 5.5
 */
export default function TranslatableTabs({
    completeness,
    localesWithErrors = [],
    children,
    onCopyFromEnglish,
}: Props) {
    return (
        <Tabs defaultValue="en">
            <TabsList>
                {LOCALES.map((locale) => (
                    <TabsTrigger
                        key={locale.code}
                        value={locale.code}
                        className={cn(
                            'gap-2',
                            localesWithErrors.includes(locale.code) &&
                                'text-destructive',
                        )}
                    >
                        {locale.label}
                        <TranslationIndicator
                            showLabels={false}
                            completeness={{
                                [locale.code]:
                                    completeness[locale.code] ?? 'empty',
                            }}
                        />
                        {localesWithErrors.includes(locale.code) && (
                            <span className="sr-only">
                                (ada error validasi)
                            </span>
                        )}
                    </TabsTrigger>
                ))}
            </TabsList>

            {LOCALES.map((locale) => (
                <TabsContent
                    key={locale.code}
                    value={locale.code}
                    dir={locale.dir}
                    className="space-y-4 pt-4"
                >
                    {locale.code !== 'en' && onCopyFromEnglish && (
                        <Button
                            type="button"
                            variant="duplicate"
                            size="sm"
                            onClick={() => onCopyFromEnglish(locale.code)}
                        >
                            <Copy />
                            Salin dari Bahasa Inggris
                        </Button>
                    )}

                    {children(locale)}
                </TabsContent>
            ))}
        </Tabs>
    );
}
