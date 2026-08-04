/**
 * Bentuk data yang dikirim `App\Http\Resources\Public\*`.
 *
 * Berbeda dari tipe admin di `models.d.ts` yang memuat seluruh terjemahan:
 * di sini teks sudah diselesaikan server ke locale aktif, jadi bertipe
 * `string | null`, bukan peta locale.
 */

export type PublicPageBlock = {
    key: string;
    type: string;
    title: string | null;
    content: string | null;
    image_url: string | null;
    extra: Record<string, unknown> | null;
};

export type PublicProductCard = {
    id: number;
    code: string;
    name: string | null;
    slug: string | null;
    short_description: string | null;
    shape: string | null;
    size_mm: string | null;
    moq_kg: number | null;
    category?: string | null;
    cover_url: string | null;
    is_calculable: boolean;
};

/** Satu angka uji pada pita spesifikasi hero. */
export type PublicSpec = {
    parameter: string;
    value: string;
    unit: string | null;
};

export type PublicSpecSheet = {
    code: string;
    name: string | null;
    slug: string | null;
    shape: string | null;
    size_mm: string | null;
    specs: PublicSpec[];
};

/**
 * Hasil Container Calculator untuk produk acuan beranda.
 *
 * `limited_by` menyebut batas mana yang habis lebih dulu — berat muat atau
 * volume ruang. Denah di beranda menandai yang mana, karena itulah yang
 * menentukan apakah pembeli sebaiknya menambah pesanan atau tidak.
 */
export type PublicContainerLoad = {
    container: {
        code: string;
        name: string;
        max_payload_kg: number;
        max_cbm: number;
        internal_length_m: number | null;
        internal_width_m: number | null;
        internal_height_m: number | null;
    };
    product: {
        code: string;
        name: string | null;
        slug: string | null;
        master_carton_weight_kg: number;
    };
    cartons: number;
    net_weight_kg: number;
    gross_weight_kg: number;
    volume_cbm: number;
    /** Rasio 0–1, bukan persen. */
    utilisation: number;
    limited_by: 'weight' | 'volume';
};

export type PublicProductCategory = {
    id: number;
    name: string;
    slug: string;
};

/** Satu baris spesifikasi pada tabel uji halaman detail produk. */
export type PublicSpecRow = {
    parameter: string;
    value: string;
    unit: string | null;
    is_highlighted: boolean;
};

export type PublicProductDocument = {
    id: number;
    title: string | null;
    type: string;
    url: string;
    /** Ukuran berkas dalam byte; formatnya diserahkan ke frontend. */
    file_size: number | null;
};

/**
 * Data master carton, dikelompokkan seperti yang dikirim server.
 *
 * Keenam angka ini selalu dibaca bersama — pembeli memakainya untuk memeriksa
 * ulang hitungan kontainer, dan angka yang terpisah dari kelompoknya kehilangan
 * arti.
 */
export type PublicMasterCarton = {
    net_weight_kg: number | null;
    tare_weight_kg: number | null;
    volume_cbm: number | null;
    length_cm: number | null;
    width_cm: number | null;
    height_cm: number | null;
    inner_box_weight_kg: number | null;
    inner_boxes_per_carton: number | null;
};

export type PublicProductDetail = {
    id: number;
    code: string;
    name: string | null;
    slug: string | null;
    short_description: string | null;
    description: string | null;
    shape: string | null;
    size_mm: string | null;
    moq_kg: number | null;
    category?: string | null;
    cover_url: string | null;
    gallery_urls: string[];
    is_calculable: boolean;
    master_carton: PublicMasterCarton;
    specifications: PublicSpecRow[];
    documents: PublicProductDocument[];
    packaging_options: {
        id: number;
        name: string | null;
        net_weight_kg: number | null;
    }[];
};

/**
 * Hasil Container Calculator untuk satu jenis kontainer.
 *
 * `limited_by` menyebut batas mana yang habis lebih dulu — berat muat atau
 * volume ruang. Itu yang menentukan apakah menambah pesanan masih ada gunanya.
 */
export type PublicContainerLoadRow = {
    container_id: number;
    code: string;
    name: string;
    max_payload_kg: number;
    max_cbm: number;
    cartons: number;
    net_weight_kg: number;
    gross_weight_kg: number;
    volume_cbm: number;
    /** Rasio 0–1, bukan persen. */
    utilisation: number;
    limited_by: 'weight' | 'volume';
};

export type PublicPostCard = {
    id: number;
    title: string | null;
    slug: string | null;
    excerpt: string | null;
    cover_url: string | null;
    reading_minutes: number | null;
    published_at: string | null;
    category?: string | null;
};

/**
 * Satu artikel lengkap dengan isinya.
 *
 * `content` berupa HTML yang sudah dibersihkan Purifier saat disimpan admin.
 */
export type PublicPostDetail = {
    id: number;
    title: string | null;
    slug: string | null;
    excerpt: string | null;
    content: string | null;
    cover_url: string | null;
    reading_minutes: number | null;
    published_at: string | null;
    category?: string | null;
    author?: string | null;
    tags: string[];
};

export type PublicTeamMember = {
    id: number;
    name: string;
    position: string | null;
    bio: string | null;
    photo_url: string | null;
    linkedin_url: string | null;
};

export type PublicFeature = {
    id: number;
    title: string | null;
    description: string | null;
    /** Nama ikon lucide, sudah tervalidasi saat disimpan admin. */
    icon: string | null;
    image_url: string | null;
};

/**
 * Satu tahap produksi. Nomor tahapnya tidak ikut dikirim — urutannya sudah
 * ditentukan `sort_order` di admin, dan frontend menurunkannya dari posisi.
 */
export type PublicProductionStep = {
    id: number;
    title: string | null;
    description: string | null;
    image_url: string | null;
};

export type PublicPackagingOption = {
    id: number;
    name: string | null;
    description: string | null;
    net_weight_kg: number | null;
    image_url: string | null;
};

export type PublicCertificate = {
    id: number;
    name: string | null;
    description: string | null;
    issuer: string | null;
    certificate_number: string | null;
    logo_url: string | null;
    /** Tanggal terbit dan masa berlaku; keduanya ditanyakan penerbit saat dicek. */
    issued_at: string | null;
    expires_at: string | null;
};

/**
 * Satu parameter yang diuji beserta ambang yang dijanjikan.
 *
 * `result_label` dan `result_value` adalah pasangan: labelnya menyebut dasar
 * angkanya ("Standar internal"), nilainya menyebut ambangnya ("Maks 2,5%").
 * Nilai tanpa label kehilangan artinya.
 */
export type PublicQcProtocol = {
    id: number;
    title: string | null;
    description: string | null;
    result_label: string | null;
    result_value: string | null;
    image_url: string | null;
};

export type PublicShippingPartner = {
    id: number;
    name: string;
    logo_url: string | null;
    website_url: string | null;
};
