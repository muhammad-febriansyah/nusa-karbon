import { Form, Head } from '@inertiajs/react';
import { KeyRound, ShieldCheck } from 'lucide-react';
import { useState } from 'react';
import InputError from '@/components/input-error';
import AuthCard from '@/components/shared/auth-card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { store as twoFactorLoginStore } from '@/routes/two-factor/login';

export default function TwoFactorChallenge() {
    const [useRecoveryCode, setUseRecoveryCode] = useState(false);

    return (
        <>
            <Head title="Verifikasi dua langkah" />

            <Form
                {...twoFactorLoginStore.form()}
                resetOnError={['code', 'recovery_code']}
            >
                {({ processing, errors }) => (
                    <AuthCard>
                        {useRecoveryCode ? (
                            <div className="flex flex-col gap-1.5">
                                <Label htmlFor="recovery_code">
                                    Kode pemulihan
                                </Label>
                                <Input
                                    id="recovery_code"
                                    name="recovery_code"
                                    autoComplete="one-time-code"
                                    autoFocus
                                    placeholder="xxxxxxxx-xxxxxxxx"
                                    aria-invalid={Boolean(errors.recovery_code)}
                                    className="h-11 bg-card"
                                />
                                <InputError message={errors.recovery_code} />
                            </div>
                        ) : (
                            <div className="flex flex-col gap-1.5">
                                <Label htmlFor="code">Kode autentikasi</Label>
                                <Input
                                    id="code"
                                    name="code"
                                    inputMode="numeric"
                                    autoComplete="one-time-code"
                                    autoFocus
                                    placeholder="123456"
                                    dir="ltr"
                                    aria-invalid={Boolean(errors.code)}
                                    className="h-11 bg-card tracking-[0.3em] tabular-nums"
                                />
                                <InputError message={errors.code} />
                            </div>
                        )}

                        <Button
                            type="submit"
                            variant="save"
                            size="lg"
                            className="w-full"
                            disabled={processing}
                            data-test="two-factor-submit-button"
                        >
                            {processing ? <Spinner /> : <ShieldCheck />}
                            {processing ? 'Memverifikasi...' : 'Verifikasi'}
                        </Button>

                        <Button
                            type="button"
                            variant="cancel"
                            size="lg"
                            className="w-full"
                            onClick={() => setUseRecoveryCode((prev) => !prev)}
                        >
                            <KeyRound />
                            {useRecoveryCode
                                ? 'Pakai kode autentikasi'
                                : 'Pakai kode pemulihan'}
                        </Button>
                    </AuthCard>
                )}
            </Form>
        </>
    );
}

TwoFactorChallenge.layout = {
    title: 'Verifikasi Dua Langkah',
    description:
        'Masukkan kode dari aplikasi autentikator Anda untuk melanjutkan.',
};
