<?php

declare(strict_types=1);

namespace App\Support\Demo;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

/**
 * Mengambil berkas contoh untuk seeder demo.
 *
 * Foto diambil lewat API Openverse dan dibatasi lisensi yang boleh dipakai
 * komersial, jadi hasil unduhannya boleh ikut masuk repo. Hasil unduhan disimpan
 * di `storage/app/demo-assets/` supaya seeder bisa dijalankan ulang tanpa
 * memukul API berkali-kali, lalu disalin ke disk publik.
 *
 * Foto orang **tidak** diambil dari internet: memakai potret orang sungguhan
 * sebagai staf atau perwakilan fiktif adalah penggambaran yang keliru. Untuk itu
 * dipakai avatar inisial yang jelas bukan foto seseorang.
 */
final class DemoAssetFetcher
{
    /** Wikimedia menolak permintaan tanpa User-Agent yang bisa dihubungi. */
    private const USER_AGENT = 'NusacarbonDemoSeeder/1.0 (local development seeding)';

    private const CACHE_DIR = 'demo-assets';

    /**
     * Kata kunci pencarian Commons per topik.
     *
     * @var array<string, array<int, string>>
     */
    private const TOPICS = [
        'charcoal' => ['charcoal briquette', 'coconut shell charcoal', 'binchotan charcoal', 'charcoal lump'],
        'shisha' => ['hookah coal', 'shisha charcoal', 'hookah'],
        'barbecue' => ['barbecue charcoal', 'charcoal grill', 'grilling meat'],
        'coconut' => ['coconut shell', 'coconut husk', 'copra'],
        'factory' => ['charcoal kiln', 'charcoal production', 'factory worker indonesia', 'briquette machine'],
        'laboratory' => ['laboratory analysis', 'laboratory balance', 'moisture analyzer'],
        'packaging' => ['cardboard box packaging', 'carton box', 'shrink wrap pallet'],
        'logistics' => ['container terminal', 'container ship', 'cargo port indonesia', 'shipping container'],
        'warehouse' => ['warehouse pallet', 'forklift warehouse', 'logistics warehouse'],
        'document' => ['certificate document', 'stamped document', 'official document'],
    ];

    /**
     * Berkas yang sudah diunduh per topik.
     *
     * @var array<string, array<int, string>>
     */
    private array $pools = [];

    public function __construct(private readonly bool $offline = false) {}

    /**
     * Menyalin satu foto bertopik `$topic` ke disk publik.
     *
     * `$index` memutar isi kolam, jadi dua record berurutan tidak memakai foto
     * yang sama selama kolamnya cukup.
     */
    public function image(string $topic, int $index, string $directory, string $basename): ?string
    {
        $pool = $this->pool($topic);

        if ($pool === []) {
            return null;
        }

        $source = $pool[$index % count($pool)];
        $extension = pathinfo($source, PATHINFO_EXTENSION) ?: 'jpg';
        $path = trim($directory, '/').'/'.Str::slug($basename).'-'.($index + 1).'.'.$extension;

        $contents = @file_get_contents($source);

        if ($contents === false) {
            return null;
        }

        Storage::disk('public')->put($path, $contents);

        return $path;
    }

    /**
     * Potret ilustrasi untuk staf dan perwakilan.
     *
     * Wajahnya digambar DiceBear dari nama sebagai benih, jadi tiap orang punya
     * potret tetap tanpa memakai foto orang sungguhan sebagai pegawai fiktif —
     * penting di halaman verifikasi perwakilan yang justru dipakai buyer untuk
     * mengecek keaslian orang. Bila jaringan mati, jatuh ke kotak inisial.
     */
    public function portrait(string $name, string $directory, string $basename): string
    {
        $path = trim($directory, '/').'/'.Str::slug($basename).'.svg';
        $cache = storage_path('app/'.self::CACHE_DIR.'/portraits');

        if (! is_dir($cache)) {
            mkdir($cache, 0755, true);
        }

        $cached = $cache.'/'.Str::slug($name).'.svg';
        $svg = is_file($cached) ? (string) file_get_contents($cached) : null;

        if ($svg === null && ! $this->offline) {
            $svg = $this->fetchPortrait($name);

            if ($svg !== null) {
                file_put_contents($cached, $svg);
            }
        }

        Storage::disk('public')->put($path, $svg ?? $this->initialsSvg($name));

        return $path;
    }

    /**
     * Avatar inisial. Dibuat lokal, dipakai sebagai cadangan potret.
     */
    public function avatar(string $name, string $directory, string $basename): string
    {
        $path = trim($directory, '/').'/'.Str::slug($basename).'.svg';

        Storage::disk('public')->put($path, $this->initialsSvg($name));

        return $path;
    }

