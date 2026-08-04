import { Form, Head } from '@inertiajs/react';
import AuthCard from '@/components/shared/auth-card';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import { logout } from '@/routes';
import { send } from '@/routes/verification';

export default function VerifyEmail({ status }: { status?: string }) {
    return (
        <>
            <Head title="Verifikasi email" />

            {status === 'verification-link-sent' && (
                <div className="rounded-[var(--radius)] border border-success/25 bg-success/10 px-3.5 py-2.5 text-center text-sm font-medium text-success">
                    Tautan verifikasi baru telah dikirim ke alamat email yang
                    Anda daftarkan.
                </div>
            )}

            <Form {...send.form()}>
                {({ processing }) => (
                    <AuthCard className="items-center text-center">
                        <Button
                            disabled={processing}
                            variant="save"
                            size="lg"
                            className="w-full"
                        >
                            {processing && <Spinner />}
                            Kirim ulang email verifikasi
                        </Button>

                        <TextLink
                            href={logout()}
                            className="text-[13px] font-medium text-muted-foreground no-underline hover:text-foreground"
                        >
                            Keluar
                        </TextLink>
                    </AuthCard>
                )}
            </Form>
        </>
    );
}

VerifyEmail.layout = {
    title: 'Verifikasi Email',
    description:
        'Verifikasi alamat email Anda dengan menekan tautan yang baru saja kami kirimkan.',
};
