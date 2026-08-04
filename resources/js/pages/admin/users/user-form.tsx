import { Link, useForm } from '@inertiajs/react';
import { Info, Save, X } from 'lucide-react';
import AvatarUploader from '@/components/form/avatar-uploader';
import Panel from '@/components/shared/panel';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { Switch } from '@/components/ui/switch';
import {
    index as usersIndex,
    store as storeUser,
    update as updateUser,
} from '@/routes/admin/users';
import type { AdminUser } from '@/types/models';

type Props = {
    user: AdminUser | null;
};

type UserFormData = {
    /** Method spoofing: request multipart tidak bisa dikirim sebagai PUT. */
    _method?: 'put';
    name: string;
    email: string;
    phone: string;
    /** Hasil potongan 1:1 dari `AvatarUploader`; dikirim sebagai multipart. */
    avatar: File | null;
    remove_avatar: boolean;
    is_active: boolean;
};

export default function UserForm({ user }: Props) {
    const isEdit = user !== null;
    const { data, setData, post, processing, errors } = useForm<UserFormData>({
        ...(user === null ? {} : { _method: 'put' as const }),
        name: user?.name ?? '',
        email: user?.email ?? '',
        phone: user?.phone ?? '',
        avatar: null,
        remove_avatar: false,
        is_active: user?.is_active ?? true,
    });

    const submit = (event: React.FormEvent) => {
        event.preventDefault();

        // Berkas avatar membuat request jadi multipart; PHP tidak mengurai
        // body PUT multipart, jadi update dikirim POST dengan `_method=put`.
        if (isEdit) {
            post(updateUser(user.id).url, { forceFormData: true });

            return;
        }

        post(storeUser().url, { forceFormData: true });
    };

    const currentAvatar =
        data.remove_avatar || data.avatar !== null
            ? null
            : (user?.avatar_url ?? null);

    return (
        <form onSubmit={submit} className="space-y-5">
            <Panel
                title="Identitas"
                description="Nama dan email yang dipakai masuk ke panel admin."
            >
                <div className="grid gap-4 sm:grid-cols-2">
                    <div className="grid gap-1.5">
                        <Label htmlFor="name">
                            Nama lengkap
                            <span aria-hidden className="text-destructive">
                                *
                            </span>
                        </Label>
                        <Input
                            id="name"
                            value={data.name}
                            onChange={(event) =>
                                setData('name', event.target.value)
                            }
                            aria-invalid={Boolean(errors.name)}
                        />
                        {errors.name && (
                            <p className="text-xs text-destructive">
                                {errors.name}
                            </p>
                        )}
                    </div>

                    <div className="grid gap-1.5">
                        <Label htmlFor="email">
                            Email
                            <span aria-hidden className="text-destructive">
                                *
                            </span>
                        </Label>
                        <Input
                            id="email"
                            type="email"
                            dir="ltr"
                            value={data.email}
                            onChange={(event) =>
                                setData('email', event.target.value)
                            }
                            aria-invalid={Boolean(errors.email)}
                        />
                        {errors.email && (
                            <p className="text-xs text-destructive">
                                {errors.email}
                            </p>
                        )}
                    </div>

                    <div className="grid gap-1.5">
                        <Label htmlFor="phone">Telepon</Label>
                        <Input
                            id="phone"
                            dir="ltr"
                            value={data.phone}
                            onChange={(event) =>
                                setData('phone', event.target.value)
                            }
                            placeholder="+62 812 3456 7890"
                        />
                    </div>
                </div>

                {!isEdit && (
                    <div className="mt-4 flex items-start gap-2.5 rounded-[var(--radius)] border border-s-[3px] border-info/25 border-s-info bg-info/10 px-3.5 py-2.5">
                        <Info
                            aria-hidden
                            className="mt-0.5 size-4 shrink-0 text-info"
                        />
                        <p className="text-[13px] text-info">
                            Password tidak diisi di sini. Pengguna menerima
                            email undangan dan menentukan passwordnya sendiri.
                        </p>
                    </div>
                )}
            </Panel>

            <Panel
                title="Foto Profil"
                description="Dipotong 1:1 dan tampil di header, sidebar, serta catatan RFQ."
            >
                <div className="grid gap-2">
                    <AvatarUploader
                        fallback={data.name === '' ? 'NC' : data.name}
                        currentUrl={currentAvatar}
                        onChange={(file) => setData('avatar', file)}
                        onRemove={() => setData('remove_avatar', true)}
                    />
                </div>
            </Panel>

            <Panel title="Status">
                <div className="flex items-center justify-between rounded-[var(--radius)] border border-border p-3">
                    <div className="space-y-0.5">
                        <Label htmlFor="is_active">Aktif</Label>
                        <p className="text-xs text-muted-foreground">
                            Pengguna nonaktif ditolak saat mencoba masuk.
                        </p>
                    </div>
                    <Switch
                        id="is_active"
                        checked={data.is_active}
                        onCheckedChange={(checked) =>
                            setData('is_active', checked)
                        }
                    />
                </div>
            </Panel>

            <div className="sticky bottom-0 -mx-4 flex justify-end gap-2 border-t border-border bg-card/95 px-4 py-3.5 backdrop-blur md:-mx-6 md:px-6">
                <Button asChild variant="cancel" type="button">
                    <Link href={usersIndex()}>
                        <X />
                        Batal
                    </Link>
                </Button>
                <Button
                    type="submit"
                    variant="save"
                    disabled={processing}
                    data-test="save-user-button"
                >
                    {processing ? <Spinner /> : <Save />}
                    {processing
                        ? 'Menyimpan...'
                        : isEdit
                          ? 'Simpan Perubahan'
                          : 'Simpan & Kirim Undangan'}
                </Button>
            </div>
        </form>
    );
}
