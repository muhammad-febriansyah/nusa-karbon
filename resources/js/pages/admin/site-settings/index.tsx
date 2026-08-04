import { useForm } from '@inertiajs/react';
import { Lock, Save } from 'lucide-react';
import { useState } from 'react';
import PageHeader from '@/components/shared/page-header';
import Panel from '@/components/shared/panel';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { Switch } from '@/components/ui/switch';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Textarea } from '@/components/ui/textarea';
import ImageUploader from '@/components/upload/image-uploader';
import { dashboard } from '@/routes/admin';
import {
    index as settingsIndex,
    update as updateSettings,
} from '@/routes/admin/site-settings';
import type { Translated } from '@/types/models';

const LOCALE = 'id';

type SettingValue = string | number | boolean | Translated | null;

type Setting = {
    key: string;
    label: string;
    description: string | null;
    type: string;
    is_secret: boolean;
    value: SettingValue;
    has_value: boolean;
    /** URL berkas tersimpan untuk tipe `image` dan `file`. */
    file_url: string | null;
};

type Group = {
    key: string;
    label: string;
    settings: Setting[];
};

type Props = {
    groups: Group[];
};

type FormData = {
    values: Record<string, SettingValue>;
};

export default function SiteSettingIndex({ groups }: Props) {
    const [tab, setTab] = useState(groups[0]?.key ?? 'identity');

    const { data, setData, put, processing, errors } = useForm<FormData>({
        values: Object.fromEntries(
            groups.flatMap((group) =>
                group.settings.map((setting) => [
                    setting.key,
                    // Kunci rahasia dikirim kosong; server membiarkan nilai
                    // lama bila field tidak diisi.
                    setting.is_secret ? '' : setting.value,
                ]),
            ),
        ),
    });

    // Pembaruan fungsional: dua uploader bisa melapor pada commit yang sama,
    // dan bentuk objek biasa membuat laporan kedua menimpa yang pertama.
    const setValue = (key: string, value: SettingValue) =>
        setData((current) => ({
            ...current,
            values: { ...current.values, [key]: value },
        }));

    const errorFor = (key: string) =>
        errors[`values.${key}` as keyof typeof errors];

    return (
        <>
            <PageHeader
                title="Pengaturan Situs"
                description="Identitas perusahaan, kontak, SEO, dan konfigurasi sistem."
                breadcrumbs={[
                    { title: 'Dashboard', href: dashboard() },
                    { title: 'Pengaturan Situs', href: settingsIndex() },
                ]}
            />

            <form
                onSubmit={(event) => {
                    event.preventDefault();
                    put(updateSettings().url, { preserveScroll: true });
                }}
                className="space-y-5"
            >
                <Tabs value={tab} onValueChange={setTab}>
                    <TabsList>
                        {groups.map((group) => (
                            <TabsTrigger key={group.key} value={group.key}>
                                {group.label}
                            </TabsTrigger>
                        ))}
                    </TabsList>

                    {groups.map((group) => (
                        <TabsContent key={group.key} value={group.key}>
                            <Panel title={group.label}>
                                <div className="grid gap-5 sm:grid-cols-2">
                                    {group.settings.map((setting) => (
                                        <SettingField
                                            key={setting.key}
                                            setting={setting}
                                            value={data.values[setting.key]}
                                            error={errorFor(setting.key)}
                                            onChange={(value) =>
                                                setValue(setting.key, value)
                                            }
                                        />
                                    ))}
                                </div>
                            </Panel>
                        </TabsContent>
                    ))}
                </Tabs>

                <div className="sticky bottom-0 -mx-4 flex justify-end gap-2 border-t border-border bg-card/95 px-4 py-3.5 backdrop-blur md:-mx-6 md:px-6">
                    <Button
                        type="submit"
                        variant="save"
                        disabled={processing}
                        data-test="save-settings-button"
                    >
                        {processing ? <Spinner /> : <Save />}
                        {processing ? 'Menyimpan...' : 'Simpan Pengaturan'}
                    </Button>
                </div>
            </form>
        </>
    );
}

