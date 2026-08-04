import { Form, Link } from '@inertiajs/react';
import {
    AlertCircle,
    ArrowRight,
    CheckCircle2,
    Clock,
    Mail,
    MapPin,
    MessageCircle,
    MessageSquareQuote,
    Phone,
    ShieldCheck,
} from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
import { cloneElement, isValidElement, useEffect, useRef } from 'react';
import type { ReactElement, ReactNode } from 'react';
import InputError from '@/components/input-error';
import Combobox from '@/components/public/combobox';
import PageHeader from '@/components/public/page-header';
import SeoHead from '@/components/public/seo-head';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { usePublicSettings } from '@/hooks/use-public-settings';
import { CountryFlag } from '@/lib/country-flag';
import { home, verifyRepresentative } from '@/routes/public';
import { store } from '@/routes/public/contact';

type Option = { id: number; name: string };

type Props = {
    products: Option[];
    containers: Option[];
    incoterms: { value: string; label: string }[];
    /** Negara ISO 3166-1 alpha-2, sudah urut menurut nama di locale aktif. */
    countries: { value: string; label: string }[];
    /**
     * Nomor referensi permintaan yang baru saja terkirim, dibawa flash session
     * dari pengalihan setelah POST. Null pada kunjungan biasa.
     */
    reference: string | null;
};

/**
 * Tinggi kendali isian.
 *
 * 44 piksel di ponsel, turun ke tinggi baku panel admin begitu ada penunjuk
 * presisi. Tinggi 36 piksel cukup untuk tetikus, tetapi di bawah ambang sentuh
 * — dan ini satu-satunya formulir panjang di situs yang diisi orang asing dari
 * ponsel, sering sambil berjalan di sela pameran dagang.
 */
const CONTROL_HEIGHT = 'h-11 md:h-9';

/**
 * Nama isian dalam bahasa manusia, dipakai ringkasan galat di puncak formulir.
 *
 * Kunci di luar daftar ini sengaja tidak pernah ditampilkan: `website` adalah
 * umpan robot, dan menyebutnya di ringkasan sama saja memberi tahu robot bidang
 * mana yang harus dibiarkan kosong.
 */
const FIELD_LABELS: Record<string, string> = {
    company_name: 'Nama perusahaan',
    contact_name: 'Nama Anda',
    email: 'Email',
    country_code: 'Kode negara',
    phone: 'Telepon',
    whatsapp: 'WhatsApp',
    product_id: 'Produk yang diminati',
    container_type_id: 'Jenis kontainer',
    incoterms: 'Incoterms',
    port_of_destination: 'Pelabuhan tujuan',
    monthly_tonnage: 'Perkiraan volume per bulan',
    packaging_notes: 'Kebutuhan kemasan',
    message: 'Pesan',
};

/**
 * Langkah yang terjadi setelah tombol kirim ditekan.
 *
 * Ditulis dari perilaku yang benar-benar dijalankan controller — nomor
 * referensi memang dibuat, dan janji 24 jam kerja sudah jadi kalimat yang sama
 * di kepala halaman. Daftar seperti ini hanya berguna selama isinya bisa
 * ditepati.
 */
const AFTER_SUBMIT_STEPS = [
    'Permintaan tercatat dengan nomor referensi.',
    'Tim kami membalas dalam 24 jam kerja.',
    'Balasan berisi harga, jadwal kirim, dan contoh dokumen.',
];

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
    const hintId = `${htmlFor}-hint`;
    const errorId = `${htmlFor}-error`;

    const describedBy =
        [hint && !error ? hintId : null, error ? errorId : null]
            .filter((id): id is string => id !== null)
            .join(' ') || undefined;

    /*
     * Kendali isian disambungkan ke keterangan dan galatnya di sini, bukan di
     * tiap pemanggil. Tiga belas isian dikali dua atribut adalah dua puluh enam
     * kesempatan untuk lupa — dan yang terlupa tidak kelihatan di layar, hanya
     * hilang dari pembaca layar.
     */
    const control =
        isValidElement(children) &&
        cloneElement(children as ReactElement<Record<string, unknown>>, {
            'aria-invalid': error ? true : undefined,
            'aria-describedby': describedBy,
        });

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

            {control || children}

            {/*
             * Keterangan bantuan tetap tampil, bukan hanya jadi placeholder.
             * Placeholder lenyap begitu pengunjung mulai mengetik — persis saat
             * keterangannya paling dibutuhkan.
             */}
            {hint && !error && (
                <p id={hintId} className="text-xs text-muted-foreground">
                    {hint}
                </p>
            )}

            <InputError id={errorId} message={error} />
        </div>
    );
}

