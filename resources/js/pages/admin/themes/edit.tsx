import { Link, useForm } from '@inertiajs/react';
import { Save, Wand2, X } from 'lucide-react';
import { useEffect, useMemo, useState } from 'react';
import PageHeader from '@/components/shared/page-header';
import Panel from '@/components/shared/panel';
import StatusBadge from '@/components/shared/status-badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Spinner } from '@/components/ui/spinner';
import {
    applyPreviewTokens,
    bestForeground,
    clearPreviewTokens,
    contrastRatio,
    isHex,
} from '@/lib/theme-preview';
import { dashboard } from '@/routes/admin';
import {
    create as themeCreate,
    edit as themeEdit,
    index as themesIndex,
    store as storeTheme,
    update as updateTheme,
} from '@/routes/admin/themes';
import type { SelectOption, Theme } from '@/types/models';
import ThemePreviewCard from './theme-preview-card';

/** Pasangan latar → teks beserta ambang minimumnya; cermin `Theme::CONTRAST_PAIRS`. */
const CONTRAST_PAIRS: [string, string, number, boolean][] = [
    ['background', 'foreground', 4.5, true],
    ['card', 'card_foreground', 4.5, true],
    ['popover', 'popover_foreground', 4.5, true],
    ['primary', 'primary_foreground', 4.5, true],
    ['secondary', 'secondary_foreground', 4.5, true],
    ['accent', 'accent_foreground', 4.5, true],
    ['success', 'success_foreground', 4.5, true],
    ['destructive', 'destructive_foreground', 4.5, true],
    ['warning', 'warning_foreground', 4.5, true],
    ['info', 'info_foreground', 4.5, true],
    ['neutral', 'neutral_foreground', 4.5, true],
    ['violet', 'violet_foreground', 4.5, true],
    ['teal', 'teal_foreground', 4.5, true],
    ['sidebar', 'sidebar_foreground', 4.5, true],
    ['sidebar_accent', 'sidebar_accent_foreground', 4.5, true],
    ['sidebar_primary', 'sidebar_primary_foreground', 4.5, true],
    ['muted', 'muted_foreground', 3.5, false],
    ['background', 'border', 1.4, false],
];

type TokenField = {
    key: string;
    label: string;
    hint: string | null;
};

type TokenGroup = {
    key: string;
    label: string;
    tokens: TokenField[];
};

type Props = {
    theme: Theme | null;
    defaultTokens: Record<string, string>;
    tokenGroups: TokenGroup[];
    scopes: SelectOption[];
};

type ThemeFormData = {
    name: string;
    scope: string;
    tokens: Record<string, string>;
};

