import { Form } from '@inertiajs/react';
import { Save } from 'lucide-react';
import { useRef } from 'react';
import SecurityController from '@/actions/App/Http/Controllers/Settings/SecurityController';
import InputError from '@/components/input-error';
import type { Props as ManageTwoFactorProps } from '@/components/manage-two-factor';
import ManageTwoFactor from '@/components/manage-two-factor';
import PasswordInput from '@/components/password-input';
import PageHeader from '@/components/shared/page-header';
import Panel from '@/components/shared/panel';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { dashboard } from '@/routes/admin';
import { edit as editProfile } from '@/routes/admin/profile';
import { edit } from '@/routes/admin/security';

type Props = {
    passwordRules: string;
} & ManageTwoFactorProps;

export default function Security(props: Props) {
    const passwordInput = useRef<HTMLInputElement>(null);
    const currentPasswordInput = useRef<HTMLInputElement>(null);

    return (
        <>
            <PageHeader
                title="Keamanan"
                description="Perbarui kata sandi dan atur verifikasi dua langkah untuk akun Anda."
                breadcrumbs={[
                    { title: 'Dashboard', href: dashboard() },
                    { title: 'Pengaturan', href: editProfile() },
                    { title: 'Keamanan', href: edit() },
                ]}
            />

            <Panel
                title="Ubah Kata Sandi"
                description="Gunakan kata sandi yang panjang dan acak agar akun tetap aman."
            >
                <Form
                    {...SecurityController.update.form()}
                    options={{ preserveScroll: true }}
                    resetOnError={[
                        'password',
                        'password_confirmation',
                        'current_password',
                    ]}
                    resetOnSuccess
                    onError={(errors) => {
                        if (errors.password) {
                            passwordInput.current?.focus();
                        }

                        if (errors.current_password) {
                            currentPasswordInput.current?.focus();
                        }
                    }}
                    className="space-y-5"
                >
                    {({ errors, processing }) => (
                        <>
                            {/* Sama seperti form modul lain: dua kolom, kata
                                sandi saat ini berdiri sendiri di baris atas. */}
                            <div className="grid gap-5 sm:grid-cols-2">
                                <div className="grid gap-1.5 sm:col-span-2">
                                    <Label htmlFor="current_password">
                                        Kata sandi saat ini
                                    </Label>
                                    <PasswordInput
                                        id="current_password"
                                        ref={currentPasswordInput}
                                        name="current_password"
                                        autoComplete="current-password"
                                        placeholder="Kata sandi saat ini"
                                        aria-invalid={Boolean(
                                            errors.current_password,
                                        )}
                                    />
                                    <InputError
                                        message={errors.current_password}
                                    />
                                </div>

                                <div className="grid gap-1.5">
                                    <Label htmlFor="password">
                                        Kata sandi baru
                                    </Label>
                                    <PasswordInput
                                        id="password"
                                        ref={passwordInput}
                                        name="password"
                                        autoComplete="new-password"
                                        placeholder="Kata sandi baru"
                                        passwordrules={props.passwordRules}
                                        aria-invalid={Boolean(errors.password)}
                                    />
                                    <InputError message={errors.password} />
                                </div>

                                <div className="grid gap-1.5">
                                    <Label htmlFor="password_confirmation">
                                        Konfirmasi kata sandi
                                    </Label>
                                    <PasswordInput
                                        id="password_confirmation"
                                        name="password_confirmation"
                                        autoComplete="new-password"
                                        placeholder="Ulangi kata sandi baru"
                                        passwordrules={props.passwordRules}
                                        aria-invalid={Boolean(
                                            errors.password_confirmation,
                                        )}
                                    />
                                    <InputError
                                        message={errors.password_confirmation}
                                    />
                                </div>
                            </div>

                            <div className="flex items-center gap-2 pt-2">
                                <Button
                                    variant="save"
                                    disabled={processing}
                                    data-test="update-password-button"
                                >
                                    {processing ? <Spinner /> : <Save />}
                                    {processing
                                        ? 'Menyimpan...'
                                        : 'Simpan Kata Sandi'}
                                </Button>
                            </div>
                        </>
                    )}
                </Form>
            </Panel>

            <ManageTwoFactor
                canManageTwoFactor={props.canManageTwoFactor}
                twoFactorEnabled={props.twoFactorEnabled}
                twoFactorConfirmed={props.twoFactorConfirmed}
                twoFactorQrCodeSvg={props.twoFactorQrCodeSvg}
                twoFactorSecretKey={props.twoFactorSecretKey}
                twoFactorRecoveryCodes={props.twoFactorRecoveryCodes}
            />
        </>
    );
}
