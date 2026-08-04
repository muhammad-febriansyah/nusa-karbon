<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Feature;
use App\Models\ProductionStep;
use App\Models\QcProtocol;
use App\Models\ShippingPartner;
use App\Models\TeamMember;
use App\Support\Locale;
use Illuminate\Database\Seeder;

/**
 * Konten perusahaan awal: proses produksi, protokol QC, keunggulan, mitra
 * pelayaran, dan tim.
 *
 * Gambar dan logo diunggah admin belakangan. Nama tim dan mitra masih data
 * sementara sampai klien mengirim daftar resminya.
 *
 * @see docs/09-tasks.md bagian 3.3
 */
final class CompanyContentSeeder extends Seeder
{
    public function run(): void
    {
        $locale = Locale::default();

        $this->seedProductionSteps($locale);
        $this->seedQcProtocols($locale);
        $this->seedFeatures($locale);
        $this->seedShippingPartners();
        $this->seedTeamMembers($locale);
    }

    private function seedProductionSteps(string $locale): void
    {
        $steps = [
            ['Seleksi Batok Kelapa', 'Batok tua kering disortir manual, batok muda dan berjamur dibuang.'],
            ['Karbonisasi', 'Batok dibakar dalam tungku tertutup sampai menjadi arang dengan kadar karbon tinggi.'],
            ['Penghalusan & Pencampuran', 'Arang digiling halus lalu dicampur perekat alami tanpa bahan kimia.'],
            ['Pencetakan', 'Adonan dicetak sesuai bentuk pesanan: cube, hexagonal, atau finger.'],
            ['Pengeringan', 'Briket dikeringkan sampai kadar air di bawah 5% agar tidak berasap saat dibakar.'],
            ['Sortir & Pengemasan', 'Briket disortir per ukuran, ditimbang, lalu dikemas ke inner box dan master carton.'],
        ];

        foreach ($steps as $position => [$title, $description]) {
            ProductionStep::query()->firstOrCreate(
                ["title->{$locale}" => $title],
                [
                    'title' => [$locale => $title],
                    'description' => [$locale => $description],
                    'sort_order' => $position,
                    'is_active' => true,
                ],
            );
        }
    }

    private function seedQcProtocols(string $locale): void
    {
        $protocols = [
            ['Drop Test', 'Briket dijatuhkan dari ketinggian standar untuk mengukur ketahanan bentuk.', 'Breakage tolerance', '< 2.5%'],
            ['Uji Kadar Air', 'Sampel tiap batch diuji kadar airnya sebelum dikemas.', 'Moisture content', '< 5%'],
            ['Uji Kadar Abu', 'Sisa abu setelah pembakaran sempurna diukur di laboratorium.', 'Ash content', '< 2.5%'],
            ['Uji Waktu Bakar', 'Durasi bakar diukur pada kondisi ruang terkendali.', 'Burning time', '> 120 menit'],
            ['Uji Nilai Kalor', 'Nilai kalor diuji untuk memastikan panas yang dihasilkan konsisten.', 'Calorific value', '> 7000 kcal/kg'],
        ];

        foreach ($protocols as $position => [$title, $description, $label, $value]) {
            QcProtocol::query()->firstOrCreate(
                ["title->{$locale}" => $title],
                [
                    'title' => [$locale => $title],
                    'description' => [$locale => $description],
                    'result_label' => [$locale => $label],
                    'result_value' => $value,
                    'sort_order' => $position,
                    'is_active' => true,
                ],
            );
        }
    }

    private function seedFeatures(string $locale): void
    {
        $features = [
            ['Kualitas Konsisten', 'Setiap batch melewati protokol QC yang sama, bukan sekadar sampel acak.', 'shield-check'],
            ['Dokumen Ekspor Lengkap', 'COA, MSDS, dan dokumen karantina disiapkan sejak awal, bukan setelah diminta.', 'file-check'],
            ['Kemasan Sesuai Buyer', 'Merek, ukuran inner box, dan desain karton mengikuti permintaan.', 'package'],
            ['Jadwal Kirim Terjaga', 'Slot kontainer dipesan lebih awal bersama mitra pelayaran tetap.', 'ship'],
        ];

        foreach ($features as $position => [$title, $description, $icon]) {
            Feature::query()->firstOrCreate(
                ["title->{$locale}" => $title],
                [
                    'title' => [$locale => $title],
                    'description' => [$locale => $description],
                    'icon' => $icon,
                    'sort_order' => $position,
                    'is_active' => true,
                ],
            );
        }
    }

    private function seedShippingPartners(): void
    {
        $partners = [
            ['Maersk', 'https://www.maersk.com'],
            ['CMA CGM', 'https://www.cma-cgm.com'],
            ['Evergreen Line', 'https://www.evergreen-line.com'],
            ['ONE', 'https://www.one-line.com'],
        ];

        foreach ($partners as $position => [$name, $url]) {
            ShippingPartner::query()->firstOrCreate(
                ['name' => $name],
                [
                    'website_url' => $url,
                    'sort_order' => $position,
                    'is_active' => true,
                ],
            );
        }
    }

    private function seedTeamMembers(string $locale): void
    {
        $members = [
            ['Adi Wicaksono', 'Export Sales Manager'],
            ['Rina Kusuma', 'Business Development'],
            ['Bayu Pratama', 'Quality Control Lead'],
        ];

        foreach ($members as $position => [$name, $jobTitle]) {
            TeamMember::query()->firstOrCreate(
                ['name' => $name],
                [
                    'position' => [$locale => $jobTitle],
                    'sort_order' => $position,
                    'is_active' => true,
                ],
            );
        }
    }
}
