import { Link } from '@inertiajs/react';
import { ArrowLeft, Mail, MessageCircle } from 'lucide-react';
import GridPattern from '@/components/public/grid-pattern';
import SeoHead from '@/components/public/seo-head';
import Spotlight from '@/components/public/spotlight';
import { Button } from '@/components/ui/button';
import { usePublicSettings } from '@/hooks/use-public-settings';
import { home } from '@/routes/public';

/**
 * Halaman publik yang rutenya sudah final tapi isinya belum dikerjakan.
 *
 * `noindex` disetel eksplisit: URL-nya sudah tetap dan akan diindeks nanti,
 * tapi mengindeksnya sekarang berarti mesin pencari menyimpan halaman kosong
 * sebagai isi resmi alamat tersebut.
 *
 * Tombolnya sengaja menuju WhatsApp dan email, bukan halaman Kontak — halaman
 * Kontak sendiri masih dilayani komponen ini, jadi menautkannya hanya akan
 * memutar pengunjung kembali ke tempat yang sama.
 */
export default function ComingSoon({ page }: { page: string }) {
    const { text } = usePublicSettings();

    const whatsapp = text('whatsapp');
    const whatsappMessage = text('whatsapp_default_message');
    const email = text('email_sales') ?? text('email');

    const whatsappUrl =
        whatsapp === null
            ? null
            : `https://wa.me/${whatsapp.replace(/\D/g, '')}${
                  whatsappMessage === null
                      ? ''
                      : `?text=${encodeURIComponent(whatsappMessage)}`
              }`;

    return (
        <>
            <SeoHead title={page} noindex />

            <section className="relative overflow-hidden">
                <GridPattern
                    size={56}
                    className="[mask-image:radial-gradient(ellipse_at_center,black,transparent_70%)] text-border/50"
                />
                <Spotlight className="start-0 -top-40 text-primary md:start-60 md:-top-20" />

                <div className="relative mx-auto flex min-h-[70svh] w-full max-w-2xl flex-col items-center justify-center px-6 py-24 text-center">
                    <p className="text-xs font-semibold tracking-widest text-primary uppercase">
                        Sedang disiapkan
                    </p>

                    <h1 className="mt-4 text-4xl font-semibold tracking-tight text-balance sm:text-5xl">
                        {page}
                    </h1>

                    <p className="mt-5 text-base leading-relaxed text-muted-foreground">
                        Halaman ini sedang kami susun. Sementara itu, kebutuhan
                        Anda tetap bisa kami jawab langsung — kirimkan
                        spesifikasi dan volume yang dicari.
                    </p>

                    <div className="mt-9 flex flex-wrap justify-center gap-3">
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
                            <Button asChild variant="export" size="lg">
                                <a href={`mailto:${email}`}>
                                    <Mail />
                                    Kirim Email
                                </a>
                            </Button>
                        )}

                        <Button asChild variant="back" size="lg">
                            <Link href={home()}>
                                <ArrowLeft />
                                Kembali ke Beranda
                            </Link>
                        </Button>
                    </div>
                </div>
            </section>
        </>
    );
}
