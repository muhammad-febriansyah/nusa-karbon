<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Incoterm;
use App\Enums\RfqStatus;
use App\Models\ContainerType;
use App\Models\Product;
use App\Models\RfqRequest;
use App\Support\Locale;
use Illuminate\Database\Seeder;

/**
 * Permintaan penawaran contoh untuk mengisi dashboard saat pengembangan.
 *
 * **Bukan bagian dari `DatabaseSeeder`.** RFQ asli datang dari form publik;
 * seeder ini hanya dijalankan manual agar grafik dan corong status punya isi
 * saat panel ditinjau. Jangan pernah dijalankan di produksi.
 */
final class RfqDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->isProduction()) {
            $this->command->warn('RfqDemoSeeder dilewati: data contoh tidak boleh masuk produksi.');

            return;
        }

        $locale = Locale::default();
        $products = Product::query()->calculable()->pluck('id')->all();
        $containers = ContainerType::query()->pluck('id')->all();

        $companies = [
            ['Al Noor Trading LLC', 'AE', 'Dubai', RfqStatus::Won, 0],
            ['Gulf Charcoal Supply', 'AE', 'Jebel Ali', RfqStatus::Negotiating, 2],
            ['Riyadh Shisha House', 'SA', 'Dammam', RfqStatus::Quoted, 5],
            ['Doha Coals WLL', 'QA', 'Hamad Port', RfqStatus::Contacted, 8],
            ['Istanbul Kömür', 'TR', 'Mersin', RfqStatus::Won, 14],
            ['Berlin Grill Supply', 'DE', 'Hamburg', RfqStatus::Lost, 21],
            ['Rotterdam BBQ Import', 'NL', 'Rotterdam', RfqStatus::Quoted, 27],
            ['Moscow Charcoal Group', 'RU', 'Novorossiysk', RfqStatus::New, 33],
            ['Cairo Shisha Co', 'EG', 'Alexandria', RfqStatus::New, 40],
            ['Warsaw Coal Trade', 'PL', 'Gdansk', RfqStatus::Contacted, 52],
            ['Seoul Hookah Lounge', 'KR', 'Busan', RfqStatus::Negotiating, 63],
            ['Mumbai Coal Exports', 'IN', 'Nhava Sheva', RfqStatus::Lost, 75],
            ['Kuwait Premium Coals', 'KW', 'Shuwaikh', RfqStatus::Won, 96],
            ['Amman Trading House', 'JO', 'Aqaba', RfqStatus::Quoted, 118],
            ['London Shisha Imports', 'GB', 'Felixstowe', RfqStatus::New, 140],
            ['Cheap Coal Offer', 'CN', 'Ningbo', RfqStatus::Spam, 6],
        ];

        $tonnage = ['54 MT', '3 x 40HC', 'two containers monthly', '1 x 20FT trial', '18 ton/bulan'];

        foreach ($companies as $index => [$company, $country, $port, $status, $daysAgo]) {
            $createdAt = now()->subDays($daysAgo);
            $reference = 'RFQ-'.$createdAt->format('Ymd').'-'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT);

            RfqRequest::query()->firstOrCreate(
                ['reference_no' => $reference],
                [
                    'product_id' => $products === [] ? null : $products[$index % count($products)],
                    'container_type_id' => $containers === [] ? null : $containers[$index % count($containers)],
                    'company_name' => $company,
                    'contact_name' => 'Purchasing Manager',
                    'email' => 'purchasing@'.str($company)->slug().'.com',
                    'phone' => '+9715'.str_pad((string) ($index * 7919 % 10000000), 7, '0', STR_PAD_LEFT),
                    'whatsapp' => '+9715'.str_pad((string) ($index * 7919 % 10000000), 7, '0', STR_PAD_LEFT),
                    'country_code' => $country,
                    'incoterms' => $index % 3 === 0 ? Incoterm::Cif : Incoterm::Fob,
                    'port_of_destination' => $port,
                    'monthly_tonnage' => $tonnage[$index % count($tonnage)],
                    'message' => 'Mohon informasi harga, spesifikasi, dan jadwal kirim terdekat.',
                    'status' => $status,
                    'is_read' => $status !== RfqStatus::New,
                    'locale' => $locale,
                    'source_url' => 'https://nusacarbon.test/products',
                    'ip_address' => '203.0.113.'.($index + 10),
                    'user_agent' => 'Mozilla/5.0',
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ],
            );
        }
    }
}
