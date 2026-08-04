import { Link, router, useForm, usePage } from '@inertiajs/react';
import { Save } from 'lucide-react';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import AvatarUploader from '@/components/form/avatar-uploader';
import InputError from '@/components/input-error';
import PageHeader from '@/components/shared/page-header';
import Panel from '@/components/shared/panel';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { dashboard } from '@/routes/admin';
import { edit } from '@/routes/admin/profile';
import { destroy as destroyAvatar } from '@/routes/admin/profile/avatar';
import { send } from '@/routes/verification';
import type { Auth } from '@/types';

type PageProps = {
    auth: Auth;
};

type ProfileForm = {
    name: string;
    email: string;
    phone: string;
    avatar: File | null;
};

export default function Profile({
    mustVerifyEmail,
    status,
}: {
    mustVerifyEmail: boolean;
    status?: string;
}) {
    const { auth } = usePage<PageProps>().props;

    // Form manual, bukan komponen <Form>, karena unggahan file perlu dikirim
    // sebagai multipart dan Laravel butuh method spoofing untuk PATCH.
    const { data, setData, post, processing, errors } = useForm<ProfileForm>({
        name: auth.user.name,
        email: auth.user.email,
        phone: (auth.user.phone as string | null) ?? '',
        avatar: null,
    });

    const submit = (event: React.FormEvent) => {
        event.preventDefault();

        post(ProfileController.update.url(), {
            forceFormData: true,
            preserveScroll: true,
            headers: { 'X-HTTP-Method-Override': 'PATCH' },
        });
    };

    return (
        <>
            <PageHeader
                title="Profil"
                description="Perbarui nama, email, nomor telepon, dan foto profil akun Anda."
                breadcrumbs={[
                    { title: 'Dashboard', href: dashboard() },
                    { title: 'Pengaturan', href: edit() },
                    { title: 'Profil', href: edit() },
                ]}
            />

            <Panel title="Foto Profil">
                <AvatarUploader
                    currentUrl={auth.user.avatar}
                    fallback={auth.user.name}
                    disabled={processing}
                    onChange={(file) => setData('avatar', file)}
                    onRemove={() =>
                        router.delete(destroyAvatar().url, {
                            preserveScroll: true,
                        })
                    }
                />
                <InputError message={errors.avatar} className="mt-2" />
            </Panel>

            <Panel title="Informasi Akun">
                <form onSubmit={submit} className="space-y-5">
                    {/* Dua kolom seperti form modul lain; field pendek yang
                        melebar penuh layar sulit dipindai. */}
                    <div className="grid gap-5 sm:grid-cols-2">
                        <div className="grid gap-1.5">
                            <Label htmlFor="name">Nama lengkap</Label>
                            <Input
                                id="name"
                                name="name"
                                value={data.name}
                                onChange={(event) =>
                                    setData('name', event.target.value)
                                }
                                required
                                autoComplete="name"
                                placeholder="Nama lengkap"
                                aria-invalid={Boolean(errors.name)}
                            />
                            <InputError message={errors.name} />
                        </div>

                        <div className="grid gap-1.5">
                            <Label htmlFor="email">Email</Label>
                            <Input
                                id="email"
                                type="email"
                                name="email"
                                value={data.email}
                                onChange={(event) =>
                                    setData('email', event.target.value)
                                }
                                required
                                autoComplete="username"
                                placeholder="nama@nusacarbon.com"
                                aria-invalid={Boolean(errors.email)}
                            />
                            <InputError message={errors.email} />
                        </div>

                        <div className="grid gap-1.5">
                            <Label htmlFor="phone">Nomor telepon</Label>
                            <Input
                                id="phone"
                                name="phone"
                                dir="ltr"
                                value={data.phone}
                                onChange={(event) =>
                                    setData('phone', event.target.value)
                                }
                                autoComplete="tel"
                                placeholder="+62 812 3456 7890"
                                aria-invalid={Boolean(errors.phone)}
                            />
                            <InputError message={errors.phone} />
                        </div>
                    </div>

                    {mustVerifyEmail &&
                        auth.user.email_verified_at === null && (
                            <div className="space-y-2">
                                <p className="text-sm text-muted-foreground">
                                    Alamat email Anda belum terverifikasi.{' '}
                                    <Link
                                        href={send()}
                                        as="button"
                                        className="font-medium text-primary underline underline-offset-4"
                                    >
                                        Kirim ulang email verifikasi.
                                    </Link>
                                </p>

                                {status === 'verification-link-sent' && (
                                    <p className="text-sm font-medium text-success">
                                        Tautan verifikasi baru telah dikirim ke
                                        email Anda.
                                    </p>
                                )}
                            </div>
                        )}

                    <div className="flex items-center gap-2 pt-2">
                        <Button
                            type="submit"
                            variant="save"
                            disabled={processing}
                            data-test="update-profile-button"
                        >
                            {processing ? <Spinner /> : <Save />}
                            {processing ? 'Menyimpan...' : 'Simpan Perubahan'}
                        </Button>
                    </div>
                </form>
            </Panel>
        </>
    );
}