function SettingField({
    setting,
    value,
    error,
    onChange,
}: {
    setting: Setting;
    value: SettingValue;
    error?: string;
    onChange: (value: SettingValue) => void;
}) {
    const wide =
        setting.type === 'text' ||
        setting.type === 'translatable' ||
        setting.type === 'json';

    const asString = typeof value === 'string' ? value : '';
    const asNumber =
        typeof value === 'number' ? String(value) : (asString ?? '');
    const asTranslated =
        value !== null && typeof value === 'object'
            ? ((value as Translated)[LOCALE] ?? '')
            : '';

    return (
        <div className={wide ? 'grid gap-1.5 sm:col-span-2' : 'grid gap-1.5'}>
            <Label htmlFor={setting.key} className="flex items-center gap-1.5">
                {setting.label}
                {setting.is_secret && (
                    <Lock
                        aria-hidden
                        className="size-3.5 text-muted-foreground"
                    />
                )}
            </Label>

            {setting.type === 'image' || setting.type === 'file' ? (
                <div className="grid gap-2">
                    {setting.file_url && (
                        <img
                            src={setting.file_url}
                            alt={`${setting.label} saat ini`}
                            className="h-16 w-fit rounded-[var(--radius)] border border-border object-contain p-1.5"
                        />
                    )}
                    <ImageUploader
                        kind="logo"
                        onChange={(token) => onChange(token)}
                        hint="Unggah untuk mengganti berkas yang tersimpan."
                    />
                </div>
            ) : setting.type === 'boolean' ? (
                <div className="flex items-center justify-between rounded-[var(--radius)] border border-border p-3">
                    <span className="text-xs text-muted-foreground">
                        {setting.description ?? 'Aktif atau nonaktif.'}
                    </span>
                    <Switch
                        id={setting.key}
                        checked={value === true}
                        onCheckedChange={(checked) => onChange(checked)}
                    />
                </div>
            ) : setting.type === 'translatable' ? (
                <Textarea
                    id={setting.key}
                    rows={2}
                    value={asTranslated}
                    onChange={(event) =>
                        onChange({ [LOCALE]: event.target.value })
                    }
                    aria-invalid={Boolean(error)}
                />
            ) : setting.type === 'text' ? (
                <Textarea
                    id={setting.key}
                    rows={3}
                    value={asString}
                    onChange={(event) => onChange(event.target.value)}
                    aria-invalid={Boolean(error)}
                />
            ) : setting.type === 'number' || setting.type === 'coordinate' ? (
                <Input
                    id={setting.key}
                    type="number"
                    step={setting.type === 'coordinate' ? 'any' : '1'}
                    dir="ltr"
                    value={asNumber}
                    onChange={(event) =>
                        onChange(
                            event.target.value === ''
                                ? null
                                : Number(event.target.value),
                        )
                    }
                    aria-invalid={Boolean(error)}
                />
            ) : setting.type === 'color' ? (
                <Input
                    id={setting.key}
                    type="color"
                    value={asString === '' ? '#000000' : asString}
                    onChange={(event) => onChange(event.target.value)}
                    className="h-10 w-20 p-1"
                />
            ) : (
                <Input
                    id={setting.key}
                    dir="ltr"
                    type={setting.is_secret ? 'password' : 'text'}
                    value={asString}
                    onChange={(event) => onChange(event.target.value)}
                    placeholder={
                        setting.is_secret && setting.has_value
                            ? 'Tersimpan — isi untuk mengganti'
                            : undefined
                    }
                    aria-invalid={Boolean(error)}
                />
            )}

            {setting.description && setting.type !== 'boolean' && !error && (
                <p className="text-xs text-muted-foreground">
                    {setting.description}
                </p>
            )}

            {error && <p className="text-xs text-destructive">{error}</p>}
        </div>
    );
}
