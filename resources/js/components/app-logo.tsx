import BrandMark from '@/components/brand-mark';

/**
 * Sidebar hanya menampilkan logo — nama perusahaan sudah terbaca dari judul tab
 * dan halaman, jadi wordmark di sini cuma menghabiskan ruang.
 */
export default function AppLogo() {
    return (
        <BrandMark
            showLabel={false}
            className="group-data-[collapsible=icon]:justify-center"
            imageClassName="h-10 shrink-0 group-data-[collapsible=icon]:h-8"
        />
    );
}
