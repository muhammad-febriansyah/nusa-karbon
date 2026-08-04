import type { TranslationStatus } from '@/components/shared/translation-indicator';

/** Nilai kolom translatable per locale. */
export type Translated = Record<string, string | null>;

export type GalleryItem = {
    id: number;
    url: string;
    name: string;
};

export type Product = {
    id: number;
    code: string;
    name: Translated;
    slug: Translated;
    short_description: Translated;
    description: Translated;
    meta_title: Translated;
    meta_description: Translated;
    product_category_id: number | null;
    category?: { id: number | null; name: Translated } | null;
    shape: string | null;
    size_mm: string | null;
    master_carton_weight_kg: number | null;
    master_carton_tare_kg: number | null;
    master_carton_cbm: number | null;
    master_carton_length_cm: number | null;
    master_carton_width_cm: number | null;
    master_carton_height_cm: number | null;
    inner_box_weight_kg: number | null;
    inner_boxes_per_carton: number | null;
    moq_kg: number | null;
    sort_order: number;
    is_active: boolean;
    is_featured: boolean;
    is_calculable: boolean;
    completeness: Record<string, TranslationStatus>;
    cover_url: string | null;
    gallery: GalleryItem[];
    specifications: ProductSpecification[];
    documents: ProductDocument[];
    packaging_option_ids: number[];
    created_at: string | null;
    updated_at: string | null;
};

/** Estimasi muatan satu produk pada satu tipe kontainer. */
export type ContainerLoad = {
    container_id: number;
    code: string;
    name: string;
    max_payload_kg: number;
    max_cbm: number;
    cartons: number;
    net_weight_kg: number;
    gross_weight_kg: number;
    volume_cbm: number;
    utilisation: number;
    limited_by: 'weight' | 'volume';
};

export type SelectOption = {
    value: string;
    label: string;
};

/** Baris spesifikasi seperti dikirim server. */
export type ProductSpecification = {
    id: number;
    parameter: Translated;
    value: string;
    unit: string | null;
    is_highlighted: boolean;
};

/** Baris spesifikasi di dalam form; `key` lokal untuk drag-and-drop. */
export type SpecificationRow = {
    key: string;
    id: number | null;
    parameter: Translated;
    value: string;
    unit: string;
    is_highlighted: boolean;
};

/** Dokumen produk seperti dikirim server. */
export type ProductDocument = {
    id: number;
    type: string;
    type_label: string;
    title: Translated;
    file_name: string;
    file_size: number;
    mime_type: string;
    url: string;
    batch_reference: string | null;
    issued_at: string | null;
    download_count: number;
    is_active: boolean;
};

/**
 * Baris dokumen di dalam form. `token` terisi hanya bila ada unggahan baru;
 * pada baris lama itu berarti berkasnya diganti.
 */
export type DocumentRow = {
    key: string;
    id: number | null;
    type: string;
    title: Translated;
    batch_reference: string;
    issued_at: string | null;
    is_active: boolean;
    token: string | null;
    /** Berkas yang sudah tersimpan; null untuk baris baru. */
    file: { name: string; size: number; url: string } | null;
};

export type SpecificationTemplateRow = {
    parameter: string;
    value: string;
    unit: string | null;
    is_highlighted: boolean;
};

export type PackagingOption = {
    id: number;
    name: Translated;
    description: Translated;
    image_url: string | null;
    net_weight_kg: number | null;
    sort_order: number;
    is_active: boolean;
    created_at: string | null;
};

export type ContainerType = {
    id: number;
    code: string;
    name: Translated;
    max_payload_kg: number;
    max_cbm: number;
    internal_length_m: number | null;
    internal_width_m: number | null;
    internal_height_m: number | null;
    /** L × W × H, null bila salah satu dimensi kosong. */
    internal_volume_cbm: number | null;
    sort_order: number;
    is_active: boolean;
    created_at: string | null;
};

export type Certificate = {
    id: number;
    name: Translated;
    description: Translated;
    issuer: string | null;
    certificate_number: string | null;
    logo_url: string | null;
    file_url: string | null;
    /** Tanggal ISO `yyyy-MM-dd`. */
    issued_at: string | null;
    expires_at: string | null;
    is_expired: boolean;
    /** Kedaluwarsa dalam 60 hari ke depan dan belum lewat. */
    is_expiring_soon: boolean;
    show_in_trust_bar: boolean;
    sort_order: number;
    is_active: boolean;
    created_at: string | null;
};

export type Representative = {
    id: number;
    name: string;
    position: Translated;
    region: string | null;
    email: string;
    phone: string | null;
    whatsapp: string | null;
    photo_url: string | null;
    /** Tanggal ISO `yyyy-MM-dd`. */
    started_at: string | null;
    ended_at: string | null;
    sort_order: number;
    is_active: boolean;
    created_at: string | null;
};

export type ProductionStep = {
    id: number;
    title: Translated;
    description: Translated;
    image_url: string | null;
    sort_order: number;
    is_active: boolean;
    created_at: string | null;
};

export type QcProtocol = {
    id: number;
    title: Translated;
    description: Translated;
    result_label: Translated;
    result_value: string | null;
    image_url: string | null;
    sort_order: number;
    is_active: boolean;
    created_at: string | null;
};

export type Feature = {
    id: number;
    title: Translated;
    description: Translated;
    /** Nama ikon lucide, huruf kecil dengan tanda hubung. */
    icon: string | null;
    image_url: string | null;
    sort_order: number;
    is_active: boolean;
    created_at: string | null;
};

