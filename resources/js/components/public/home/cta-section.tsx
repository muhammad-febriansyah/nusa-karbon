import { Link } from '@inertiajs/react';
import { MessageCircle, MessageSquareQuote } from 'lucide-react';
import BlurFade from '@/components/public/blur-fade';
import GridPattern from '@/components/public/grid-pattern';
import { Button } from '@/components/ui/button';
import { usePublicSettings } from '@/hooks/use-public-settings';
import { contact } from '@/routes/public';
import type { PublicPageBlock } from '@/types';

export default function CtaSection({ block }: { block?: PublicPageBlock }) {
    const { text } = usePublicSettings();

    const whatsapp = text('whatsapp');
    const whatsappMessage = text('whatsapp_default_message');
    const whatsappUrl =
        whatsapp === null
            ? null
            : `https://wa.me/${whatsapp.replace(/\D/g, '')}${
                  whatsappMessage === null
                      ? ''
                      : `?text=${encodeURIComponent(whatsappMessage)}`
              }`;

    return (
        <section className="mx-auto w-full max-w-7xl px-6 pb-4">
            <BlurFade>
                <div className="rounded-card relative overflow-hidden border border-border bg-card px-6 py-16 text-center sm:px-12">
                    <GridPattern
                        size={40}
                        className="[mask-image:radial-gradient(ellipse_at_center,black,transparent_70%)] text-border/60"
                    />
                    <div
                        aria-hidden
                        className="surface-brand-glow pointer-events-none absolute start-1/2 -top-32 size-[520px] -translate-x-1/2 rounded-full opacity-70"
                    />

                    <div className="relative mx-auto max-w-2xl">
                        <h2 className="text-3xl font-semibold tracking-tight text-balance sm:text-4xl">
                            {block?.title ?? 'Butuh penawaran?'}
                        </h2>

                        {block?.content && (
                            <p className="mt-4 text-base leading-relaxed text-muted-foreground">
                                {block.content}
                            </p>
                        )}

                        <div className="mt-9 flex flex-wrap justify-center gap-3">
                            <Button asChild variant="create" size="lg">
                                <Link href={contact()}>
                                    <MessageSquareQuote />
                                    Kirim Permintaan Penawaran
                                </Link>
                            </Button>

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
                        </div>
                    </div>
                </div>
            </BlurFade>
        </section>
    );
}
