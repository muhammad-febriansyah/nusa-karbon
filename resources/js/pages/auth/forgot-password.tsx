import { Form, Head } from '@inertiajs/react';
import InputError from '@/components/input-error';
import AuthCard from '@/components/shared/auth-card';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { login } from '@/routes';
import { email } from '@/routes/password';

export default function ForgotPassword({ status }: { status?: string }) {
    return (
        <>
            <Head title="Lupa kata sandi" />

            {status && (
                <div className="rounded-[var(--radius)] border border-success/25 bg-success/10 px-3.5 py-2.5 text-center text-sm font-medium text-success">
                    {status}
                </div>
            )}

            <Form {...email.form()}>
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
                                autoComplete="off"
                                autoFocus
                                placeholder="nama@nusacarbon.com"
                                aria-invalid={Boolean(errors.email)}
                                className="h-11 bg-card"
                            />
                            <InputError message={errors.email} />
                        </div>

                        <Button
                            variant="save"
                            size="lg"
                            className="w-full"
                            disabled={processing}
                            data-test="email-password-reset-link-button"
                        >
                            {processing && <Spinner />}
                            Kirim tautan reset
                        </Button>
                    </AuthCard>
                )}
            </Form>

            <div className="space-x-1 text-center text-[13px] text-muted-foreground">
                <span>Atau kembali ke</span>
                <TextLink
                    href={login()}
                    className="font-medium text-primary no-underline"
                >
                    halaman masuk
                </TextLink>
            </div>
        </>
    );
}

ForgotPassword.layout = {
    title: 'Lupa Kata Sandi',
    description: 'Masukkan email Anda untuk menerima tautan reset kata sandi.',
};
