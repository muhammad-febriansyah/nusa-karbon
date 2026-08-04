import { Form } from '@inertiajs/react';
import { CheckCircle2, MessageSquareQuote } from 'lucide-react';
import type { ReactNode } from 'react';
import InputError from '@/components/input-error';
import PageHeader from '@/components/public/page-header';
import SeoHead from '@/components/public/seo-head';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { usePublicSettings } from '@/hooks/use-public-settings';
import { home } from '@/routes/public';
import { store } from '@/routes/public/contact';

type Option = { id: number; name: string };

type Props = {
    products: Option[];
    containers: Option[];
    incoterms: { value: string; label: string }[];
};

function Field({
    label,
    htmlFor,
    required = false,
    hint,
    error,
    children,
}: {
    label: string;
    htmlFor: string;
    required?: boolean;
    hint?: string;
    error?: string;
    children: ReactNode;
}) {
    return (
        <div className="space-y-2">
            <Label htmlFor={htmlFor}>
                {label}
                {required && (
                    <span aria-hidden className="ms-0.5 text-primary">
                        *
                    </span>
                )}
            </Label>
            {children}
            {/*
             * Keterangan bantuan tetap tampil, bukan hanya jadi placeholder.
             * Placeholder lenyap begitu pengunjung mulai mengetik — persis saat
             * keterangannya paling dibutuhkan.
             */}
            {hint && !error && (
                <p className="text-xs text-muted-foreground">{hint}</p>
            )}
            <InputError message={error} />
        </div>
    );
}

/**
 * Formulir permintaan penawaran.
 *
 * Hanya empat isian yang wajib: perusahaan, nama, email, negara tujuan. Tanpa
 * keempatnya tim sales tidak bisa membalas. Sisanya membuat penawaran lebih
 * tepat, tetapi mewajibkannya membuat pembeli yang baru menjajaki batal
 * mengirim — dan lead yang batal terkirim lebih mahal daripada lead yang
 * datanya kurang lengkap.
 */