    private function fetchPortrait(string $name): ?string
    {
        try {
            $response = Http::withHeaders(['User-Agent' => self::USER_AGENT])
                ->timeout(30)
                ->retry(2, 800)
                ->get('https://api.dicebear.com/9.x/personas/svg', [
                    'seed' => $name,
                    // Latar netral mengikuti warna permukaan admin, bukan pastel
                    // bawaan yang bentrok dengan tema.
                    'backgroundColor' => 'ecebe7,f4f3f0,e6e3dd',
                    'backgroundType' => 'solid',
                    // Palet bawaan memuat rambut merah muda dan koral; dibatasi
                    // ke warna alami supaya halaman tim terbaca sebagai profil
                    // perusahaan, bukan avatar main-main.
                    'hairColor' => '2c2320,4a3728,6c4545,8d6748,b58143,9a9a9a',
                    'clothingColor' => '2f4b7c,456dff,4b5563,0f766e,6dbb58,7555ca',
                ]);
        } catch (Throwable) {
            return null;
        }

        $body = $response->body();

        return $response->successful() && str_contains($body, '<svg') ? $body : null;
    }

    /**
     * Logo wordmark buatan sendiri; dipakai sebagai cadangan.
     */
    public function logo(string $name, string $directory, string $basename): string
    {
        $path = trim($directory, '/').'/'.Str::slug($basename).'.svg';

        Storage::disk('public')->put($path, $this->initialsSvg($name, wide: true));

        return $path;
    }

    /**
     * Logo resmi perusahaan dari Wikimedia Commons.
     *
     * Hanya berkas berstatus **public domain** yang diambil — logo pelayaran
     * umumnya masuk kategori itu karena hanya berupa teks dan bentuk sederhana.
     * Bila tidak ketemu, jatuh ke wordmark inisial supaya barisnya tetap terisi.
     */
    public function brandLogo(string $name, string $query, string $directory, string $basename): string
    {
        $cache = storage_path('app/'.self::CACHE_DIR.'/logos');

        if (! is_dir($cache)) {
            mkdir($cache, 0755, true);
        }

        $existing = glob($cache.'/'.Str::slug($name).'.*') ?: [];
        $source = $existing[0] ?? ($this->offline ? null : $this->downloadLogo($name, $query, $cache));

        if ($source === null) {
            return $this->logo($name, $directory, $basename);
        }

        $extension = pathinfo($source, PATHINFO_EXTENSION);
        $path = trim($directory, '/').'/'.Str::slug($basename).'.'.$extension;

        Storage::disk('public')->put($path, (string) file_get_contents($source));

        return $path;
    }

    private function downloadLogo(string $name, string $query, string $cache): ?string
    {
        try {
            $response = Http::withHeaders(['User-Agent' => self::USER_AGENT])
                ->timeout(30)
                ->retry(2, 1_000)
                ->get('https://commons.wikimedia.org/w/api.php', [
                    'action' => 'query',
                    'format' => 'json',
                    'generator' => 'search',
                    'gsrsearch' => $query,
                    'gsrnamespace' => 6,
                    'gsrlimit' => 8,
                    'prop' => 'imageinfo',
                    'iiprop' => 'url|mime|extmetadata',
                ]);
        } catch (Throwable) {
            return null;
        }

        /** @var array<string, array{title?: string, imageinfo?: array<int, array<string, mixed>>}> $pages */
        $pages = $response->json('query.pages') ?? [];

        foreach ($pages as $page) {
            $title = (string) ($page['title'] ?? '');
            $info = $page['imageinfo'][0] ?? null;

            if ($info === null || ! $this->isUsableLogo($title, $info)) {
                continue;
            }

            $url = (string) $info['url'];
            $extension = $info['mime'] === 'image/svg+xml' ? 'svg' : 'png';
            $binary = $this->fetchBinary($url);

            if ($binary === null || strlen($binary) < 200) {
                continue;
            }

            $target = $cache.'/'.Str::slug($name).'.'.$extension;
            file_put_contents($target, $binary);

            return $target;
        }

        return null;
    }

