import { Form, Head } from '@inertiajs/react';
import InputError from '@/components/input-error';
import PasswordInput from '@/components/password-input';
import AuthCard from '@/components/shared/auth-card';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { store } from '@/routes/password/confirm';

export default function ConfirmPassword() {
    return (
        <>
            <Head title="Konfirmasi kata sandi" />

            <Form {...store.form()} resetOnSuccess={['password']}>
                {({ processing, errors }) => (
                    <AuthCard>
                        <div className="flex flex-col gap-1.5">
                            <Label
                                htmlFor="password"
                                className="text-[13px] text-foreground"
                            >
                                Kata Sandi
                            </Label>
                            <PasswordInput
                                id="password"
                                name="password"
                                placeholder="Kata sandi"
                                autoComplete="current-password"
                                autoFocus
                                aria-invalid={Boolean(errors.password)}
                                className="h-11 bg-card"
                            />
                            <InputError message={errors.password} />
                        </div>

                        <Button
                            type="submit"
                            variant="save"
                            size="lg"
                            className="w-full"
                            disabled={processing}
                            data-test="confirm-password-button"
                        >
                            {processing && <Spinner />}
                            Konfirmasi kata sandi
                        </Button>
                    </AuthCard>
                )}
            </Form>
        </>
    );
}

ConfirmPassword.layout = {
    title: 'Konfirmasi Kata Sandi',
    description:
        'Ini adalah area aman. Konfirmasi kata sandi Anda sebelum melanjutkan.',
};