export default function Contact({ products, containers, incoterms }: Props) {
    const { text } = usePublicSettings();

    return (
        <>
            <SeoHead
                title="Minta Penawaran"
                description="Kirim kebutuhan Anda — ukuran, volume, pelabuhan tujuan — dan kami balas dengan harga serta jadwal kirim."
            />

            <PageHeader
                eyebrow="Kontak"
                title="Minta penawaran"
                breadcrumbs={[{ name: 'Beranda', href: home() }]}
                description="Isi yang Anda ketahui saja. Kami balas dalam 24 jam kerja dengan harga, jadwal kirim, dan contoh dokumen."
            />

            <div className="mx-auto w-full max-w-3xl px-6 py-12">
                <Form
                    action={store()}
                    resetOnSuccess
                    className="space-y-6"
                    disableWhileProcessing
                >
                    {({ errors, processing, recentlySuccessful }) => (
                        <>
                            {recentlySuccessful && (
                                <p
                                    role="status"
                                    className="rounded-panel flex items-start gap-3 border border-success/40 bg-success/10 p-4 text-sm leading-relaxed"
                                >
                                    <CheckCircle2 className="mt-0.5 size-4 shrink-0 text-success" />
                                    <span>
                                        Permintaan Anda sudah kami terima. Tim
                                        kami membalas dalam 24 jam kerja.
                                    </span>
                                </p>
                            )}

                            <div className="grid gap-5 sm:grid-cols-2">
                                <Field
                                    label="Nama perusahaan"
                                    htmlFor="company_name"
                                    required
                                    error={errors.company_name}
                                >
                                    <Input
                                        id="company_name"
                                        name="company_name"
                                        required
                                        autoComplete="organization"
                                    />
                                </Field>

                                <Field
                                    label="Nama Anda"
                                    htmlFor="contact_name"
                                    required
                                    error={errors.contact_name}
                                >
                                    <Input
                                        id="contact_name"
                                        name="contact_name"
                                        required
                                        autoComplete="name"
                                    />
                                </Field>

                                <Field
                                    label="Email"
                                    htmlFor="email"
                                    required
                                    error={errors.email}
                                >
                                    <Input
                                        id="email"
                                        name="email"
                                        type="email"
                                        required
                                        autoComplete="email"
                                        dir="ltr"
                                    />
                                </Field>

                                <Field
                                    label="Kode negara"
                                    htmlFor="country_code"
                                    required
                                    hint="Dua huruf, misalnya SA, RU, atau AE."
                                    error={errors.country_code}
                                >
                                    <Input
                                        id="country_code"
                                        name="country_code"
                                        required
                                        maxLength={2}
                                        autoComplete="country"
                                        dir="ltr"
                                        className="uppercase"
                                    />
                                </Field>

                                <Field
                                    label="Telepon"
                                    htmlFor="phone"
                                    error={errors.phone}
                                >
                                    <Input
                                        id="phone"
                                        name="phone"
                                        type="tel"
                                        autoComplete="tel"
                                        dir="ltr"
                                    />
                                </Field>

                                <Field
                                    label="WhatsApp"
                                    htmlFor="whatsapp"
                                    error={errors.whatsapp}
                                >
                                    <Input
                                        id="whatsapp"
                                        name="whatsapp"
                                        type="tel"
                                        dir="ltr"
                                    />
                                </Field>
                            </div>

                            <div className="grid gap-5 sm:grid-cols-2">
                                <Field
                                    label="Produk yang diminati"
                                    htmlFor="product_id"
                                    error={errors.product_id}
                                >
                                    {/*
                                     * `select` bawaan peramban, bukan komponen
                                     * kustom. Daftarnya panjang, dan pemilih
                                     * bawaan ponsel jauh lebih enak dipakai
                                     * untuk daftar sepanjang ini daripada
                                     * dropdown yang digambar ulang.
                                     */}
                                    <select
                                        id="product_id"
                                        name="product_id"
                                        defaultValue=""
                                        className="h-9 w-full rounded-[var(--radius)] border border-input bg-card px-3 text-sm focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                    >
                                        <option value="">
                                            Belum menentukan
                                        </option>
                                        {products.map((product) => (
                                            <option
                                                key={product.id}
                                                value={product.id}
                                            >
                                                {product.name}
                                            </option>
                                        ))}
                                    </select>
                                </Field>

                                <Field
                                    label="Jenis kontainer"
                                    htmlFor="container_type_id"
                                    error={errors.container_type_id}
                                >
                                    <select
                                        id="container_type_id"
                                        name="container_type_id"
                                        defaultValue=""
                                        className="h-9 w-full rounded-[var(--radius)] border border-input bg-card px-3 text-sm focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                    >
                                        <option value="">
                                            Belum menentukan
                                        </option>
                                        {containers.map((container) => (
                                            <option
                                                key={container.id}
                                                value={container.id}
                                            >
                                                {container.name}
                                            </option>
                                        ))}
                                    </select>
                                </Field>

                                <Field
                                    label="Incoterms"
                                    htmlFor="incoterms"
                                    error={errors.incoterms}
                                >
                                    <select
                                        id="incoterms"
                                        name="incoterms"
                                        defaultValue=""
                                        className="h-9 w-full rounded-[var(--radius)] border border-input bg-card px-3 text-sm focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                    >
                                        <option value="">
                                            Belum menentukan
                                        </option>
                                        {incoterms.map((term) => (
                                            <option
                                                key={term.value}
                                                value={term.value}
                                            >
                                                {term.label}
                                            </option>
                                        ))}
                                    </select>
                                </Field>

                                <Field
                                    label="Pelabuhan tujuan"
                                    htmlFor="port_of_destination"
                                    error={errors.port_of_destination}
                                >
                                    <Input
                                        id="port_of_destination"
                                        name="port_of_destination"
                                    />
                                </Field>
                            </div>

                            <Field
                                label="Perkiraan volume per bulan"
                                htmlFor="monthly_tonnage"
                                hint="Boleh perkiraan kasar, misalnya 2 kontainer atau 40 ton."
                                error={errors.monthly_tonnage}
                            >
                                <Input
                                    id="monthly_tonnage"
                                    name="monthly_tonnage"
                                />
                            </Field>

                            <Field
                                label="Kebutuhan kemasan"
                                htmlFor="packaging_notes"
                                error={errors.packaging_notes}
                            >
                                <Textarea
                                    id="packaging_notes"
                                    name="packaging_notes"
                                    rows={3}
                                />
                            </Field>

                            <Field
                                label="Pesan"
                                htmlFor="message"
                                error={errors.message}
                            >
                                <Textarea
                                    id="message"
                                    name="message"
                                    rows={5}
                                />
                            </Field>

                            {/*
                             * Umpan robot. Disembunyikan dari mata dan dari
                             * jalur papan ketik, tetapi tidak memakai
                             * `display: none` — sebagian robot memang melewati
                             * bidang yang tersembunyi total. `aria-hidden`
                             * menjaganya tidak pernah dibacakan pembaca layar.
                             */}
                            <div
                                aria-hidden
                                className="absolute -left-[9999px] h-0 w-0 overflow-hidden"
                            >
                                <label htmlFor="website">
                                    Jangan isi kolom ini
                                </label>
                                <input
                                    id="website"
                                    name="website"
                                    type="text"
                                    tabIndex={-1}
                                    autoComplete="off"
                                />
                            </div>

                            <div className="flex flex-wrap items-center gap-4">
                                <Button
                                    type="submit"
                                    variant="create"
                                    size="lg"
                                    disabled={processing}
                                >
                                    <MessageSquareQuote />
                                    {processing
                                        ? 'Mengirim...'
                                        : 'Kirim permintaan'}
                                </Button>

                                <p className="text-xs text-muted-foreground">
                                    Bertanda{' '}
                                    <span className="text-primary">*</span>{' '}
                                    wajib diisi.
                                </p>
                            </div>

                            {text('office_hours') && (
                                <p className="text-sm text-muted-foreground">
                                    Jam kerja kami {text('office_hours')}.
                                </p>
                            )}
                        </>
                    )}
                </Form>
            </div>
        </>
    );
}
