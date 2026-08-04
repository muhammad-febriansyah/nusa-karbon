import {
    Anchor,
    Award,
    BadgeCheck,
    Banknote,
    Boxes,
    Calculator,
    Check,
    CircleCheck,
    ClipboardCheck,
    Clock,
    Coins,
    Container,
    Droplets,
    Factory,
    FileCheck,
    FileText,
    Flame,
    Gauge,
    Globe,
    Handshake,
    Headset,
    Layers,
    Leaf,
    Lock,
    Microscope,
    Package,
    Recycle,
    Repeat,
    Ruler,
    Scale,
    ShieldCheck,
    Ship,
    Signature,
    Sparkles,
    Sprout,
    Star,
    Target,
    Thermometer,
    Timer,
    TrendingUp,
    Truck,
    Users,
    Warehouse,
    Zap,
} from 'lucide-react';
import type { LucideIcon } from 'lucide-react';

/**
 * Ikon yang boleh dipakai kolom `icon` pada Keunggulan.
 *
 * Dipetakan statis, bukan lewat `DynamicIcon` lucide. `DynamicIcon` memang bisa
 * merender nama ikon apa pun, tapi caranya dengan satu permintaan jaringan per
 * ikon saat komponen dirender — pada beranda yang memuat enam keunggulan itu
 * enam permintaan tambahan demi gambar sebesar 20 piksel.
 *
 * Daftar ini sengaja terbatas pada ikon yang masuk akal untuk ekspor briket.
 * Pemilih ikon visual yang direncanakan di `docs/09-tasks.md` bagian 3.3
 * sebaiknya menawarkan daftar yang sama persis, supaya admin tidak bisa memilih
 * nama yang tidak ada di sini.
 */
const FEATURE_ICONS: Record<string, LucideIcon> = {
    anchor: Anchor,
    award: Award,
    'badge-check': BadgeCheck,
    banknote: Banknote,
    boxes: Boxes,
    calculator: Calculator,
    check: Check,
    'circle-check': CircleCheck,
    'clipboard-check': ClipboardCheck,
    clock: Clock,
    coins: Coins,
    container: Container,
    droplets: Droplets,
    factory: Factory,
    'file-check': FileCheck,
    'file-text': FileText,
    flame: Flame,
    gauge: Gauge,
    globe: Globe,
    handshake: Handshake,
    headset: Headset,
    layers: Layers,
    leaf: Leaf,
    lock: Lock,
    microscope: Microscope,
    package: Package,
    recycle: Recycle,
    repeat: Repeat,
    ruler: Ruler,
    scale: Scale,
    'shield-check': ShieldCheck,
    ship: Ship,
    signature: Signature,
    sparkles: Sparkles,
    sprout: Sprout,
    star: Star,
    target: Target,
    thermometer: Thermometer,
    timer: Timer,
    'trending-up': TrendingUp,
    truck: Truck,
    users: Users,
    warehouse: Warehouse,
    zap: Zap,
};

/**
 * Merender ikon keunggulan berdasarkan namanya.
 *
 * Nama yang tidak dikenal jatuh ke `Sparkles` — kolomnya divalidasi formatnya
 * saja, jadi admin tetap bisa mengetik nama lucide di luar daftar ini.
 */
export default function FeatureIcon({
    name,
    className,
}: {
    name: string | null;
    className?: string;
}) {
    const Icon = (name === null ? null : FEATURE_ICONS[name]) ?? Sparkles;

    return <Icon className={className} aria-hidden />;
}
