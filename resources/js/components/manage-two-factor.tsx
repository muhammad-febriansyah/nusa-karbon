import { Form, router, usePage } from '@inertiajs/react';
import { Copy, RotateCcw, ShieldCheck, ShieldOff, X } from 'lucide-react';
import { useState } from 'react';
import InputError from '@/components/input-error';
import Panel from '@/components/shared/panel';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { useClipboard } from '@/hooks/use-clipboard';
import {
    confirm as confirmTwoFactor,
    disable as disableTwoFactor,
    enable as enableTwoFactor,
    regenerateRecoveryCodes,
} from '@/routes/two-factor';

export type Props = {
    canManageTwoFactor: boolean;
    twoFactorEnabled?: boolean;
    twoFactorConfirmed?: boolean;
    twoFactorQrCodeSvg?: string | null;
    twoFactorSecretKey?: string | null;
    twoFactorRecoveryCodes?: string[];
};

export default function ManageTwoFactor({
    canManageTwoFactor,
    twoFactorEnabled,
    twoFactorConfirmed,
    twoFactorQrCodeSvg,
    twoFactorSecretKey,
    twoFactorRecoveryCodes = [],
}: Props) {
    const { errors } = usePage().props;
    const [copiedText, copy] = useClipboard();
    const [processing, setProcessing] = useState(false);

    const startSetup = () => {
        setProcessing(true);
        router.post(
            enableTwoFactor().url,
            {},
            {
                preserveScroll: true,
                onFinish: () => setProcessing(false),
            },
        );
    };

    if (!canManageTwoFactor) {
        return null;
    }

    if (!twoFactorEnabled) {
        return (
            <Panel
                title="Verifikasi Dua Langkah"
                description="Tambahkan lapisan keamanan kedua memakai aplikasi autentikator."
            >
                <Button
                    variant="toggle"
                    onClick={startSetup}
                    disabled={processing}
                    data-test="enable-two-factor-button"
                >
                    {processing ? <Spinner /> : <ShieldCheck />}
                    {processing ? 'Menyiapkan...' : 'Aktifkan 2FA'}
                </Button>
            </Panel>
        );
    }

    return (
        <Panel
            title="Verifikasi Dua Langkah"
            description={
                twoFactorConfirmed
                    ? 'Verifikasi dua langkah aktif untuk akun ini.'
                    : 'Pindai kode QR lalu masukkan kode dari aplikasi autentikator untuk menyelesaikan aktivasi.'
            }
        >
            <div className="space-y-4">
                {!twoFactorConfirmed && twoFactorQrCodeSvg && (
                    <>
                        <div
                            className="w-fit rounded-[var(--radius)] border border-border bg-card p-4 [&>svg]:size-44"
                            dangerouslySetInnerHTML={{
                                __html: twoFactorQrCodeSvg,
                            }}
                        />

                        {twoFactorSecretKey && (
                            <div className="flex flex-col gap-1.5">
                                <Label htmlFor="two-factor-secret">
                                    Kunci rahasia
                                </Label>
                                <div className="flex items-center gap-2">
                                    <Input
                                        id="two-factor-secret"
                                        readOnly
                                        dir="ltr"
                                        value={twoFactorSecretKey}
                                        className="font-mono"
                                    />
                                    <Button
                                        type="button"
                                        variant="duplicate"
                                        onClick={() => copy(twoFactorSecretKey)}
                                    >
                                        <Copy />
                                        {copiedText === twoFactorSecretKey
                                            ? 'Tersalin'
                                            : 'Salin'}
                                    </Button>
                                </div>
                                <p className="text-xs text-muted-foreground">
                                    Pakai kunci ini bila aplikasi autentikator
                                    tidak bisa memindai kode QR.
                                </p>
                            </div>
                        )}

                        <Form
                            {...confirmTwoFactor.form()}
                            options={{ preserveScroll: true }}
                            className="flex flex-col gap-1.5"
                        >
                            {({
                                processing: confirming,
                                errors: formErrors,
                            }) => (
                                <>
                                    <Label htmlFor="code">
                                        Kode autentikasi
                                    </Label>
                                    <div className="flex items-center gap-2">
                                        <Input
                                            id="code"
                                            name="code"
                                            inputMode="numeric"
                                            autoComplete="one-time-code"
                                            placeholder="123456"
                                            dir="ltr"
                                            aria-invalid={Boolean(
                                                formErrors.code,
                                            )}
                                            className="max-w-40 tracking-[0.3em] tabular-nums"
                                        />
                                        <Button
                                            type="submit"
                                            variant="save"
                                            disabled={confirming}
                                            data-test="confirm-two-factor-button"
                                        >
                                            {confirming ? (
                                                <Spinner />
                                            ) : (
                                                <ShieldCheck />
                                            )}
                                            Konfirmasi
                                        </Button>
                                    </div>
                                    <InputError
                                        message={
                                            formErrors.code ?? errors?.code
                                        }
                                    />
                                </>
                            )}
                        </Form>
                    </>
                )}

                {twoFactorConfirmed && twoFactorRecoveryCodes.length > 0 && (
                    <div className="flex flex-col gap-2">
                        <Label>Kode pemulihan</Label>
                        <p className="text-xs text-muted-foreground">
                            Simpan di tempat aman. Tiap kode hanya bisa dipakai
                            sekali bila Anda kehilangan akses ke aplikasi
                            autentikator.
                        </p>
                        <ul
                            dir="ltr"
                            className="grid gap-1 rounded-[var(--radius)] border border-border bg-muted p-4 font-mono text-[13px] sm:grid-cols-2"
                        >
                            {twoFactorRecoveryCodes.map((code) => (
                                <li key={code}>{code}</li>
                            ))}
                        </ul>
                    </div>
                )}

                <div className="flex flex-wrap items-center gap-2">
                    {twoFactorConfirmed && (
                        <Form
                            {...regenerateRecoveryCodes.form()}
                            options={{ preserveScroll: true }}
                        >
                            {({ processing: regenerating }) => (
                                <Button
                                    type="submit"
                                    variant="reset"
                                    disabled={regenerating}
                                >
                                    {regenerating ? <Spinner /> : <RotateCcw />}
                                    Buat Ulang Kode Pemulihan
                                </Button>
                            )}
                        </Form>
                    )}

                    <Dialog>
                        <DialogTrigger asChild>
                            <Button
                                variant="delete"
                                data-test="disable-two-factor-button"
                            >
                                <ShieldOff />
                                Nonaktifkan 2FA
                            </Button>
                        </DialogTrigger>
                        <DialogContent>
                            <DialogTitle>
                                Nonaktifkan verifikasi dua langkah?
                            </DialogTitle>
                            <DialogDescription>
                                Akun hanya akan dilindungi kata sandi. Kode
                                pemulihan yang ada sekarang ikut dihapus.
                            </DialogDescription>
                            <DialogFooter className="gap-2">
                                <DialogClose asChild>
                                    <Button variant="cancel">
                                        <X />
                                        Batal
                                    </Button>
                                </DialogClose>
                                <Form
                                    {...disableTwoFactor.form()}
                                    options={{ preserveScroll: true }}
                                >
                                    {({ processing: disabling }) => (
                                        <Button
                                            type="submit"
                                            variant="delete"
                                            disabled={disabling}
                                        >
                                            {disabling ? (
                                                <Spinner />
                                            ) : (
                                                <ShieldOff />
                                            )}
                                            Nonaktifkan 2FA
                                        </Button>
                                    )}
                                </Form>
                            </DialogFooter>
                        </DialogContent>
                    </Dialog>
                </div>
            </div>
        </Panel>
    );
}