    /**
     * Menolak berkas yang bukan logo utama perusahaan: bendera kapal, logo anak
     * usaha, dan berkas berlisensi selain public domain.
     *
     * @param  array<string, mixed>  $info
     */
    private function isUsableLogo(string $title, array $info): bool
    {
        $mime = (string) ($info['mime'] ?? '');

        if (! in_array($mime, ['image/svg+xml', 'image/png'], true)) {
            return false;
        }

        /** @var array<string, array{value?: string}> $meta */
        $meta = $info['extmetadata'] ?? [];
        $license = Str::lower((string) ($meta['LicenseShortName']['value'] ?? ''));

        if (! str_contains($license, 'public domain') && ! str_contains($license, 'cc0')) {
            return false;
        }

        $haystack = Str::lower($title);

        if (! str_contains($haystack, 'logo')) {
            return false;
        }

        foreach (['flag', 'cruise', 'drilling', 'tanker', 'terminal', 'airline', 'bank', 'stadium'] as $unwanted) {
            if (str_contains($haystack, $unwanted)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Mengisi cache seluruh topik dan melaporkan jumlah berkas per topik.
     *
     * @return array<string, int>
     */
    public function warm(): array
    {
        $counts = [];

        foreach (array_keys(self::TOPICS) as $topic) {
            $counts[$topic] = count($this->pool($topic));
        }

        return $counts;
    }

    /**
     * Daftar berkas lokal untuk satu topik; mengunduh bila belum ada.
     *
     * @return array<int, string>
     */
    private function pool(string $topic): array
    {
        if (isset($this->pools[$topic])) {
            return $this->pools[$topic];
        }

        $directory = storage_path('app/'.self::CACHE_DIR.'/'.$topic);

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $cached = glob($directory.'/*.{jpg,jpeg,png}', GLOB_BRACE) ?: [];

        if ($cached === [] && ! $this->offline) {
            $cached = $this->download($topic, $directory);
        }

        sort($cached);

        return $this->pools[$topic] = $cached;
    }

    /**
     * @return array<int, string>
     */
    private function download(string $topic, string $directory): array
    {
        $saved = [];

        foreach (self::TOPICS[$topic] ?? [] as $query) {
            try {
                $response = Http::withHeaders(['User-Agent' => self::USER_AGENT])
                    ->timeout(30)
                    ->retry(2, 1_000)
                    ->get('https://api.openverse.org/v1/images/', [
                        'q' => $query,
                        'page_size' => 10,
                        // Hanya lisensi yang boleh dipakai komersial, karena
                        // hasilnya ikut masuk repo dan dipakai saat demo.
                        'license_type' => 'commercial',
                        'mature' => 'false',
                    ]);
            } catch (Throwable) {
                continue;
            }

            /** @var array<int, array{url?: string, filetype?: string}> $results */
            $results = $response->json('results') ?? [];

            foreach ($results as $result) {
                $url = $result['url'] ?? null;
                $filetype = $result['filetype'] ?? 'jpg';

                if ($url === null || ! in_array($filetype, ['jpg', 'jpeg', 'png'], true)) {
                    continue;
                }

                $extension = $filetype === 'png' ? 'png' : 'jpg';
                $target = $directory.'/'.substr(md5($url), 0, 12).'.'.$extension;

                if (file_exists($target)) {
                    $saved[] = $target;

                    continue;
                }

                $binary = $this->fetchBinary($url);

                // Berkas mungil biasanya halaman error, bukan foto.
                if ($binary === null || strlen($binary) < 8_000) {
                    continue;
                }

                file_put_contents($target, $binary);
                $saved[] = $target;
            }
        }

        return $saved;
    }

    /**
     * Wikimedia membalas 429 bila unduhan beruntun terlalu rapat, jadi tiap
     * permintaan diberi jeda dan percobaan ulang yang makin lama.
     */
    private function fetchBinary(string $url): ?string
    {
        for ($attempt = 1; $attempt <= 3; $attempt++) {
            usleep(400_000);

            try {
                $response = Http::withHeaders(['User-Agent' => self::USER_AGENT])
                    ->timeout(60)
                    ->get($url);
            } catch (Throwable) {
                continue;
            }

            if ($response->status() === 429) {
                usleep($attempt * 3_000_000);

                continue;
            }

            if ($response->successful()) {
                return $response->body();
            }

            return null;
        }

        return null;
    }

    /**
     * Kotak inisial dengan warna yang stabil per nama.
     */
    private function initialsSvg(string $name, bool $wide = false): string
    {
        $initials = Str::of($name)
            ->explode(' ')
            ->filter()
            ->take(2)
            ->map(fn (string $word): string => Str::upper(Str::substr($word, 0, 1)))
            ->implode('');

        $palette = ['#1D4ED8', '#B45309', '#15803D', '#7C3AED', '#0F766E', '#B91C1C', '#C2410C'];
        $background = $palette[crc32($name) % count($palette)];

        $width = $wide ? 480 : 320;
        $height = 320;

        return <<<SVG
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {$width} {$height}" width="{$width}" height="{$height}" role="img" aria-label="{$name}">
                <rect width="{$width}" height="{$height}" fill="{$background}"/>
                <text x="50%" y="50%" dy="0.36em" text-anchor="middle" fill="#FFFFFF"
                      font-family="Helvetica, Arial, sans-serif" font-size="128" font-weight="600">{$initials}</text>
            </svg>
            SVG;
    }
}
