import { Form, Head } from '@inertiajs/react';
import InputError from '@/components/input-error';
import PasswordInput from '@/components/password-input';
import AuthCard from '@/components/shared/auth-card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { update } from '@/routes/password';

type Props = {
    token: string;
    email: string;
    passwordRules: string;
};

export default function ResetPassword({ token, email, passwordRules }: Props) {
    return (
        <>
            <Head title="Reset kata sandi" />

            <Form
                {...update.form()}
                transform={(data) => ({ ...data, token, email })}
                resetOnSuccess={['password', 'password_confirmation']}
            >
                {({ processing, errors }) => (
                    <AuthCard>
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
                                name="email"
                                autoComplete="email"
                                value={email}
                                readOnly
                                aria-invalid={Boolean(errors.email)}
                                className="h-11 bg-muted"
                            />
                            <InputError message={errors.email} />
                        </div>

                        <div className="flex flex-col gap-1.5">
                            <Label
                                htmlFor="password"
                                className="text-[13px] text-foreground"
                            >
                                Kata Sandi Baru
                            </Label>
                            <PasswordInput
                                id="password"
                                name="password"
                                autoComplete="new-password"
                                autoFocus
                                placeholder="Kata sandi baru"
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
                                name="password_confirmation"
                                autoComplete="new-password"
                                placeholder="Ulangi kata sandi baru"
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
                            disabled={processing}
                            data-test="reset-password-button"
                        >
                            {processing && <Spinner />}
                            Reset kata sandi
                        </Button>
                    </AuthCard>
                )}
            </Form>
        </>
    );
}

ResetPassword.layout = {
    title: 'Reset Kata Sandi',
    description: 'Silakan masukkan kata sandi baru Anda di bawah ini.',
};
