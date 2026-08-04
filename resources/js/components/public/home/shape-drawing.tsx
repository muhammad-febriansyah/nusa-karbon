import { cn } from '@/lib/utils';

type Props = {
    /** Nilai kolom `shape` produk, apa adanya dari admin. */
    shape: string | null;
    /** Nilai kolom `size_mm` produk, misalnya "25 x 25 x 25". */
    sizeMm: string | null;
    className?: string;
};

/** Titik pada bidang gambar. */
type Point = { x: number; y: number };

/** Lebar benda pada gambar. Bukan skala nyata — hanya proporsi di layar. */
const SPAN = 92;

/** Tinggi sisi tegaknya. */
const DEPTH = 46;

const CENTER_X = 100;

/** Titik pusat bidang atas. */
const TOP_Y = 62;

/** Ketinggian garis ukuran. */
const DIMENSION_Y = 168;

/**
 * Perbandingan tinggi terhadap lebar pada proyeksi isometrik yang dipakai di
 * sini. Setengah berarti bidang atas terlihat sebagai belah ketupat dengan
 * tinggi separuh lebarnya — sudut isometrik baku 30°.
 */
const ISO_SQUASH = 0.5;

function translate(point: Point, dy: number): Point {
    return { x: point.x, y: point.y + dy };
}

function toPath(points: Point[]): string {
    return points.map((p) => `${p.x.toFixed(2)},${p.y.toFixed(2)}`).join(' ');
}

/**
 * Titik sudut bidang atas untuk tiap bentuk.
 *
 * Urutannya selalu berlawanan arah jarum jam dimulai dari titik paling kanan,
 * sehingga sisi tegak yang terlihat selalu berada di indeks yang sama dan
 * penggambarannya tidak perlu tahu bentuk apa yang sedang digambar.
 */
function topFaceOf(shape: string | null): Point[] {
    const radius = SPAN / 2;
    const normalised = shape?.toLowerCase();

    if (normalised === 'hexagonal') {
        return Array.from({ length: 6 }, (_, index) => {
            const angle = (Math.PI / 3) * index;

            return {
                x: CENTER_X + radius * Math.cos(angle),
                y: TOP_Y + radius * ISO_SQUASH * Math.sin(angle),
            };
        });
    }

    /*
     * Briket "Finger" jauh lebih panjang daripada lebarnya. Digambar sebagai
     * kubus, pembeli akan salah menduga bentuknya — jadi salah satu sisinya
     * dipersempit dan garis ukurannya ikut menyempit.
     */
    const halfWidth = normalised === 'finger' ? radius / 2 : radius;

    return [
        { x: CENTER_X + halfWidth, y: TOP_Y },
        { x: CENTER_X, y: TOP_Y + radius * ISO_SQUASH },
        { x: CENTER_X - halfWidth, y: TOP_Y },
        { x: CENTER_X, y: TOP_Y - radius * ISO_SQUASH },
    ];
}

/**
 * Briket sebagai gambar teknik isometrik.
 *
 * Versi sebelumnya menggambar tampak atas, dan tampak atas sebuah kubus adalah
 * persegi — tidak ada yang mengenalinya sebagai briket. Proyeksi isometrik
 * menunjukkan tiga sisi sekaligus, jadi bendanya langsung terbaca padat dan
 * bentuknya jelas tanpa keterangan.
 *
 * Foto produk tidak dipakai di sini karena tidak menjawab satu-satunya hal yang
 * ditanyakan pembeli lebih dulu: ukurannya. Kubus 22 mm dan 30 mm terlihat
 * sama persis di foto tanpa pembanding. Garis ukuran menjawab itu langsung,
 * dan bentuknya mengikuti kolom `shape` sehingga gambar ini tidak pernah
 * menampilkan bentuk yang berbeda dari produknya.
 */
