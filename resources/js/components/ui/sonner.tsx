import { Toaster as Sonner, type ToasterProps } from 'sonner';
import { useFlashToast } from '@/hooks/use-flash-toast';

/**
 * Latar bertint tipis dengan teks dan garis tepi berwarna penuh, mengikuti pola
 * badge status supaya tetap terbaca setelah tema diganti.
 */
const tint = (token: string, mix: number) =>
    `color-mix(in oklab, var(--color-${token}) ${mix}%, var(--color-card))`;

const edge = (token: string) =>
    `color-mix(in oklab, var(--color-${token}) 32%, transparent)`;

// Posisi dan durasi mengikuti docs/07-admin-ui-standards.md bagian 4.
function Toaster({ ...props }: ToasterProps) {
    useFlashToast();

    return (
        <Sonner
            theme="light"
            className="toaster group"
            position="top-right"
            duration={4000}
            // Tanpa `richColors`, Sonner memakai warna netral untuk semua jenis
            // dan toast sukses tidak bisa dibedakan dari galat.
            richColors
            style={
                {
                    '--normal-bg': 'var(--color-popover)',
                    '--normal-text': 'var(--color-popover-foreground)',
                    '--normal-border': 'var(--color-border)',

                    '--success-bg': tint('success', 12),
                    '--success-text': 'var(--color-success)',
                    '--success-border': edge('success'),

                    '--error-bg': tint('destructive', 12),
                    '--error-text': 'var(--color-destructive)',
                    '--error-border': edge('destructive'),

                    '--warning-bg': tint('warning', 14),
                    '--warning-text': 'var(--color-warning)',
                    '--warning-border': edge('warning'),

                    '--info-bg': tint('info', 12),
                    '--info-text': 'var(--color-info)',
                    '--info-border': edge('info'),
                } as React.CSSProperties
            }
            {...props}
        />
    );
}

export { Toaster };
