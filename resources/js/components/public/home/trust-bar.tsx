import type { PublicCertificate } from '@/types';

/**
 * Deretan sertifikat di bawah hero.
 *
 * Sengaja diam. Versi sebelumnya menjalankan baris ini sebagai marquee, dan
 * teks berjalan adalah hal terburuk yang bisa dilakukan pada satu-satunya
 * bagian halaman yang tugasnya membuktikan sesuatu: nomor sertifikat harus bisa
 * dibaca, disalin, dan dicocokkan — bukan dikejar dengan mata.
 *
 * Logo tanpa nomor tidak membuktikan apa pun, jadi yang diutamakan di sini
 * adalah teks penerbit dan nomornya; logo hanya ikut bila sudah diunggah.
 */
export default function TrustBar({
    certificates,
}: {
    certificates: PublicCertificate[];
}) {
    if (certificates.length === 0) {
        return null;
    }

    return (
        <section className="border-b border-border/70">
            <div className="mx-auto w-full max-w-7xl px-6 py-12">
                <h2 className="text-sm font-medium text-muted-foreground">
                    Sertifikasi &amp; kepatuhan
                </h2>

                <ul className="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    {certificates.map((certificate) => (
                        <li
                            key={certificate.id}
                            className="rounded-panel flex flex-col gap-2 border border-border/70 bg-card p-5"
                        >
                            {certificate.logo_url && (
                                <img
                                    src={certificate.logo_url}
                                    alt=""
                                    loading="lazy"
                                    decoding="async"
                                    className="h-7 w-auto self-start object-contain"
                                />
                            )}

                            <p className="text-sm leading-snug font-medium">
                                {certificate.name}
                            </p>

                            {(certificate.issuer ||
                                certificate.certificate_number) && (
                                <p className="text-xs leading-relaxed text-muted-foreground">
                                    {certificate.issuer}
                                    {certificate.certificate_number && (
                                        <span
                                            className="mt-0.5 block font-mono"
                                            dir="ltr"
                                        >
                                            {certificate.certificate_number}
                                        </span>
                                    )}
                                </p>
                            )}
                        </li>
                    ))}
                </ul>
            </div>
        </section>
    );
}