export default function ShapeDrawing({ shape, sizeMm, className }: Props) {
    const top = topFaceOf(shape);
    const bottom = top.map((point) => translate(point, DEPTH));

    const left = Math.min(...top.map((p) => p.x));
    const right = Math.max(...top.map((p) => p.x));

    /*
     * Sisi tegak yang terlihat hanyalah yang menghadap ke depan — yaitu rusuk
     * yang salah satu ujungnya berada di bawah garis tengah bidang atas.
     */
    const faces = top
        .map((point, index) => {
            const next = top[(index + 1) % top.length];

            return { point, next, index };
        })
        .filter(({ point, next }) => point.y > TOP_Y || next.y > TOP_Y);

    const edge = sizeMm?.match(/\d+(?:[.,]\d+)?/)?.[0] ?? null;
    const label = edge === null ? null : `${edge} mm`;

    /*
     * Lebar potongan garis ukuran dihitung dari panjang teksnya. IBM Plex Mono
     * berlebar tetap, jadi 4 satuan per karakter cukup akurat pada ukuran huruf
     * 13 di dalam viewBox selebar 200 — dan karena mono, "8 mm" dan "30 mm"
     * tidak pernah meleset tak terduga.
     */
    const labelHalfWidth = label === null ? 0 : (label.length * 4) / 2 + 4;

    return (
        <svg
            viewBox="0 0 200 186"
            role="img"
            aria-label={
                label === null
                    ? `Gambar teknik briket bentuk ${shape ?? 'kubus'}`
                    : `Gambar teknik briket bentuk ${shape ?? 'kubus'}, lebar ${label}`
            }
            className={cn('h-auto w-full', className)}
        >
            {/*
             * Ketiga bidangnya memakai `currentColor` dengan kepekatan berbeda,
             * bukan warna tersendiri. Dengan begitu benda ini tetap terbaca
             * padat baik di atas kertas terang maupun di atas pita gelap —
             * yang berubah hanya warna tintanya, bukan susunan gelap-terangnya.
             */}
            <g className="fill-current">
                {faces.map(({ point, next, index }) => (
                    <polygon
                        key={index}
                        points={toPath([
                            point,
                            next,
                            translate(next, DEPTH),
                            translate(point, DEPTH),
                        ])}
                        opacity={0.08 + index * 0.04}
                    />
                ))}
                <polygon points={toPath(top)} opacity={0.22} />
            </g>

            {/* Rusuknya digambar terpisah agar ketebalannya seragam. */}
            <g
                className="stroke-current"
                strokeWidth={1}
                strokeLinejoin="round"
                fill="none"
                opacity={0.75}
            >
                <polygon points={toPath(top)} />
                {faces.map(({ point, next, index }) => (
                    <polygon
                        key={index}
                        points={toPath([
                            point,
                            next,
                            translate(next, DEPTH),
                            translate(point, DEPTH),
                        ])}
                    />
                ))}
            </g>

            {/*
             * Garis bantu dibuat jauh lebih samar daripada rusuk bendanya.
             * Bobot yang sama membuat keduanya terbaca sebagai satu kesatuan
             * bentuk; kontras inilah yang memberitahu mata mana benda dan mana
             * keterangan ukuran.
             */}
            <g className="stroke-current" strokeWidth={0.75} opacity={0.35}>
                <line
                    x1={left}
                    y1={Math.max(...bottom.map((p) => p.y)) + 12}
                    x2={left}
                    y2={DIMENSION_Y + 6}
                />
                <line
                    x1={right}
                    y1={Math.max(...bottom.map((p) => p.y)) + 12}
                    x2={right}
                    y2={DIMENSION_Y + 6}
                />
            </g>

            <g className="stroke-current" strokeWidth={0.75} opacity={0.8}>
                {/*
                 * Garisnya diputus di tengah dan angkanya duduk di dalam
                 * potongan itu, seperti pada gambar kerja. Angka yang mengambang
                 * di bawah garis terbaca sebagai keterangan gambar; angka yang
                 * duduk di garisnya terbaca sebagai ukuran garis itu sendiri.
                 */}
                <line
                    x1={left}
                    y1={DIMENSION_Y}
                    x2={CENTER_X - labelHalfWidth}
                    y2={DIMENSION_Y}
                />
                <line
                    x1={CENTER_X + labelHalfWidth}
                    y1={DIMENSION_Y}
                    x2={right}
                    y2={DIMENSION_Y}
                />
                {/*
                 * Ujung garis ukuran memakai garis miring 45°, bukan mata panah.
                 * Itu konvensi gambar teknik, dan pada ukuran sekecil ini jauh
                 * lebih terbaca daripada segitiga yang menggumpal jadi titik.
                 */}
                <line
                    x1={left - 4}
                    y1={DIMENSION_Y + 4}
                    x2={left + 4}
                    y2={DIMENSION_Y - 4}
                />
                <line
                    x1={right - 4}
                    y1={DIMENSION_Y + 4}
                    x2={right + 4}
                    y2={DIMENSION_Y - 4}
                />
            </g>

            {label && (
                <text
                    x={CENTER_X}
                    y={DIMENSION_Y + 4.5}
                    textAnchor="middle"
                    /*
                     * `dir` bukan atribut SVG; arah teks di dalam gambar diatur
                     * lewat properti CSS `direction`. Dikunci ke ltr supaya
                     * "25 mm" tidak terbalik jadi "mm 25" pada locale Arab.
                     */
                    className="fill-current font-mono text-[13px] tracking-tight opacity-70 [direction:ltr]"
                >
                    {label}
                </text>
            )}
        </svg>
    );
}
