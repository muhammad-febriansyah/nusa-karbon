import { Form, Head } from '@inertiajs/react';
import InputError from '@/components/input-error';
import PasswordInput from '@/components/password-input';
import AuthCard from '@/components/shared/auth-card';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { login } from '@/routes';
import { store } from '@/routes/register';

type Props = {
    passwordRules: string;
};

export default function Register({ passwordRules }: Props) {
    return (
        <>
            <Head title="Daftar" />

            <Form
                {...store.form()}
                resetOnSuccess={['password', 'password_confirmation']}
                disableWhileProcessing
            >
                {({ processing, errors }) => (
                    <AuthCard>
                        <div className="flex flex-col gap-1.5">
                            <Label
                                htmlFor="name"
                                className="text-[13px] text-foreground"
                            >
                                Nama Lengkap
                            </Label>
                            <Input
                                id="name"
                                type="text"
                                required
                                autoFocus
                                tabIndex={1}
                                autoComplete="name"
                                name="name"
                                placeholder="Nama lengkap"
                                aria-invalid={Boolean(errors.name)}
                                className="h-11 bg-card"
                            />
                            <InputError message={errors.name} />
                        </div>

                        <div className="flex flex-col gap-1.5">
                            <Label
                                htmlFor="email"
                                className="text-[13px] text-foreground"
                            >
                                Email
                            </Label>
                            <Input
                                id="email"
                                type="email"
                                required
                                tabIndex={2}
                                autoComplete="email"
                                name="email"
                                placeholder="nama@nusacarbon.com"
                                aria-invalid={Boolean(errors.email)}
                                className="h-11 bg-card"
                            />
                            <InputError message={errors.email} />
                        </div>

                        <div className="flex flex-col gap-1.5">
                            <Label
                                htmlFor="password"
                                className="text-[13px] text-foreground"
                            >
                                Kata Sandi
                            </Label>
                            <PasswordInput
                                id="password"
                                required
                                tabIndex={3}
                                autoComplete="new-password"
                                name="password"
                                placeholder="Kata sandi"
                                passwordrules={passwordRules}
                                aria-invalid={Boolean(errors.password)}
                                className="h-11 bg-card"
                            />
                            <InputError message={errors.password} />
                        </div>

                        <div className="flex flex-col gap-1.5">
                            <Label
                                htmlFor="password_confirmation"
                                className="text-[13px] text-foreground"
                            >
                                Konfirmasi Kata Sandi
                            </Label>
                            <PasswordInput
                                id="password_confirmation"
                                required
                                tabIndex={4}
                                autoComplete="new-password"
                                name="password_confirmation"
                                placeholder="Ulangi kata sandi"
                                passwordrules={passwordRules}
                                aria-invalid={Boolean(
                                    errors.password_confirmation,
                                )}
                                className="h-11 bg-card"
                            />
                            <InputError
                                message={errors.password_confirmation}
                            />
                        </div>

                        <Button
                            type="submit"
                            variant="save"
                            size="lg"
                            className="w-full"
                            tabIndex={5}
                            data-test="register-user-button"
                        >
                            {processing && <Spinner />}
                            Buat akun
                        </Button>
                    </AuthCard>
                )}
            </Form>

            <div className="text-center text-[13px] text-muted-foreground">
                Sudah punya akun?{' '}
                <TextLink
                    href={login()}
                    tabIndex={6}
                    className="font-medium text-primary no-underline"
                >
                    Masuk
                </TextLink>
            </div>
        </>
    );
}

Register.layout = {
    title: 'Buat Akun',
    description: 'Masukkan data Anda untuk membuat akun baru.',
};
