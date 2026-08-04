import { Ship } from 'lucide-react';
import Marquee from '@/components/public/marquee';
import Section from '@/components/public/section';
import type { PublicShippingPartner } from '@/types';

/** Di bawah jumlah ini, baris berjalan terlihat seperti animasi yang macet. */
const MARQUEE_THRESHOLD = 5;

function PartnerLogo({ partner }: { partner: PublicShippingPartner }) {
    const content = partner.logo_url ? (
        <img
            src={partner.logo_url}
            alt={partner.name}
            loading="lazy"
            decoding="async"
            className="h-10 w-auto max-w-40 object-contain opacity-70 transition-opacity duration-300 group-hover/logo:opacity-100"
        />
    ) : (
        <span className="flex items-center gap-2 text-sm font-medium text-muted-foreground">
            <Ship className="size-4" />
            {partner.name}
        </span>
    );

    if (partner.website_url === null) {
        return <div className="group/logo flex items-center">{content}</div>;
    }

    return (
        <a
            href={partner.website_url}
            target="_blank"
            rel="noopener noreferrer"
            aria-label={partner.name}
            className="group/logo flex items-center"
        >
            {content}
        </a>
    );
}

export default function ShippingPartnerSection({
    items,
}: {
    items: PublicShippingPartner[];
}) {
    if (items.length === 0) {
        return null;
    }

    return (
        <Section
            eyebrow="Logistik"
            title="Dikirim lewat pelayaran yang sudah Anda kenal"
            description="Jadwal keberangkatan reguler dari pelabuhan Surabaya dan Jakarta."
            centered
        >
            {items.length >= MARQUEE_THRESHOLD ? (
                <Marquee durationSeconds={35}>
                    {items.map((partner) => (
                        <PartnerLogo key={partner.id} partner={partner} />
                    ))}
                </Marquee>
            ) : (
                <div className="flex flex-wrap items-center justify-center gap-x-12 gap-y-8">
                    {items.map((partner) => (
                        <PartnerLogo key={partner.id} partner={partner} />
                    ))}
                </div>
            )}
        </Section>
    );
}
