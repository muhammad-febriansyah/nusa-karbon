<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\SettingType;
use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Services\SettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Halaman pengaturan situs. Field-nya dirender dari kolom `type` tiap baris,
 * jadi menambah pengaturan baru cukup lewat seeder.
 *
 * @see docs/09-tasks.md bagian 4.3
 */
final class SiteSettingController extends Controller
{
    /**
     * Judul grup seperti yang dibaca admin.
     */
    private const GROUP_LABELS = [
        'identity' => 'Identitas',
        'contact' => 'Kontak',
        'social' => 'Media Sosial',
        'seo' => 'SEO',
        'email' => 'Email',
        'system' => 'Sistem',
    ];

    public function __construct(private readonly SettingService $settings) {}

    public function index(): Response
    {
        $grouped = $this->settings->grouped();

        // Urutan tab mengikuti daftar grup, bukan urutan abjad dari query —
        // identitas perusahaan yang paling sering diubah harus di depan.
        $ordered = array_values(array_filter(
            array_keys(self::GROUP_LABELS),
            fn (string $key): bool => isset($grouped[$key]),
        ));

        return Inertia::render('admin/site-settings/index', [
            'groups' => array_map(
                fn (string $key): array => [
                    'key' => $key,
                    'label' => self::GROUP_LABELS[$key],
                    'settings' => $grouped[$key] ?? [],
                ],
                $ordered,
            ),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        /** @var array<string, SiteSetting> $settings */
        $settings = SiteSetting::query()->get()->keyBy('key')->all();

        $rules = [];

        foreach ($settings as $key => $setting) {
            $extra = $setting->rules ?? [];

            $rules["values.{$key}"] = array_merge($setting->type->baseRules(), $extra);

            if ($setting->type === SettingType::EmailList) {
                $rules["values.{$key}"][] = function (string $attribute, mixed $value, callable $fail): void {
                    if (! is_string($value) || trim($value) === '') {
                        return;
                    }

                    foreach (explode(',', $value) as $email) {
                        if (! filter_var(trim($email), FILTER_VALIDATE_EMAIL)) {
                            $fail('Daftar email berisi alamat yang tidak valid: '.trim($email).'.');

                            return;
                        }
                    }
                };
            }
        }

        /** @var array<string, mixed> $values */
        $values = $request->validate($rules)['values'] ?? [];

        $this->settings->setMany($values);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Pengaturan situs berhasil disimpan.',
        ]);

        return back();
    }
}
