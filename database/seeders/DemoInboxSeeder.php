<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Incoterm;
use App\Enums\RfqStatus;
use App\Models\ContactMessage;
use App\Models\ContainerType;
use App\Models\DocumentDownload;
use App\Models\Product;
use App\Models\ProductDocument;
use App\Models\RfqNote;
use App\Models\RfqRequest;
use App\Models\User;
use App\Support\Locale;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Kotak masuk demo: permintaan penawaran beserta catatan tindak lanjut, pesan
 * kontak umum, dan riwayat unduhan dokumen.
 *
 * Data ini normalnya lahir dari form publik. Seeder-nya dipisah supaya tidak
 * ikut `DatabaseSeeder` dan tidak pernah masuk produksi.
 */
final class DemoInboxSeeder extends Seeder
{
    public function run(): void
    {
        $this->rfqRequests();
        $this->contactMessages();
        $this->documentDownloads();
    }

    private function rfqRequests(): void
    {
        $locale = Locale::default();
        $productIds = Product::query()->calculable()->pluck('id')->all();
        $containerIds = ContainerType::query()->where('is_active', true)->pluck('id')->all();
        $userId = User::query()->value('id');

        $buyers = [
            ['Al Noor Trading LLC', 'AE', 'Jebel Ali', RfqStatus::Won, 0],
            ['Gulf Charcoal Supply', 'AE', 'Dubai', RfqStatus::Negotiating, 2],
            ['Emirates Shisha House', 'AE', 'Sharjah', RfqStatus::Quoted, 4],
            ['Riyadh Shisha House', 'SA', 'Dammam', RfqStatus::Quoted, 5],
            ['Jeddah Coal Trading', 'SA', 'Jeddah', RfqStatus::Won, 7],
            ['Doha Coals WLL', 'QA', 'Hamad Port', RfqStatus::Contacted, 8],
            ['Kuwait Premium Coals', 'KW', 'Shuwaikh', RfqStatus::Won, 11],
            ['Muscat Charcoal Co', 'OM', 'Sohar', RfqStatus::New, 13],
            ['Istanbul Komur', 'TR', 'Mersin', RfqStatus::Won, 14],
            ['Ankara Charcoal Import', 'TR', 'Izmir', RfqStatus::Negotiating, 17],
            ['Cairo Shisha Co', 'EG', 'Alexandria', RfqStatus::New, 19],
            ['Amman Trading House', 'JO', 'Aqaba', RfqStatus::Quoted, 21],
            ['Beirut Coal Import', 'LB', 'Beirut', RfqStatus::Lost, 23],
            ['Berlin Grill Supply', 'DE', 'Hamburg', RfqStatus::Lost, 26],
            ['Munich BBQ Wholesale', 'DE', 'Bremerhaven', RfqStatus::Quoted, 28],
            ['Rotterdam BBQ Import', 'NL', 'Rotterdam', RfqStatus::Quoted, 31],
            ['Antwerp Charcoal BV', 'BE', 'Antwerp', RfqStatus::Contacted, 34],
            ['Warsaw Coal Trade', 'PL', 'Gdansk', RfqStatus::Contacted, 38],
            ['London Shisha Imports', 'GB', 'Felixstowe', RfqStatus::New, 41],
            ['Moscow Charcoal Group', 'RU', 'Novorossiysk', RfqStatus::New, 45],
            ['Kyiv Grill Supply', 'UA', 'Odesa', RfqStatus::Lost, 49],
            ['Madrid Carbon SL', 'ES', 'Valencia', RfqStatus::Negotiating, 53],
            ['Milano Carbone SRL', 'IT', 'Genoa', RfqStatus::Quoted, 58],
            ['Seoul Hookah Lounge', 'KR', 'Busan', RfqStatus::Negotiating, 63],
            ['Osaka Yakiniku Supply', 'JP', 'Kobe', RfqStatus::Won, 68],
            ['Mumbai Coal Exports', 'IN', 'Nhava Sheva', RfqStatus::Lost, 75],
            ['Singapore Charcoal Pte', 'SG', 'Singapore', RfqStatus::Contacted, 82],
            ['Sydney BBQ Distributors', 'AU', 'Sydney', RfqStatus::New, 90],
            ['Los Angeles Grill Co', 'US', 'Long Beach', RfqStatus::Quoted, 104],
            ['Cheap Coal Offer', 'CN', 'Ningbo', RfqStatus::Spam, 6],
        ];

        $tonnage = ['54 MT', '3 x 40HC per bulan', '2 kontainer per bulan', '1 x 20FT trial', '18 ton/bulan', '100 MT per kuartal'];

        foreach ($buyers as $index => [$company, $country, $port, $status, $daysAgo]) {
            $createdAt = now()->subDays($daysAgo);
            $reference = 'RFQ-'.$createdAt->format('Ymd').'-'.str_pad((string) ($index + 101), 4, '0', STR_PAD_LEFT);

            $rfq = RfqRequest::query()->updateOrCreate(
                ['reference_no' => $reference],
                [
                    'product_id' => $productIds === [] ? null : $productIds[$index % count($productIds)],
                    'container_type_id' => $containerIds === [] ? null : $containerIds[$index % count($containerIds)],
                    'company_name' => $company,
                    'contact_name' => 'Purchasing Manager',
                    'email' => 'purchasing@'.Str::slug($company).'.com',
                    'phone' => '+9715'.str_pad((string) (($index + 3) * 7919 % 10000000), 7, '0', STR_PAD_LEFT),
                    'whatsapp' => '+9715'.str_pad((string) (($index + 3) * 7919 % 10000000), 7, '0', STR_PAD_LEFT),
                    'country_code' => $country,
                    'incoterms' => $index % 3 === 0 ? Incoterm::Cif : Incoterm::Fob,
                    'port_of_destination' => $port,
                    'monthly_tonnage' => $tonnage[$index % count($tonnage)],
                    'packaging_notes' => $index % 4 === 0
                        ? 'Inner box 1 kg dengan label merek kami, master carton polos.'
                        : null,
                    'message' => 'Mohon informasi harga, spesifikasi teknis, dan jadwal kirim terdekat untuk tujuan '.$port.'.',
                    'status' => $status,
                    'is_read' => $status !== RfqStatus::New,
                    'locale' => $locale,
                    'source_url' => 'https://nusacarbon.test/products',
                    'ip_address' => '203.0.113.'.($index + 10),
                    'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ],
            );

            if ($userId === null || $rfq->notes()->exists()) {
                continue;
            }

            foreach ($this->notesFor($status) as $offset => $note) {
                RfqNote::query()->create([
                    'rfq_request_id' => $rfq->id,
                    'user_id' => $userId,
                    'note' => $note,
                    'created_at' => $createdAt->copy()->addDays($offset + 1),
                    'updated_at' => $createdAt->copy()->addDays($offset + 1),
                ]);
            }
        }
    }

    /**
     * Catatan mengikuti status supaya riwayatnya masuk akal saat dibuka.
     *
     * @return array<int, string>
     */
    private function notesFor(RfqStatus $status): array
    {
        return match ($status) {
            RfqStatus::New => [],
            RfqStatus::Contacted => ['Sudah dibalas lewat email, menunggu konfirmasi volume bulanan.'],
            RfqStatus::Quoted => [
                'Sudah dibalas lewat email, menunggu konfirmasi volume bulanan.',
                'Penawaran FOB Surabaya dikirim, berlaku 14 hari.',
            ],
            RfqStatus::Negotiating => [
                'Penawaran FOB Surabaya dikirim, berlaku 14 hari.',
                'Buyer minta harga CIF dan sampel dua bentuk. Sampel dikirim minggu ini.',
            ],
            RfqStatus::Won => [
                'Penawaran dikirim, buyer minta jadwal produksi.',
                'Kontrak trial satu kontainer 20FT disepakati.',
                'Uang muka 30 persen diterima, produksi masuk antrean.',
            ],
            RfqStatus::Lost => [
                'Penawaran dikirim, buyer membandingkan dengan pemasok lain.',
                'Buyer memilih pemasok lain karena harga. Tetap dipantau untuk kuartal berikutnya.',
            ],
            RfqStatus::Spam => ['Isi pesan tidak relevan dan alamat email tidak bisa diverifikasi.'],
        };
    }

    private function contactMessages(): void
    {
        $locale = Locale::default();

        $messages = [
            ['Ahmad Fauzi', 'Permintaan katalog produk', 'Mohon dikirim katalog lengkap beserta daftar harga FOB.'],
            ['Sarah Wilson', 'Sample request', 'We would like to request samples of cube 26 mm and hexagonal 22 mm.'],
            ['Mohammed Al Rashid', 'Partnership inquiry', 'We are a distributor in Dubai looking for a long term supplier.'],
            ['Budi Santoso', 'Lowongan kerja', 'Saya ingin menanyakan lowongan di bagian produksi.'],
            ['Elena Petrova', 'Shipping to Novorossiysk', 'Do you ship directly to Novorossiysk or via transshipment?'],
            ['Hans Mueller', 'BBQ briquette specification', 'Please share the specification sheet for pillow shape briquettes.'],
            ['Fatima Zahra', 'Halal certificate', 'Could you send a copy of your halal certificate?'],
            ['James Carter', 'Private label packaging', 'What is the minimum order for private label packaging?'],
            ['Rizal Hakim', 'Kunjungan pabrik', 'Kami ingin menjadwalkan kunjungan ke pabrik bulan depan.'],
            ['Yuki Tanaka', 'Yakiniku charcoal', 'Do you produce charcoal suitable for yakiniku restaurants?'],
            ['Carlos Mendez', 'Payment terms', 'Do you accept L/C at sight for first order?'],
            ['Nguyen Van An', 'Activated carbon', 'We are interested in granular activated carbon, please advise.'],
            ['Olga Ivanova', 'Price list request', 'Please send your latest FOB price list for shisha briquettes.'],
            ['David Kim', 'Container loading', 'How many cartons fit in a 40HC for cube 26 mm?'],
            ['Siti Aminah', 'Kerja sama distributor', 'Kami distributor lokal, ingin menanyakan skema keagenan.'],
            ['Marco Rossi', 'Ash content question', 'What is the guaranteed maximum ash content for premium grade?'],
            ['Aisha Rahman', 'Delivery time', 'What is the lead time after down payment?'],
            ['Peter Novak', 'Fumigation certificate', 'Is the ISPM 15 fumigation certificate included?'],
            ['Wahyu Setiawan', 'Penawaran bahan baku', 'Kami pemasok batok kelapa dari Sulawesi, ingin menawarkan kerja sama.'],
            ['Laura Sanchez', 'Retail packaging', 'Do you offer 3 kg retail bags with our design?'],
            ['Ali Hassan', 'Verification of representative', 'Is Mr. Kareem an authorised representative in Egypt?'],
            ['Chen Wei', 'OEM inquiry', 'We need OEM production of 5 containers per month.'],
            ['Dwi Lestari', 'Permintaan magang', 'Apakah menerima mahasiswa magang di bagian QC?'],
            ['Thomas Berg', 'Sustainability report', 'Do you have a carbon footprint statement for your product?'],
            ['Nadia Karim', 'Complaint about batch', 'Batch SP-2431 had higher moisture than stated in the COA.'],
            ['Ryan Cooper', 'Distributor in Australia', 'Looking for an exclusive distribution agreement for Australia.'],
            ['Indra Permana', 'Harga lokal', 'Apakah menjual untuk pasar dalam negeri?'],
            ['Amina Toure', 'Shipping to Lagos', 'Please quote CIF Lagos for one 40HC of BBQ briquettes.'],
            ['Viktor Sokolov', 'Long term contract', 'We want to lock the price for twelve months.'],
            ['Putri Maharani', 'Undangan pameran', 'Kami mengundang Nusacarbon untuk pameran dagang di Surabaya.'],
        ];

        foreach ($messages as $index => [$name, $subject, $body]) {
            $createdAt = now()->subDays($index * 2 + 1);

            ContactMessage::query()->updateOrCreate(
                ['email' => Str::slug($name, '.').'@example.com', 'subject' => $subject],
                [
                    'name' => $name,
                    'phone' => '+62 812 '.str_pad((string) (($index + 1) * 4567 % 100000000), 8, '0', STR_PAD_LEFT),
                    'message' => $body,
                    'is_read' => $index % 3 !== 0,
                    'is_spam' => $index === 18,
                    'locale' => $locale,
                    'ip_address' => '198.51.100.'.($index + 5),
                    'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)',
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ],
            );
        }
    }

    /**
     * Riwayat unduhan supaya penghitung pada dokumen produk punya asal usul.
     */
    private function documentDownloads(): void
    {
        if (DocumentDownload::query()->exists()) {
            return;
        }

        $countries = ['AE', 'SA', 'QA', 'TR', 'DE', 'NL', 'GB', 'RU', 'KR', 'JP', 'IN', 'US'];
        $documents = ProductDocument::query()->limit(30)->get();

        foreach ($documents as $index => $document) {
            for ($visit = 0; $visit < 3; $visit++) {
                DocumentDownload::query()->create([
                    'product_document_id' => $document->id,
                    'ip_address' => '192.0.2.'.((($index * 3) + $visit) % 250 + 1),
                    'country_code' => $countries[($index + $visit) % count($countries)],
                    'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                    'referer' => 'https://nusacarbon.test/products',
                    'locale' => Locale::default(),
                    'created_at' => now()->subDays(($index + $visit) % 60),
                ]);
            }
        }
    }
}
