import { Form, Head } from '@inertiajs/react';
import { CircleAlert, Lock, LogIn, Mail } from 'lucide-react';
import InputError from '@/components/input-error';
import PasswordInput from '@/components/password-input';
import AuthCard from '@/components/shared/auth-card';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { cn } from '@/lib/utils';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';

type Props = {
    status?: string;
    canResetPassword: boolean;
};

export default function Login({ status, canResetPassword }: Props) {
    return (
        <>
            <Head title="Masuk" />

            {status && (
                <div className="rounded-[var(--radius)] border border-success/25 bg-success/10 px-3.5 py-2.5 text-center text-sm font-medium text-success">
                    {status}
                </div>
            )}

            <Form
                {...store.form()}
                resetOnSuccess={['password']}
                className="flex flex-col gap-5.5"
            >
                {({ processing, errors }) => {
                    const hasError = Boolean(errors.email || errors.password);

                    return (
                        <>
                            {hasError && (
                                <div className="flex items-center gap-2.5 rounded-[var(--radius)] border border-s-[3px] border-destructive/25 border-s-destructive bg-destructive/10 px-3.5 py-2.5">
                                    <CircleAlert className="size-4 shrink-0 text-destructive" />
                                    <span className="text-[13px] text-destructive">
                                        {errors.email ?? errors.password}
                                    </span>
                                </div>
                            )}

                            <AuthCard>
                                <div className="flex flex-col gap-1.5">
                                    <Label
                                        htmlFor="email"
                                        className="text-[13px] text-foreground"
                                    >
                                        Email
                                    </Label>
                                    <div className="relative flex items-center">
                                        <Mail
                                            className={cn(
                                                'absolute start-3 z-10 size-4',
                                                errors.email
                                                    ? 'text-destructive'
                                                    : 'text-muted-foreground',
                                            )}
                                        />
                                        <Input
                                            id="email"
                                            type="email"
                                            name="email"
                                            required
                                            autoFocus
                                            tabIndex={1}
                                            autoComplete="email"
                                            placeholder="nama@nusacarbon.com"
                                            aria-invalid={Boolean(errors.email)}
                                            className="h-11 bg-card ps-9.5"
                                        />
                                    </div>
                                    <InputError message={errors.email} />
                                </div>

                                <div className="flex flex-col gap-1.5">
                                    <Label
                                        htmlFor="password"
                                        className="text-[13px] text-foreground"
                                    >
                                        Kata Sandi
                                    </Label>
                                    <div className="relative flex items-center">
                                        <Lock
                                            className={cn(
                                                'absolute start-3 z-10 size-4',
                                                errors.password
                                                    ? 'text-destructive'
                                                    : 'text-muted-foreground',
                                            )}
                                        />
                                        <PasswordInput
                                            id="password"
                                            name="password"
                                            required
                                            tabIndex={2}
                                            autoComplete="current-password"
                                            placeholder="••••••••••"
                                            aria-invalid={Boolean(
                                                errors.password,
                                            )}
                                            className="h-11 bg-card ps-9.5"
                                        />
                                    </div>
                                    <InputError message={errors.password} />
                                </div>

                                <div className="flex items-center justify-between">
                                    <div className="flex items-center gap-2">
                                        <Checkbox
                                            id="remember"
                                            name="remember"
                                            tabIndex={3}
                                        />
                                        <Label
                                            htmlFor="remember"
                                            className="text-[13px] font-normal text-muted-foreground"
                                        >
                                            Ingat saya
                                        </Label>
                                    </div>

                                    {canResetPassword && (
                                        <TextLink
                                            href={request()}
                                            tabIndex={5}
                                            className="text-[13px] font-medium text-primary no-underline"
                                        >
                                            Lupa kata sandi?
                                        </TextLink>
                                    )}
                                </div>

                                <Button
                                    type="submit"
                                    variant="save"
                                    size="lg"
                                    className="w-full"
                                    tabIndex={4}
                                    disabled={processing}
                                    data-test="login-button"
                                >
                                    {processing ? (
                                        <Spinner />
                                    ) : (
                                        <LogIn className="size-4" />
                                    )}
                                    Masuk
                                </Button>
                            </AuthCard>

                            <div className="text-center text-[13px] text-muted-foreground">
                                Belum punya akun?{' '}
                                <TextLink
                                    href={register()}
                                    tabIndex={5}
                                    className="font-medium text-primary no-underline"
                                >
                                    Daftar
                                </TextLink>
                            </div>
                        </>
                    );
                }}
            </Form>
        </>
    );
}

Login.layout = {
    title: 'Masuk ke Panel Admin',
    description: 'Masukkan email dan kata sandi Anda untuk melanjutkan.',
};