/**
 * Satu kartu kelompok isian.
 *
 * Memakai `ring`, bukan `border`, karena `legend` di dalam `fieldset` bergaris
 * tepi dirender di dalam takik garis itu — judul kelompok jadi duduk menumpang
 * di atas garis, dan keterangan dua barisnya ikut terangkat ke luar kartu.
 * `ring` adalah bayangan, bukan garis tepi, jadi takiknya tidak pernah dibuat.
 */
function FieldGroup({
    title,
    note,
    children,
}: {
    title: string;
    note?: string;
    children: ReactNode;
}) {
    return (
        <fieldset className="rounded-panel bg-card p-6 ring-1 ring-border/70 sm:p-8">
            <legend className="float-start w-full p-0">
                <span className="font-display text-lg font-bold tracking-[-0.02em]">
                    {title}
                </span>
                {note && (
                    <span className="mt-1 block text-sm leading-relaxed text-muted-foreground">
                        {note}
                    </span>
                )}
            </legend>

            <div className="clear-both pt-6">{children}</div>
        </fieldset>
    );
}

/**
 * Ringkasan galat di puncak formulir.
 *
 * Formulir ini punya tiga belas isian yang terbagi tiga kartu. Galat yang hanya
 * muncul di bawah isiannya bisa berada satu layar penuh dari tombol kirim —
 * pengunjung menekan kirim, halaman seolah tidak berubah, dan tidak ada
 * penjelasan apa pun di bagian yang sedang ia lihat. Ringkasan ini menarik
 * fokus ke dirinya begitu galat datang, lalu meloncatkan fokus ke isian yang
 * ditunjuk saat tautannya ditekan.
 */
function ErrorSummary({ errors }: { errors: Record<string, string> }) {
    const ref = useRef<HTMLDivElement>(null);

    const entries = Object.entries(errors).filter(
        ([field]) => field in FIELD_LABELS,
    );

    /*
     * Objek `errors` beridentitas baru tiap render, jadi efeknya bergantung
     * pada daftar kuncinya sebagai string. Tanpa ini fokus akan direbut ulang
     * pada setiap ketukan papan ketik selama galatnya masih ada.
     */
    const signature = entries.map(([field]) => field).join(',');

    useEffect(() => {
        if (signature !== '') {
            ref.current?.focus();
        }
    }, [signature]);

    if (entries.length === 0) {
        return null;
    }

    return (
        <div
            ref={ref}
            role="alert"
            tabIndex={-1}
            className="rounded-panel border border-destructive/40 bg-destructive/10 p-5 focus-visible:ring-2 focus-visible:ring-destructive focus-visible:ring-offset-2 focus-visible:outline-none"
        >
            <p className="flex items-start gap-2.5 text-sm font-medium">
                <AlertCircle
                    aria-hidden
                    className="mt-0.5 size-4 shrink-0 text-destructive"
                />
                {entries.length === 1
                    ? 'Ada satu isian yang perlu diperbaiki.'
                    : `Ada ${entries.length} isian yang perlu diperbaiki.`}
            </p>

            <ul className="mt-3 space-y-1.5 ps-7">
                {entries.map(([field, message]) => (
                    <li key={field} className="text-sm leading-relaxed">
                        <a
                            href={`#${field}`}
                            onClick={(event) => {
                                event.preventDefault();
                                document.getElementById(field)?.focus();
                            }}
                            className="font-medium text-destructive underline underline-offset-4 transition-colors hover:text-destructive/80"
                        >
                            {FIELD_LABELS[field]}
                        </a>
                        <span className="text-muted-foreground">
                            {' — '}
                            {message}
                        </span>
                    </li>
                ))}
            </ul>
        </div>
    );
}

/**
 * Tanda terima permintaan.
 *
 * Menampilkan nomor referensinya, bukan hanya kalimat "sudah kami terima".
 * Nomor itu satu-satunya pegangan pengunjung bila ia perlu menyusul lewat
 * WhatsApp atau email sebelum balasan pertama datang — dan sebelumnya nomor itu
 * dibuat server lalu dibuang sebelum sempat sampai ke layar.
 */