export default function ThemeEdit({
    theme,
    defaultTokens,
    tokenGroups,
    scopes,
}: Props) {
    const isEdit = theme !== null;
    const { data, setData, post, put, processing, errors } =
        useForm<ThemeFormData>({
            name: theme?.name ?? '',
            scope: theme?.scope ?? 'both',
            tokens: { ...defaultTokens, ...(theme?.tokens ?? {}) },
        });

    const [previewTokens, setPreviewTokens] = useState(data.tokens);

    // Pratinjau di-debounce 150 ms supaya mengetik hex tidak memicu repaint
    // pada tiap karakter.
    useEffect(() => {
        const timer = window.setTimeout(
            () => setPreviewTokens(data.tokens),
            150,
        );

        return () => window.clearTimeout(timer);
    }, [data.tokens]);

    // Token diterapkan ke dokumen agar komponen nyata di halaman ikut berubah,
    // lalu dibersihkan saat editor ditinggalkan tanpa menyimpan.
    useEffect(() => {
        applyPreviewTokens(previewTokens);
    }, [previewTokens]);

    useEffect(() => {
        const keys = Object.keys(defaultTokens);

        return () => clearPreviewTokens(keys);
    }, [defaultTokens]);

    const contrast = useMemo(
        () =>
            CONTRAST_PAIRS.map(
                ([background, foreground, minimum, blocking]) => {
                    const backgroundValue =
                        data.tokens[background] ?? '#FFFFFF';
                    const foregroundValue =
                        data.tokens[foreground] ?? '#000000';
                    const valid =
                        isHex(backgroundValue) && isHex(foregroundValue);
                    const ratio = valid
                        ? contrastRatio(backgroundValue, foregroundValue)
                        : 0;

                    return {
                        background,
                        foreground,
                        minimum,
                        blocking,
                        ratio,
                        passes: valid && ratio + 0.005 >= minimum,
                    };
                },
            ),
        [data.tokens],
    );

    const failing = contrast.filter((row) => row.blocking && !row.passes);

    /**
     * Mengganti setiap `*_foreground` yang gagal dengan hitam atau putih —
     * menyelesaikan mayoritas kasus dalam satu klik.
     */
    const autoFix = () => {
        const next = { ...data.tokens };

        for (const row of failing) {
            const background = next[row.background];

            if (background && isHex(background)) {
                next[row.foreground] = bestForeground(background);
            }
        }

        setData('tokens', next);
    };

    const submit = (event: React.FormEvent) => {
        event.preventDefault();

        if (isEdit) {
            put(updateTheme(theme.id).url);

            return;
        }

        post(storeTheme().url);
    };

    const label = isEdit ? `Ubah ${theme.name}` : 'Tambah Tema';

    // Pesan preset terkunci dikirim controller sebagai error bernama `theme`,
    // di luar daftar field form.
    const themeError = (errors as Record<string, string | undefined>).theme;

    return (
        <>
            <PageHeader
                title={label}
                description="Ubah palet lalu lihat hasilnya seketika di kolom kanan. Palet yang tidak terbaca ditolak saat disimpan."
                breadcrumbs={[
                    { title: 'Dashboard', href: dashboard() },
                    { title: 'Tema & Warna', href: themesIndex() },
                    {
                        title: isEdit ? theme.name : 'Tambah Tema',
                        href: isEdit ? themeEdit(theme.id) : themeCreate(),
                    },
                ]}
            />

            {themeError && (
                <div className="rounded-[var(--radius)] border border-s-[3px] border-destructive/25 border-s-destructive bg-destructive/10 px-3.5 py-2.5 text-[13px] text-destructive">
                    {themeError}
                </div>
            )}

            <form onSubmit={submit} className="grid gap-5 lg:grid-cols-5">
                <div className="space-y-5 lg:col-span-3">
                    <Panel title="Informasi Tema">
                        <div className="grid gap-4 sm:grid-cols-2">
                            <div className="grid gap-1.5">
                                <Label htmlFor="name">
                                    Nama tema
                                    <span
                                        aria-hidden
                                        className="text-destructive"
                                    >
                                        *
                                    </span>
                                </Label>
                                <Input
                                    id="name"
                                    value={data.name}
                                    onChange={(event) =>
                                        setData('name', event.target.value)
                                    }
                                    placeholder="Contoh: Navy Corporate"
                                    aria-invalid={Boolean(errors.name)}
                                />
                                {errors.name && (
                                    <p className="text-xs text-destructive">
                                        {errors.name}
                                    </p>
                                )}
                            </div>

                            <div className="grid gap-1.5">
                                <Label htmlFor="scope">Dipakai di</Label>
                                <Select
                                    value={data.scope}
                                    onValueChange={(value) =>
                                        setData('scope', value)
                                    }
                                >
                                    <SelectTrigger id="scope">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {scopes.map((scope) => (
                                            <SelectItem
                                                key={scope.value}
                                                value={scope.value}
                                            >
                                                {scope.label}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                {errors.scope && (
                                    <p className="text-xs text-destructive">
                                        {errors.scope}
                                    </p>
                                )}
                            </div>
                        </div>
                    </Panel>

                    {tokenGroups.map((group) => (
                        <Panel key={group.key} title={group.label}>
                            <div className="grid gap-4 sm:grid-cols-2">
                                {group.tokens.map((token) => (
                                    <TokenInput
                                        key={token.key}
                                        token={token}
                                        value={data.tokens[token.key] ?? ''}
                                        error={
                                            errors[
                                                `tokens.${token.key}` as keyof typeof errors
                                            ]
                                        }
                                        onChange={(value) =>
                                            setData('tokens', {
                                                ...data.tokens,
                                                [token.key]: value,
                                            })
                                        }
                                    />
                                ))}
                            </div>
                        </Panel>
                    ))}
                </div>

                <div className="space-y-5 lg:col-span-2">
                    <div className="lg:sticky lg:top-4 lg:space-y-5">
                        <Panel
                            title="Pratinjau Langsung"
                            description="Komponen nyata, bukan gambar."
                        >
                            <ThemePreviewCard tokens={previewTokens} />
                        </Panel>

                        <Panel
                            title="Pemeriksaan Kontras"
                            description="Pasangan bertanda wajib minimal 4,5:1 agar teks terbaca."
                            action={
                                failing.length > 0 ? (
                                    <Button
                                        type="button"
                                        variant="filter"
                                        size="sm"
                                        onClick={autoFix}
                                    >
                                        <Wand2 />
                                        Perbaiki Otomatis
                                    </Button>
                                ) : undefined
                            }
                        >
                            <ul className="grid gap-2">
                                {contrast.map((row) => (
                                    <li
                                        key={`${row.background}-${row.foreground}`}
                                        className="flex items-center justify-between gap-2 text-[13px]"
                                    >
                                        <span className="truncate">
                                            {row.foreground.replaceAll(
                                                '_',
                                                ' ',
                                            )}{' '}
                                            /{' '}
                                            {row.background.replaceAll(
                                                '_',
                                                ' ',
                                            )}
                                        </span>
                                        <span className="flex items-center gap-2">
                                            <span className="tabular-nums">
                                                {row.ratio.toFixed(1)}:1
                                            </span>
                                            <StatusBadge
                                                label={
                                                    row.passes
                                                        ? 'Lolos'
                                                        : row.blocking
                                                          ? 'Ditolak'
                                                          : 'Peringatan'
                                                }
                                                tone={
                                                    row.passes
                                                        ? 'success'
                                                        : row.blocking
                                                          ? 'destructive'
                                                          : 'warning'
                                                }
                                            />
                                        </span>
                                    </li>
                                ))}
                            </ul>
                        </Panel>
                    </div>
                </div>

                <div className="sticky bottom-0 -mx-4 flex justify-end gap-2 border-t border-border bg-card/95 px-4 py-3.5 backdrop-blur md:-mx-6 md:px-6 lg:col-span-5">
                    <Button asChild variant="cancel" type="button">
                        <Link href={themesIndex()}>
                            <X />
                            Batal
                        </Link>
                    </Button>
                    <Button
                        type="submit"
                        variant="save"
                        disabled={processing}
                        data-test="save-theme-button"
                    >
                        {processing ? <Spinner /> : <Save />}
                        {processing
                            ? 'Menyimpan...'
                            : isEdit
                              ? 'Simpan Perubahan'
                              : 'Simpan Tema'}
                    </Button>
                </div>
            </form>
        </>
    );
}

function TokenInput({
    token,
    value,
    error,
    onChange,
}: {
    token: TokenField;
    value: string;
    error?: string;
    onChange: (value: string) => void;
}) {
    const isRadius = token.key === 'radius';

    return (
        <div className="grid gap-1.5">
            <Label htmlFor={`token-${token.key}`} className="capitalize">
                {token.label}
            </Label>

            <div className="flex items-center gap-2">
                {!isRadius && (
                    <input
                        type="color"
                        aria-label={`Pilih warna ${token.label}`}
                        value={isHex(value) ? value : '#000000'}
                        onChange={(event) => onChange(event.target.value)}
                        className="size-9 shrink-0 cursor-pointer rounded-[var(--radius)] border border-border bg-transparent p-1"
                    />
                )}
                <Input
                    id={`token-${token.key}`}
                    dir="ltr"
                    value={value}
                    onChange={(event) => onChange(event.target.value)}
                    placeholder={isRadius ? '0.5rem' : '#C94A17'}
                    aria-invalid={Boolean(error)}
                />
            </div>

            {token.hint && !error && (
                <p className="text-xs text-muted-foreground">{token.hint}</p>
            )}
            {error && <p className="text-xs text-destructive">{error}</p>}
        </div>
    );
}