export type ShippingPartner = {
    id: number;
    name: string;
    logo_url: string | null;
    website_url: string | null;
    sort_order: number;
    is_active: boolean;
    created_at: string | null;
};

export type TeamMember = {
    id: number;
    name: string;
    position: Translated;
    bio: Translated;
    photo_url: string | null;
    linkedin_url: string | null;
    sort_order: number;
    is_active: boolean;
    created_at: string | null;
};

export type FaqCategory = {
    id: number;
    name: Translated;
    /** Hanya dikirim pada halaman index kategori. */
    faqs_count?: number;
    sort_order: number;
    is_active: boolean;
    created_at: string | null;
};

export type Faq = {
    id: number;
    faq_category_id: number | null;
    category_name?: Translated;
    question: Translated;
    answer: Translated;
    sort_order: number;
    is_active: boolean;
    created_at: string | null;
};

export type PageBlock = {
    id: number;
    page_key: string;
    block_key: string;
    type: 'rich_text' | 'text_image' | 'list' | 'stat';
    type_label: string;
    title: Translated;
    content: Translated;
    image_url: string | null;
    /** Data tambahan sesuai `type`; bentuknya berbeda per tipe blok. */
    extra: {
        items?: string[];
        value?: string;
        unit?: string;
    } | null;
    sort_order: number;
    is_active: boolean;
};

export type GalleryAlbumItem = {
    id: number;
    type: 'image' | 'video';
    type_label: string;
    file_url: string | null;
    video_url: string | null;
    /** Berkas unggahan bila ada, kalau tidak URL penyedia luar. */
    playback_url: string | null;
    caption: Translated;
    sort_order: number;
};

export type Gallery = {
    id: number;
    title: Translated;
    slug: Translated;
    description: Translated;
    cover_url: string | null;
    /** Hanya dikirim pada halaman index. */
    items_count?: number;
    /** Hanya dikirim pada halaman kelola isi album. */
    items?: GalleryAlbumItem[];
    sort_order: number;
    is_active: boolean;
    created_at: string | null;
};

export type PostCategory = {
    id: number;
    name: Translated;
    slug: Translated;
    description: Translated;
    /** Hanya dikirim pada halaman index kategori. */
    posts_count?: number;
    sort_order: number;
    is_active: boolean;
    created_at: string | null;
};

export type Post = {
    id: number;
    post_category_id: number | null;
    category_name?: Translated;
    author_name?: string | null;
    title: Translated;
    slug: Translated;
    excerpt: Translated;
    content: Translated;
    meta_title: Translated;
    meta_description: Translated;
    cover_url: string | null;
    status: 'draft' | 'scheduled' | 'published' | 'archived';
    status_label: string;
    /** Token warna badge dari enum PHP. */
    status_color: string;
    published_at: string | null;
    published_at_display: string | null;
    view_count: number;
    reading_minutes: number | null;
    /** Nama tag; hanya dikirim saat relasinya dimuat. */
    tags?: string[];
    created_at: string | null;
};

export type RfqNote = {
    id: number;
    note: string;
    user_name: string | null;
    created_at: string;
};

export type RfqRequest = {
    id: number;
    reference_no: string;
    company_name: string;
    contact_name: string | null;
    email: string;
    phone: string | null;
    whatsapp: string | null;
    /** URL wa.me siap klik, null bila nomornya kosong. */
    whatsapp_link: string | null;
    country_code: string;
    incoterms: string | null;
    port_of_destination: string | null;
    monthly_tonnage: string | null;
    packaging_notes: string | null;
    message: string | null;
    status: string;
    status_label: string;
    status_color: string;
    is_read: boolean;
    locale: string;
    source_url: string | null;
    ip_address: string | null;
    product_name?: string | null;
    container_code?: string | null;
    notes?: RfqNote[];
    created_at: string;
    created_at_date: string | null;
};

export type ContactMessage = {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    subject: string | null;
    message: string;
    is_read: boolean;
    is_spam: boolean;
    locale: string;
    ip_address: string | null;
    created_at: string;
};

export type ActivityLogEntry = {
    id: number;
    log_name: string | null;
    event: string | null;
    description: string;
    subject_type: string | null;
    subject_label: string | null;
    subject_id: number | null;
    causer_name: string;
    /** Nilai lama vs baru per kolom yang berubah. */
    changes: {
        old: Record<string, unknown>;
        attributes: Record<string, unknown>;
    };
    created_at: string;
};

export type AdminUser = {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    avatar_url: string | null;
    is_active: boolean;
    two_factor_enabled: boolean;
    email_verified: boolean;
    last_login_at: string | null;
    last_login_ip: string | null;
    created_at: string | null;
};

export type ThemeContrastRow = {
    background: string;
    foreground: string;
    ratio: number;
    minimum: number;
    /** True bila pasangan ini menolak simpan saat gagal. */
    blocking: boolean;
    passes: boolean;
};

export type Theme = {
    id: number;
    name: string;
    slug: string;
    scope: 'admin' | 'public' | 'both';
    tokens: Record<string, string>;
    is_preset: boolean;
    is_active_admin: boolean;
    is_active_public: boolean;
    /** Enam warna untuk pratinjau palet di daftar. */
    swatches: string[];
    /** Hanya dikirim di halaman editor. */
    contrast?: ThemeContrastRow[];
    created_at: string | null;
};

export type ProductCategory = {
    id: number;
    name: Translated;
    slug: Translated;
    description: Translated;
    icon: string | null;
    sort_order: number;
    is_active: boolean;
    completeness: Record<string, TranslationStatus>;
    created_at: string | null;
};