function SuccessPanel({ reference }: { reference: string }) {
    const ref = useRef<HTMLDivElement>(null);

    useEffect(() => {
        ref.current?.focus();
    }, [reference]);

    return (
        <div
            ref={ref}
            role="status"
            tabIndex={-1}
            className="rounded-panel mb-6 flex items-start gap-3 border border-success/40 bg-success/10 p-5 focus-visible:ring-2 focus-visible:ring-success focus-visible:ring-offset-2 focus-visible:outline-none"
        >
            <CheckCircle2
                aria-hidden
                className="mt-0.5 size-5 shrink-0 text-success"
            />

            <div className="min-w-0 space-y-1">
                <p className="text-sm leading-relaxed font-medium">
                    Permintaan Anda sudah kami terima.
                </p>
                <p className="text-sm leading-relaxed text-muted-foreground">
                    Nomor referensi{' '}
                    <span
                        className="font-mono text-foreground tabular-nums"
                        dir="ltr"
                    >
                        {reference}
                    </span>
                    . Tim kami membalas dalam 24 jam kerja.
                </p>
            </div>
        </div>
    );
}

function ChannelRow({
    icon: Icon,
    label,
    children,
}: {
    icon: LucideIcon;
    label: string;
    children: ReactNode;
}) {
    return (
        <li className="flex items-start gap-3">
            <Icon aria-hidden className="mt-0.5 size-4 shrink-0 text-primary" />
            <div className="min-w-0">
                <p className="text-xs text-muted-foreground">{label}</p>
                <div className="mt-0.5 text-sm leading-relaxed">{children}</div>
            </div>
        </li>
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
 *
 * Halaman ini tetap berpusat pada formulirnya, tetapi tidak lagi hanya
 * formulir. Pembeli yang belum siap mengisi tiga belas isian diberi jalur
 * tercepat lebih dulu — WhatsApp dan email di kepala halaman — dan pembeli yang
 * ingin memeriksa dulu siapa yang ia hubungi menemukan alamat, telepon, serta
 * jam kerja tanpa harus menggulung sampai kaki halaman.
 */
export default function Contact({
    products,
    containers,
    incoterms,
    countries,
    reference,
}: Props) {
    const { text } = usePublicSettings();

    const email = text('email');
    const phone = text('phone');
    const whatsapp = text('whatsapp');
    const whatsappMessage = text('whatsapp_default_message');
    const officeHours = text('office_hours');
    const address = text('address');

    const region = [
        text('city'),
        text('province'),
        text('postal_code'),
        text('country'),
    ]
        .filter((part): part is string => part !== null)
        .join(', ');

    const whatsappUrl =
        whatsapp === null
            ? null
            : `https://wa.me/${whatsapp.replace(/\D/g, '')}${
                  whatsappMessage === null
                      ? ''
                      : `?text=${encodeURIComponent(whatsappMessage)}`
              }`;

    /*
     * Dibedakan dari `hasDetails` karena judulnya menjanjikan hal berbeda.
     * "Hubungi langsung" di atas jam kerja dan nama negara adalah janji yang
     * tidak ditepati — tidak ada satu pun yang bisa dihubungi di sana. Judulnya
     * baru muncul kalau memang ada nomor atau alamat surel yang bisa dipakai.
     */
    const hasDirectChannel =
        email !== null || phone !== null || whatsapp !== null;

    const hasDetails =
        officeHours !== null || address !== null || region !== '';

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
            >
                {/*
                 * Dua jalur tercepat, di layar pertama. Sebagian pembeli hanya
                 * ingin menanyakan satu hal sebelum menyiapkan spesifikasi, dan
                 * memaksanya melewati formulir tiga belas isian untuk pertanyaan
                 * satu kalimat adalah cara paling mahal kehilangan pembeli.
                 */}
                {(whatsappUrl !== null || email !== null) && (
                    <div className="flex flex-wrap items-center gap-3">
                        {whatsappUrl !== null && (
                            <Button asChild variant="whatsapp" size="lg">
                                <a
                                    href={whatsappUrl}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >
                                    <MessageCircle />
                                    Chat WhatsApp
                                </a>
                            </Button>
                        )}

                        {email !== null && (
                            <Button asChild variant="cancel" size="lg">
                                <a href={`mailto:${email}`}>
                                    <Mail />
                                    Kirim email
                                </a>
                            </Button>
                        )}

                        <p className="text-sm text-muted-foreground">
                            atau isi formulir di bawah untuk penawaran lengkap.
                        </p>
                    </div>
                )}
            </PageHeader>

            <div className="mx-auto grid w-full max-w-7xl gap-10 px-6 py-12 lg:grid-cols-[minmax(0,1fr)_20rem] lg:gap-14 lg:py-16">
                <div className="min-w-0">
                    {reference !== null && (
                        <SuccessPanel reference={reference} />
                    )}

                    <Form
                        action={store()}
                        resetOnSuccess
                        className="space-y-6"
                        disableWhileProcessing
                    >
                        {({ errors, processing }) => (
                            <>
                                <ErrorSummary errors={errors} />

                                <FieldGroup
                                    title="Tentang Anda"
                                    note="Empat isian bertanda * sudah cukup untuk kami balas."
                                >
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
                                                placeholder="Al Noor Trading LLC"
                                                required
                                                autoComplete="organization"
                                                className={CONTROL_HEIGHT}
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
                                                placeholder="Ahmad Faisal"
                                                required
                                                autoComplete="name"
                                                className={CONTROL_HEIGHT}
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
                                                placeholder="nama@perusahaan.com"
                                                type="email"
                                                required
                                                autoComplete="email"
                                                inputMode="email"
                                                dir="ltr"
                                                className={CONTROL_HEIGHT}
                                            />
                                        </Field>

                                        {/*
                                         * Daftar pilihan, bukan dua huruf yang
                                         * diketik sendiri. Kode ISO bukan hal
                                         * yang dihafal pembeli — dan kesalahan
                                         * mengetiknya menghasilkan negara lain
                                         * yang sah secara format, jadi tidak
                                         * pernah tertangkap sampai tim sales
                                         * menghitung ongkos kirim ke tujuan
                                         * yang keliru.
                                         */}
                                        <Field
                                            label="Negara tujuan"
                                            htmlFor="country_code"
                                            required
                                            error={errors.country_code}
                                        >
                                            <Combobox
                                                id="country_code"
                                                name="country_code"
                                                className={CONTROL_HEIGHT}
                                                placeholder="Pilih negara"
                                                searchPlaceholder="Cari negara atau kode…"
                                                emptyText="Negara tidak ditemukan."
                                                options={countries.map(
                                                    (country) => ({
                                                        value: country.value,
                                                        label: country.label,
                                                        hint: country.value,
                                                        icon: (
                                                            <CountryFlag
                                                                code={
                                                                    country.value
                                                                }
                                                            />
                                                        ),
                                                    }),
                                                )}
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
                                                placeholder="+971 50 123 4567"
                                                type="tel"
                                                autoComplete="tel"
                                                inputMode="tel"
                                                dir="ltr"
                                                className={CONTROL_HEIGHT}
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
                                                placeholder="+971 50 123 4567"
                                                type="tel"
                                                inputMode="tel"
                                                dir="ltr"
                                                className={CONTROL_HEIGHT}
                                            />
                                        </Field>
                                    </div>
                                </FieldGroup>

                                <FieldGroup
                                    title="Detail permintaan"
                                    note="Semuanya opsional. Makin lengkap, makin dekat penawaran pertama kami dengan harga akhirnya."
                                >
                                    <div className="grid gap-5 sm:grid-cols-2">
                                        <Field
                                            label="Produk yang diminati"
                                            htmlFor="product_id"
                                            error={errors.product_id}
                                        >
                                            <Combobox
                                                id="product_id"
                                                name="product_id"
                                                className={CONTROL_HEIGHT}
                                                placeholder="Belum menentukan"
                                                searchPlaceholder="Cari produk…"
                                                emptyText="Produk tidak ditemukan."
                                                options={products.map(
                                                    (product) => ({
                                                        value: String(
                                                            product.id,
                                                        ),
                                                        label: product.name,
                                                    }),
                                                )}
                                            />
                                        </Field>

                                        <Field
                                            label="Jenis kontainer"
                                            htmlFor="container_type_id"
                                            error={errors.container_type_id}
                                        >
                                            <Combobox
                                                id="container_type_id"
                                                name="container_type_id"
                                                className={CONTROL_HEIGHT}
                                                placeholder="Belum menentukan"
                                                searchPlaceholder="Cari jenis kontainer…"
                                                emptyText="Jenis kontainer tidak ditemukan."
                                                options={containers.map(
                                                    (container) => ({
                                                        value: String(
                                                            container.id,
                                                        ),
                                                        label: container.name,
                                                    }),
                                                )}
                                            />
                                        </Field>

                                        <Field
                                            label="Incoterms"
                                            htmlFor="incoterms"
                                            error={errors.incoterms}
                                        >
                                            <Combobox
                                                id="incoterms"
                                                name="incoterms"
                                                className={CONTROL_HEIGHT}
                                                placeholder="Belum menentukan"
                                                searchPlaceholder="Cari term…"
                                                emptyText="Term tidak ditemukan."
                                                options={incoterms.map(
                                                    (term) => ({
                                                        value: term.value,
                                                        label: term.label,
                                                        hint: term.value,
                                                    }),
                                                )}
                                            />
                                        </Field>

                                        <Field
                                            label="Pelabuhan tujuan"
                                            htmlFor="port_of_destination"
                                            error={errors.port_of_destination}
                                        >
                                            <Input
                                                id="port_of_destination"
                                                name="port_of_destination"
                                                placeholder="Jebel Ali, Dubai"
                                                className={CONTROL_HEIGHT}
                                            />
                                        </Field>

                                        <div className="sm:col-span-2">
                                            <Field
                                                label="Perkiraan volume per bulan"
                                                htmlFor="monthly_tonnage"
                                                hint="Boleh perkiraan kasar."
                                                error={errors.monthly_tonnage}
                                            >
                                                <Input
                                                    id="monthly_tonnage"
                                                    name="monthly_tonnage"
                                                placeholder="2 kontainer atau 40 ton"
                                                    className={CONTROL_HEIGHT}
                                                />
                                            </Field>
                                        </div>

                                        <div className="sm:col-span-2">
                                            <Field
                                                label="Kebutuhan kemasan"
                                                htmlFor="packaging_notes"
                                                error={errors.packaging_notes}
                                            >
                                                <Textarea
                                                    id="packaging_notes"
                                                    name="packaging_notes"
                                                    placeholder="Master carton 20 kg, cetak logo sendiri"
                                                    rows={3}
                                                />
                                            </Field>
                                        </div>
                                    </div>
                                </FieldGroup>

                                <FieldGroup
                                    title="Pesan"
                                    note="Hal lain yang perlu kami tahu — jadwal, sertifikat, atau kebutuhan merek pribadi."
                                >
                                    <Field
                                        label="Pesan"
                                        htmlFor="message"
                                        error={errors.message}
                                    >
                                        <Textarea
                                            id="message"
                                            name="message"
                                            placeholder="Jadwal kirim yang diharapkan, sertifikat yang dibutuhkan, atau pertanyaan lain."
                                            rows={5}
                                        />
                                    </Field>
                                </FieldGroup>

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

                                <div className="flex flex-wrap items-center gap-x-4 gap-y-3">
                                    <Button
                                        type="submit"
                                        variant="create"
                                        size="lg"
                                        disabled={processing}
                                        className="w-full sm:w-auto"
                                    >
                                        <MessageSquareQuote />
                                        {processing
                                            ? 'Mengirim...'
                                            : 'Kirim permintaan'}
                                    </Button>

                                    <p className="text-xs leading-relaxed text-muted-foreground">
                                        Bertanda{' '}
                                        <span className="text-primary">*</span>{' '}
                                        wajib diisi. Data Anda hanya dipakai
                                        untuk membalas permintaan ini.
                                    </p>
                                </div>
                            </>
                        )}
                    </Form>
                </div>

                {/*
                 * Rincian kontak menemani formulir di layar lebar dan turun ke
                 * bawahnya di ponsel. Isinya sengaja bukan salinan tombol di
                 * kepala halaman: yang di atas adalah tindakan yang ditekan,
                 * yang di sini adalah keterangan yang dibaca dan disalin —
                 * alamat, jam kerja, nomor yang perlu dicatat.
                 */}
                <aside className="lg:sticky lg:top-28 lg:self-start">
                    <div className="rounded-panel bg-card p-6 ring-1 ring-border/70">
                        <h2 className="font-display text-lg font-bold tracking-[-0.02em]">
                            Setelah Anda kirim
                        </h2>

                        <ol className="mt-4 space-y-3">
                            {AFTER_SUBMIT_STEPS.map((step, index) => (
                                <li
                                    key={step}
                                    className="flex items-start gap-3 text-sm leading-relaxed text-muted-foreground"
                                >
                                    <span
                                        aria-hidden
                                        className="mt-0.5 flex size-5 shrink-0 items-center justify-center rounded-full bg-primary/10 font-mono text-[11px] font-medium text-primary tabular-nums"
                                    >
                                        {index + 1}
                                    </span>
                                    {step}
                                </li>
                            ))}
                        </ol>

                        {(hasDirectChannel || hasDetails) && (
                            <>
                                <hr className="my-6 border-border/70" />

                                {/*
                                 * Judulnya menyesuaikan isinya. Tanpa nomor
                                 * atau surel, "Hubungi langsung" adalah janji
                                 * kosong; tetapi membiarkan barisnya tanpa
                                 * judul sama sekali membuat jam kerja dan
                                 * alamat terbaca sebagai lanjutan langkah
                                 * "Setelah Anda kirim" di atasnya.
                                 */}
                                <h2 className="font-display text-lg font-bold tracking-[-0.02em]">
                                    {hasDirectChannel
                                        ? 'Hubungi langsung'
                                        : 'Jam kerja & lokasi'}
                                </h2>

                                <ul className="mt-4 space-y-4">
                                    {email !== null && (
                                        <ChannelRow icon={Mail} label="Email">
                                            <a
                                                href={`mailto:${email}`}
                                                dir="ltr"
                                                className="break-all transition-colors hover:text-primary"
                                            >
                                                {email}
                                            </a>
                                        </ChannelRow>
                                    )}

                                    {phone !== null && (
                                        <ChannelRow
                                            icon={Phone}
                                            label="Telepon"
                                        >
                                            <a
                                                href={`tel:${phone.replace(/\s/g, '')}`}
                                                dir="ltr"
                                                className="transition-colors hover:text-primary"
                                            >
                                                {phone}
                                            </a>
                                        </ChannelRow>
                                    )}

                                    {whatsapp !== null &&
                                        whatsappUrl !== null && (
                                            <ChannelRow
                                                icon={MessageCircle}
                                                label="WhatsApp"
                                            >
                                                <a
                                                    href={whatsappUrl}
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    dir="ltr"
                                                    className="transition-colors hover:text-primary"
                                                >
                                                    {whatsapp}
                                                </a>
                                            </ChannelRow>
                                        )}

                                    {officeHours !== null && (
                                        <ChannelRow
                                            icon={Clock}
                                            label="Jam kerja"
                                        >
                                            {officeHours}
                                        </ChannelRow>
                                    )}

                                    {(address !== null || region !== '') && (
                                        <ChannelRow
                                            icon={MapPin}
                                            label="Alamat"
                                        >
                                            {address && <span>{address}</span>}
                                            {address && region !== '' && <br />}
                                            {region !== '' && (
                                                <span>{region}</span>
                                            )}
                                        </ChannelRow>
                                    )}
                                </ul>
                            </>
                        )}
                    </div>

                    {/*
                     * Peringatan penipuan diletakkan di sini, bukan hanya di
                     * kaki halaman. Inilah satu-satunya halaman tempat pembeli
                     * baru menyerahkan datanya dan menunggu balasan berisi
                     * nomor rekening — dan tepat di titik itulah penipu
                     * mengaku sebagai perwakilan kami. Peringatan di kaki
                     * halaman terbaca setelah keputusan diambil.
                     */}
                    <div className="rounded-panel mt-6 bg-card p-6 ring-1 ring-border/70">
                        <h2 className="flex items-center gap-2 font-display text-lg font-bold tracking-[-0.02em]">
                            <ShieldCheck
                                aria-hidden
                                className="size-5 shrink-0 text-primary"
                            />
                            Sebelum mengirim dana
                        </h2>

                        <p className="mt-3 text-sm leading-relaxed text-muted-foreground">
                            Kami tidak pernah meminta pembayaran ke rekening
                            pribadi. Nama dan nomor perwakilan resmi bisa Anda
                            periksa sendiri sebelum mentransfer.
                        </p>

                        <Link
                            href={verifyRepresentative()}
                            className="mt-4 inline-flex items-center gap-2 text-sm font-medium text-primary underline-offset-4 transition-colors hover:underline focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
                        >
                            Verifikasi perwakilan
                            <ArrowRight
                                aria-hidden
                                className="size-4 rtl:rotate-180"
                            />
                        </Link>
                    </div>
                </aside>
            </div>
        </>
    );
}
