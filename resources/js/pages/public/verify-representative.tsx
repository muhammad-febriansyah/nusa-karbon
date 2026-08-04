import { Form } from '@inertiajs/react';
import { CircleAlert, Search, ShieldCheck } from 'lucide-react';
import PageHeader from '@/components/public/page-header';
import SeoHead from '@/components/public/seo-head';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { home, verifyRepresentative } from '@/routes/public';

type Result = {
    id: number;
    name: string;
    position: string | null;
    region: string | null;
    photo_url: string | null;
    started_at: string | null;
    ended_at: string | null;
    is_current: boolean;
};

type Props = {
    query: string;
    searched: boolean;
    results: Result[];
    minQueryLength: number;
};

const dateFormat = new Intl.DateTimeFormat('id-ID', {
    month: 'long',
    year: 'numeric',
});

function formatDate(value: string | null): string | null {
    return value === null ? null : dateFormat.format(new Date(value));
}

/**
 * Verifikasi perwakilan resmi.
 *
 * Pemasok briket kerap ditiru akun yang mengaku perwakilan lalu meminta
 * transfer ke rekening pribadi. Halaman ini memberi pembeli cara memastikan
 * sendiri sebelum uang berpindah.
 *
 * Daftar lengkap tidak pernah ditampilkan: pengunjung harus sudah memegang satu
 * nama, email, atau nomor untuk diperiksa. Menampilkan seluruh tim justru
 * memberi penipu bahan mentah untuk menyamar lebih meyakinkan.
 */
export default function VerifyRepresentative({
    query,
    searched,
    results,
    minQueryLength,
}: Props) {
    return (
        <>
            <SeoHead
                title="Verifikasi Perwakilan Resmi"
                description="Periksa apakah orang yang menghubungi Anda benar-benar perwakilan resmi kami sebelum melakukan pembayaran."
            />

            <PageHeader
                eyebrow="Keamanan"
                title="Verifikasi perwakilan resmi"
                breadcrumbs={[{ name: 'Beranda', href: home() }]}
                description="Kami tidak pernah meminta pembayaran ke rekening pribadi. Sebelum mengirim uang, periksa dulu apakah orang yang menghubungi Anda memang terdaftar."
            >
                <Form
                    action={verifyRepresentative()}
                    method="get"
                    className="flex flex-col gap-3 sm:flex-row sm:items-end"
                >
                    <div className="w-full space-y-2 sm:max-w-md">
                        <Label htmlFor="q">
                            Nama, email, atau nomor WhatsApp
                        </Label>
                        <Input
                            id="q"
                            name="q"
                            defaultValue={query}
                            placeholder="Contoh: +62 812 3456 7890"
                            autoComplete="off"
                        />
                    </div>

                    <Button type="submit" variant="create" size="lg">
                        <Search />
                        Periksa
                    </Button>
                </Form>
            </PageHeader>

            <div className="mx-auto w-full max-w-3xl px-6 py-12">
                {!searched && (
                    <p className="text-base leading-relaxed text-muted-foreground">
                        Masukkan minimal {minQueryLength} karakter. Kami tidak
                        menampilkan daftar lengkap perwakilan — Anda perlu
                        memasukkan identitas yang ingin diperiksa, supaya data
                        tim tidak bisa dipanen untuk penyamaran.
                    </p>
                )}

                {searched && results.length === 0 && (
                    /*
                     * Hasil kosong adalah temuan, bukan kegagalan pencarian.
                     * Kalimatnya harus menyebut konsekuensinya dengan tegas,
                     * karena inilah saat pembeli paling berisiko kehilangan
                     * uang.
                     */
                    <div className="rounded-panel border border-destructive/40 bg-destructive/10 p-6">
                        <p className="flex items-start gap-3 text-base leading-relaxed">
                            <CircleAlert className="mt-0.5 size-5 shrink-0 text-destructive" />
                            <span>
                                <span className="font-semibold">
                                    Tidak ditemukan perwakilan aktif yang cocok
                                    dengan &ldquo;{query}&rdquo;.
                                </span>{' '}
                                Jangan melakukan pembayaran apa pun. Hubungi
                                kami lebih dulu lewat alamat resmi di situs ini
                                untuk memastikan.
                            </span>
                        </p>
                    </div>
                )}

                {searched && results.length > 0 && (
                    <div className="space-y-4">
                        <p className="text-sm text-muted-foreground">
                            <span className="tabular-nums">
                                {results.length}
                            </span>{' '}
                            perwakilan cocok dengan &ldquo;{query}&rdquo;.
                        </p>

                        <ul className="space-y-4">
                            {results.map((person) => (
                                <li
                                    key={person.id}
                                    className="rounded-panel flex gap-4 border border-border/70 bg-card p-5"
                                >
                                    <div className="size-16 shrink-0 overflow-hidden rounded-full border border-border/70 bg-muted">
                                        {person.photo_url && (
                                            <img
                                                src={person.photo_url}
                                                alt=""
                                                loading="lazy"
                                                decoding="async"
                                                className="size-full object-cover"
                                            />
                                        )}
                                    </div>

                                    <div className="min-w-0 flex-1">
                                        <p className="flex flex-wrap items-center gap-2 font-semibold">
                                            {person.name}
                                            {person.is_current ? (
                                                <span className="inline-flex items-center gap-1 rounded-full bg-success/10 px-2.5 py-0.5 text-xs font-medium text-success">
                                                    <ShieldCheck className="size-3.5" />
                                                    Perwakilan aktif
                                                </span>
                                            ) : (
                                                /*
                                                 * Mantan perwakilan tetap
                                                 * ditampilkan, dengan penanda
                                                 * tegas. Menyembunyikannya
                                                 * membuat pembeli mengira orang
                                                 * itu palsu, padahal ia memang
                                                 * pernah resmi — dan justru
                                                 * itulah celah yang sering
                                                 * dipakai untuk menipu.
                                                 */
                                                <span className="inline-flex items-center gap-1 rounded-full bg-warning/10 px-2.5 py-0.5 text-xs font-medium text-warning">
                                                    <CircleAlert className="size-3.5" />
                                                    Sudah tidak mewakili kami
                                                </span>
                                            )}
                                        </p>

                                        {(person.position || person.region) && (
                                            <p className="mt-1 text-sm text-muted-foreground">
                                                {[
                                                    person.position,
                                                    person.region,
                                                ]
                                                    .filter(Boolean)
                                                    .join(' · ')}
                                            </p>
                                        )}

                                        {formatDate(person.started_at) && (
                                            <p className="mt-2 text-xs text-muted-foreground">
                                                Terdaftar sejak{' '}
                                                {formatDate(person.started_at)}
                                                {formatDate(person.ended_at) &&
                                                    ` sampai ${formatDate(person.ended_at)}`}
                                            </p>
                                        )}
                                    </div>
                                </li>
                            ))}
                        </ul>

                        <p className="text-sm leading-relaxed text-muted-foreground">
                            Nama yang cocok bukan jaminan percakapan Anda aman.
                            Kami tetap tidak pernah meminta pembayaran ke
                            rekening pribadi atas nama perorangan.
                        </p>
                    </div>
                )}
            </div>
        </>
    );
}
