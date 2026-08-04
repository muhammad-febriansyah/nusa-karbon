<?php

namespace App\Http\Middleware;

use App\Models\ContactMessage;
use App\Models\RfqRequest;
use App\Models\Theme;
use App\Services\SettingService;
use App\Services\ThemeService;
use App\Support\Locale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'brand' => $this->brand(),
            'auth' => [
                'user' => $request->user(),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'unread' => $this->unreadCounts(),
            'theme' => [
                'css' => $this->themeCss($request),
            ],
            'locale' => $this->locale(),
            'settings' => $this->isAdminRequest($request)
                ? []
                : app(SettingService::class)->publicSettings(),
            'seo' => $this->isAdminRequest($request)
                ? null
                : [
                    /*
                     * Diambil dari request, bukan disusun di frontend, supaya
                     * skema dan host-nya benar di belakang proxy — nilai
                     * inilah yang jadi canonical dan basis hreflang.
                     */
                    'canonical' => $request->url(),
                    'baseUrl' => rtrim((string) url('/'), '/'),
                    'path' => '/'.ltrim($request->path() === '/' ? '' : $request->path(), '/'),
                ],
        ];
    }

    private function isAdminRequest(Request $request): bool
    {
        return $request->is('admin', 'admin/*');
    }

    /**
     * Locale aktif beserta daftar bahasa yang tersedia.
     *
     * Daftarnya dibaca dari `config/locales.php`, bukan ditanam di frontend,
     * sehingga pemilih bahasa ikut tumbuh sendiri begitu Fase 6 memindahkan
     * entri dari `planned` ke `supported`.
     *
     * @return array{
     *     current: string,
     *     direction: string,
     *     supported: array<int, array{
     *         code: string, name: string, native: string, dir: string,
     *         prefix: string, flag: string
     *     }>,
     *     planned: array<int, array{
     *         code: string, name: string, native: string, dir: string, flag: string
     *     }>
     * }
     */
    private function locale(): array
    {
        $current = app()->getLocale();

        $supported = array_values(array_filter(array_map(
            function (string $code): ?array {
                $meta = Locale::meta($code);

                if ($meta === null) {
                    return null;
                }

                return [
                    'code' => $code,
                    'name' => $meta['name'],
                    'native' => $meta['native'],
                    'dir' => $meta['dir'],
                    'prefix' => Locale::urlPrefix($code),
                    // Kode negara bendera, dipakai pemilih bahasa di navbar.
                    'flag' => $meta['flag'],
                ];
            },
            Locale::supported(),
        )));

        /*
         * Bahasa yang direncanakan ikut dikirim supaya pemilih bahasa bisa
         * menampilkannya sebagai pilihan nonaktif. Sengaja terpisah dari
         * `supported` — apa pun yang masuk daftar dukungan akan ikut dianggap
         * bisa dituju, dan rutenya belum ada.
         */
        $planned = array_map(
            static fn (string $code, array $meta): array => [
                'code' => $code,
                'name' => $meta['name'],
                'native' => $meta['native'],
                'dir' => $meta['dir'],
                'flag' => $meta['flag'],
            ],
            array_keys(Locale::planned()),
            array_values(Locale::planned()),
        );

        return [
            'current' => $current,
            'direction' => Locale::direction($current),
            'supported' => $supported,
            'planned' => $planned,
        ];
    }

    /**
     * Identitas merek dari `site_settings`, dipakai sidebar, halaman masuk, dan
     * tag favicon di `app.blade.php`.
     *
     * Nilainya berupa URL siap pakai — komponen tidak perlu tahu berkasnya
     * disimpan di disk mana.
     *
     * @return array{name: string, logo: string|null, favicon: string|null}
     */
    private function brand(): array
    {
        $settings = app(SettingService::class);

        return [
            'name' => (string) ($settings->get('company_name') ?: config('app.name')),
            'logo' => $this->fileUrl($settings->get('logo_primary')),
            'favicon' => $this->fileUrl($settings->get('favicon')),
        ];
    }

    private function fileUrl(mixed $path): ?string
    {
        return is_string($path) && $path !== ''
            ? Storage::disk('public')->url($path)
            : null;
    }

    /**
     * Blok CSS variable tema aktif. Dirender server ke `<head>` — menyuntiknya
     * setelah hidrasi menyebabkan kedipan warna default.
     *
     * @see docs/10-theming.md bagian 2
     */
    private function themeCss(Request $request): string
    {
        $themes = app(ThemeService::class);
        $scope = $this->isAdminRequest($request) ? Theme::SCOPE_ADMIN : Theme::SCOPE_PUBLIC;

        return $themes->toCssVariables($themes->tokensFor($scope));
    }

    /**
     * Jumlah badge sidebar untuk RFQ dan pesan kontak.
     *
     * Tamu tidak perlu dihitung — badge hanya tampil di panel admin, dan
     * menghitungnya di setiap request publik hanya menambah query sia-sia.
     *
     * @return array{rfq: int, contactMessages: int}
     */
    private function unreadCounts(): array
    {
        if (! Auth::check()) {
            return ['rfq' => 0, 'contactMessages' => 0];
        }

        return [
            'rfq' => RfqRequest::query()->unread()->count(),
            'contactMessages' => ContactMessage::query()->unread()->count(),
        ];
    }
}
