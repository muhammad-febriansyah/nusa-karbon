<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Certificate;
use App\Models\Feature;
use App\Models\Gallery;
use App\Models\PackagingOption;
use App\Models\PageBlock;
use App\Models\Post;
use App\Models\ProductionStep;
use App\Models\QcProtocol;
use App\Models\Representative;
use App\Models\ShippingPartner;
use App\Models\TeamMember;
use App\Support\Demo\DemoAssetFetcher;
use App\Support\Demo\DemoPdf;
use App\Support\Locale;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Mengisi gambar pada baris yang belum punya.
 *
 * Seeder demo lain hanya mengurus baris yang mereka buat sendiri, sementara
 * seeder bawaan proyek sudah menulis sebagian data tanpa gambar. Tanpa langkah
 * ini daftar admin memperlihatkan campuran baris bergambar dan kotak kosong.
 */
final class DemoMediaBackfillSeeder extends Seeder
{
    /**
     * Model => [kolom gambar, topik foto, direktori tujuan].
     *
     * @var array<class-string<Model>, array{0: string, 1: string, 2: string}>
     */
    private const IMAGE_COLUMNS = [
        ProductionStep::class => ['image_path', 'factory', 'production-steps'],
        QcProtocol::class => ['image_path', 'laboratory', 'qc-protocols'],
        Feature::class => ['image_path', 'charcoal', 'features'],
        PackagingOption::class => ['image_path', 'packaging', 'packaging-options'],
        Gallery::class => ['cover_path', 'warehouse', 'galleries'],
        Post::class => ['cover_path', 'charcoal', 'posts'],
        PageBlock::class => ['image_path', 'factory', 'page-blocks'],
    ];

    public function __construct(
        private readonly DemoAssetFetcher $assets = new DemoAssetFetcher,
        private readonly DemoPdf $pdf = new DemoPdf,
    ) {}

    public function run(): void
    {
        foreach (self::IMAGE_COLUMNS as $model => [$column, $topic, $directory]) {
            $this->fillImages($model, $column, $topic, $directory);
        }

        $this->fillAvatars(Representative::class, 'photo_path', 'representatives');
        $this->fillAvatars(TeamMember::class, 'photo_path', 'team');
        $this->fillLogos();
        $this->fillCertificateFiles();
    }

    /**
     * @param  class-string<Model>  $model
     */
    private function fillImages(string $model, string $column, string $topic, string $directory): void
    {
        $model::query()->whereNull($column)->get()->each(
            function (Model $record, int $index) use ($column, $topic, $directory): void {
                $path = $this->assets->image($topic, $index + 3, $directory, 'demo-'.$record->getKey());

                if ($path !== null) {
                    $record->forceFill([$column => $path])->save();
                }
            },
        );
    }

    /**
     * @param  class-string<Representative|TeamMember>  $model
     */
    private function fillAvatars(string $model, string $column, string $directory): void
    {
        $model::query()->whereNull($column)->get()->each(
            function (Representative|TeamMember $record) use ($column, $directory): void {
                $record->forceFill([
                    $column => $this->assets->portrait($record->name, $directory, 'demo-'.Str::slug($record->name)),
                ])->save();
            },
        );
    }

    private function fillLogos(): void
    {
        ShippingPartner::query()->whereNull('logo_path')->get()->each(
            function (ShippingPartner $partner): void {
                $partner->forceFill([
                    'logo_path' => $this->assets->logo($partner->name, 'shipping-partners', 'demo-'.Str::slug($partner->name)),
                ])->save();
            },
        );

        Certificate::query()->whereNull('logo_path')->get()->each(
            function (Certificate $certificate): void {
                $issuer = $certificate->issuer ?? $certificate->getTranslation('name', Locale::default());

                $certificate->forceFill([
                    'logo_path' => $this->assets->logo($issuer, 'certificates', 'demo-logo-'.$certificate->id),
                ])->save();
            },
        );
    }

    private function fillCertificateFiles(): void
    {
        $locale = Locale::default();

        Certificate::query()->whereNull('file_path')->get()->each(
            function (Certificate $certificate) use ($locale): void {
                $name = $certificate->getTranslation('name', $locale);
                $path = 'certificates/demo-'.$certificate->id.'-'.Str::slug($name).'.pdf';

                $this->pdf->write(
                    $path,
                    Str::upper($name),
                    'Diterbitkan oleh '.($certificate->issuer ?? 'lembaga penerbit'),
                    [
                        ['Certificate Number', $certificate->certificate_number ?? '-'],
                        ['Holder', 'PT Nusa Carbon Indonesia'],
                        ['Scope', 'Coconut shell charcoal briquette manufacturing'],
                        ['Issued On', $certificate->issued_at?->format('d F Y') ?? '-'],
                        ['Valid Until', $certificate->expires_at?->format('d F Y') ?? '-'],
                    ],
                );

                $certificate->forceFill(['file_path' => $path])->save();
            },
        );
    }
}
