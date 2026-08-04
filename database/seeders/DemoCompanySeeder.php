<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Certificate;
use App\Models\Feature;
use App\Models\ProductionStep;
use App\Models\QcProtocol;
use App\Models\Representative;
use App\Models\ShippingPartner;
use App\Models\TeamMember;
use App\Support\Demo\DemoAssetFetcher;
use App\Support\Demo\DemoPdf;
use App\Support\Locale;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Profil perusahaan demo: sertifikat, perwakilan resmi, proses produksi,
 * protokol QC, keunggulan, tim, dan mitra pelayaran.
 */
final class DemoCompanySeeder extends Seeder
{
    public function __construct(
        private readonly DemoAssetFetcher $assets = new DemoAssetFetcher,
        private readonly DemoPdf $pdf = new DemoPdf,
    ) {}

    public function run(): void
    {
        $this->certificates();
        $this->representatives();
        $this->productionSteps();
        $this->qcProtocols();
        $this->features();
        $this->teamMembers();
        $this->shippingPartners();
    }

    private function certificates(): void
    {
        $locale = Locale::default();

        $certificates = [
            ['ISO 9001:2015', 'TUV Rheinland', 36],
            ['ISO 14001:2015 Environmental Management', 'TUV Rheinland', 36],
            ['ISO 45001:2018 Occupational Health & Safety', 'SGS Indonesia', 36],
            ['Certificate of Origin', 'Kementerian Perdagangan RI', 12],
            ['Certificate of Origin (Form D)', 'Kementerian Perdagangan RI', 12],
            ['Phytosanitary Certificate', 'Badan Karantina Indonesia', 6],
            ['Fumigation Certificate', 'PT Sucofindo', 6],
            ['SGS Quality Inspection', 'SGS Indonesia', 12],
            ['Intertek Loading Supervision', 'Intertek Indonesia', 12],
            ['Halal Certificate', 'BPJPH', 24],
            ['SNI Product Certificate', 'BSN', 36],
            ['Legalitas Ekspor (NIB)', 'OSS Kementerian Investasi', 60],
            ['Surat Keterangan Asal Barang', 'Dinas Perindustrian', 12],
            ['REACH Compliance Statement', 'Eurofins', 24],
            ['RoHS Compliance Statement', 'Eurofins', 24],
            ['FSC Chain of Custody', 'Control Union', 36],
            ['Verifikasi Legalitas Kayu (SVLK)', 'PT Mutuagung Lestari', 36],
            ['Food Contact Material Test', 'SGS Indonesia', 24],
            ['Heavy Metal Test Report', 'Saraswanti Indo Genetech', 12],
            ['Proximate Analysis Report', 'Sucofindo Laboratory', 12],
            ['Calorific Value Test Report', 'Sucofindo Laboratory', 12],
            ['Burning Time Test Report', 'Nusacarbon QC Laboratory', 12],
            ['Ash Content Test Report', 'Nusacarbon QC Laboratory', 12],
            ['Moisture Content Test Report', 'Nusacarbon QC Laboratory', 12],
            ['Marine Insurance Policy', 'PT Asuransi Ekspor Indonesia', 12],
            ['Export Registration Certificate', 'Kementerian Perdagangan RI', 24],
            ['Business Licence (SIUP)', 'Pemerintah Kabupaten', 60],
            ['Tax Registration (NPWP)', 'Direktorat Jenderal Pajak', 120],
            ['GMP Certificate', 'Control Union', 24],
            ['Carbon Footprint Statement', 'Control Union', 24],
        ];

        foreach ($certificates as $position => [$name, $issuer, $validMonths]) {
            $issuedAt = Carbon::now()->subMonths($position % 18)->startOfMonth();

            $certificate = Certificate::query()->updateOrCreate(
                ['name->'.$locale => $name],
                [
                    'name' => [$locale => $name],
                    'issuer' => $issuer,
                    'certificate_number' => sprintf('NC/%s/%04d', $issuedAt->format('Y'), $position + 1),
                    'description' => [$locale => "Diterbitkan oleh {$issuer} dan diperbarui setiap {$validMonths} bulan."],
                    'issued_at' => $issuedAt,
                    'expires_at' => $issuedAt->copy()->addMonths($validMonths),
                    'show_in_trust_bar' => $position < 6,
                    'sort_order' => $position,
                    'is_active' => true,
                ],
            );

            if ($certificate->logo_path === null) {
                $certificate->forceFill([
                    'logo_path' => $this->assets->logo($issuer, 'certificates', 'logo-'.Str::slug($issuer).'-'.$position),
                ])->save();
            }

            if ($certificate->file_path === null) {
                $path = 'certificates/'.Str::slug($name).'.pdf';

                $this->pdf->write(
                    $path,
                    Str::upper($name),
                    'Diterbitkan oleh '.$issuer,
                    [
                        ['Certificate Number', (string) $certificate->certificate_number],
                        ['Holder', 'PT Nusa Carbon Indonesia'],
                        ['Scope', 'Coconut shell charcoal briquette manufacturing'],
                        ['Issued On', $issuedAt->format('d F Y')],
                        ['Valid Until', $issuedAt->copy()->addMonths($validMonths)->format('d F Y')],
                    ],
                );

                $certificate->forceFill(['file_path' => $path])->save();
            }
        }
    }

    private function representatives(): void
    {
        $locale = Locale::default();

        $people = [
            ['Adi Wicaksono', 'Export Sales Manager', 'MENA', 'AE', '+971 4 555 0110'],
            ['Rina Kusuma', 'Business Development', 'MENA', 'SA', '+966 11 555 0120'],
            ['Bayu Pratama', 'Regional Sales', 'MENA', 'QA', '+974 4 555 0130'],
            ['Dewi Anggraini', 'Regional Sales', 'MENA', 'KW', '+965 2 555 0140'],
            ['Fajar Nugroho', 'Key Account Manager', 'MENA', 'OM', '+968 24 555 0150'],
            ['Sinta Larasati', 'Export Sales', 'MENA', 'EG', '+20 2 555 0160'],
            ['Hendra Gunawan', 'Export Sales', 'MENA', 'JO', '+962 6 555 0170'],
            ['Maya Puspita', 'Regional Manager', 'Europe', 'DE', '+49 40 555 0180'],
            ['Rizky Ramadhan', 'Export Sales', 'Europe', 'NL', '+31 10 555 0190'],
            ['Nadia Safitri', 'Export Sales', 'Europe', 'PL', '+48 58 555 0200'],
            ['Yoga Saputra', 'Key Account Manager', 'Europe', 'GB', '+44 20 555 0210'],
            ['Intan Permata', 'Business Development', 'Europe', 'TR', '+90 324 555 0220'],
            ['Arif Setiawan', 'Export Sales', 'Europe', 'RU', '+7 495 555 0230'],
            ['Laras Wulandari', 'Export Sales', 'Europe', 'UA', '+380 48 555 0240'],
            ['Bagus Santosa', 'Regional Manager', 'Asia', 'KR', '+82 51 555 0250'],
            ['Putri Handayani', 'Export Sales', 'Asia', 'JP', '+81 3 555 0260'],
            ['Dimas Prasetyo', 'Export Sales', 'Asia', 'CN', '+86 574 555 0270'],
            ['Ayu Lestari', 'Key Account Manager', 'Asia', 'IN', '+91 22 555 0280'],
            ['Reza Maulana', 'Export Sales', 'Asia', 'SG', '+65 6555 0290'],
            ['Kirana Dewi', 'Export Sales', 'Asia', 'MY', '+60 3 555 0300'],
            ['Galih Wibowo', 'Regional Manager', 'Americas', 'US', '+1 213 555 0310'],
            ['Sari Melati', 'Export Sales', 'Americas', 'CA', '+1 604 555 0320'],
            ['Aditya Ramadhan', 'Export Sales', 'Americas', 'BR', '+55 11 555 0330'],
            ['Tari Anjani', 'Export Sales', 'Americas', 'MX', '+52 55 555 0340'],
            ['Bimo Aryo', 'Regional Manager', 'Africa', 'ZA', '+27 21 555 0350'],
            ['Wulan Cahyani', 'Export Sales', 'Africa', 'NG', '+234 1 555 0360'],
            ['Panji Nugraha', 'Export Sales', 'Africa', 'KE', '+254 20 555 0370'],
            ['Anisa Rahma', 'Customer Success', 'Asia', 'ID', '+62 21 555 0380'],
            ['Rangga Aditya', 'Documentation Officer', 'Asia', 'ID', '+62 21 555 0390'],
            ['Salsabila Putri', 'Export Documentation', 'Asia', 'ID', '+62 21 555 0400'],
        ];

        foreach ($people as $position => [$name, $role, $region, $country, $phone]) {
            $email = Str::slug($name, '.').'@nusacarbon.com';

            // Dikunci pada nama supaya perwakilan bawaan `RepresentativeSeeder`
            // diperbarui, bukan digandakan dengan alamat email berbeda.
            $representative = Representative::query()->updateOrCreate(
                ['name' => $name],
                [
                    'email' => $email,
                    'position' => [$locale => $role],
                    'region' => $region,
                    'phone' => $phone,
                    'whatsapp' => $phone,
                    'started_at' => Carbon::now()->subYears(1)->subDays($position * 11),
                    // Dua perwakilan lama sengaja dinonaktifkan supaya halaman
                    // verifikasi punya contoh "sudah tidak mewakili kami".
                    'ended_at' => $position >= 28 ? Carbon::now()->subDays(30) : null,
                    'sort_order' => $position,
                    'is_active' => $position < 28,
                ],
            );

            if ($representative->photo_path === null) {
                $representative->forceFill([
                    'photo_path' => $this->assets->portrait($name, 'representatives', 'foto-'.Str::slug($name)),
                ])->save();
            }
        }
    }

    private function productionSteps(): void
    {
        $steps = [
            ['Seleksi Batok Kelapa', 'Batok kelapa tua disortir manual, dipisahkan dari sabut dan batok muda yang kadar airnya masih tinggi.'],
            ['Penjemuran Awal', 'Batok dijemur 2–3 hari sampai kadar air turun di bawah 12 persen sebelum masuk tungku.'],
            ['Karbonisasi', 'Batok dibakar dalam tungku tertutup pada suhu terkendali sehingga menghasilkan arang dengan fixed carbon tinggi.'],
            ['Pendinginan Arang', 'Arang didiamkan sampai suhunya turun agar tidak memicu kebakaran di gudang bahan.'],
            ['Penyaringan Kasar', 'Arang disaring untuk memisahkan bongkahan besar, arang setengah matang, dan abu.'],
            ['Penggilingan', 'Arang digiling menjadi serbuk halus dengan ukuran mesh seragam.'],
            ['Pengayakan Mesh', 'Serbuk diayak bertingkat supaya ukuran partikelnya konsisten antar batch.'],
            ['Pencampuran Perekat', 'Serbuk dicampur perekat tepung tapioka food grade dengan takaran tetap.'],
            ['Pengadukan Adonan', 'Adonan diaduk sampai homogen agar kepadatan briket merata.'],
            ['Pencetakan Briket', 'Adonan dipres mesin cetak sesuai bentuk pesanan: cube, hexagonal, finger, atau pillow.'],
            ['Pemotongan Ukuran', 'Briket dipotong sesuai ukuran milimeter yang diminta buyer.'],
            ['Pengeringan Oven Tahap 1', 'Briket masuk oven suhu rendah selama 12 jam untuk menguapkan air permukaan.'],
            ['Pengeringan Oven Tahap 2', 'Suhu dinaikkan bertahap sampai kadar air stabil di bawah 5 persen.'],
            ['Pendinginan Briket', 'Briket didinginkan di rak terbuka sebelum ditimbang agar tidak berkeringat dalam kemasan.'],
            ['Sortir Visual', 'Briket cacat, retak, atau tidak seragam disingkirkan dari jalur kemas.'],
            ['Uji Kadar Air', 'Sampel tiap batch diuji dengan moisture analyzer di laboratorium internal.'],
            ['Uji Kadar Abu', 'Sampel dibakar di tanur untuk mengukur sisa abu.'],
            ['Uji Nilai Kalor', 'Nilai kalor diukur dengan bomb calorimeter dan dicatat di COA.'],
            ['Uji Lama Bakar', 'Briket dinyalakan pada kondisi standar untuk mengukur durasi bakar.'],
            ['Uji Bau dan Asap', 'Briket dinyalakan di ruang uji untuk memastikan tidak berbau dan minim asap.'],
            ['Penimbangan Inner Box', 'Briket ditimbang per inner box dengan toleransi berat yang ketat.'],
            ['Pengemasan Inner Box', 'Inner box ditutup dan diberi label sesuai desain buyer.'],
            ['Pengemasan Master Carton', 'Inner box disusun ke master carton, jumlah per karton dicatat di packing list.'],
            ['Penandaan Karton', 'Karton diberi marking: nama produk, batch, berat bersih, dan negara asal.'],
            ['Palletisasi', 'Karton disusun di atas pallet kayu yang sudah difumigasi.'],
            ['Shrink Wrapping', 'Pallet dibungkus plastik agar stabil selama perjalanan laut.'],
            ['Inspeksi Pra-Muat', 'Surveyor independen memeriksa jumlah, berat, dan kondisi kemasan sebelum stuffing.'],
            ['Stuffing Kontainer', 'Pallet dimasukkan ke kontainer dengan pola muat yang meminimalkan ruang kosong.'],
            ['Penyegelan Kontainer', 'Kontainer disegel dan nomor segel dicatat pada dokumen pengapalan.'],
            ['Serah Terima Dokumen', 'COA, packing list, dan sertifikat asal dikirim ke buyer sebelum kapal berangkat.'],
        ];

        $this->sortableWithImage(ProductionStep::class, $steps, 'factory', 'production-steps');
    }

    private function qcProtocols(): void
    {
        $protocols = [
            ['Kadar Air (Moisture)', 'Diukur dengan moisture analyzer pada tiap batch.', 'Maks 5%'],
            ['Kadar Abu (Ash Content)', 'Sampel dibakar di tanur 750 derajat Celsius.', 'Maks 2,5%'],
            ['Fixed Carbon', 'Dihitung dari analisis proksimat.', 'Min 78%'],
            ['Zat Terbang (Volatile Matter)', 'Diukur pada suhu 900 derajat Celsius.', 'Maks 15%'],
            ['Nilai Kalor', 'Diuji dengan bomb calorimeter.', 'Min 7.000 kcal/kg'],
            ['Lama Bakar', 'Diuji pada kondisi ruang standar tanpa kipas.', 'Min 120 menit'],
            ['Waktu Menyala', 'Waktu sampai briket menyala penuh.', 'Maks 8 menit'],
            ['Kadar Sulfur', 'Diuji di laboratorium eksternal per kuartal.', 'Maks 0,1%'],
            ['Uji Bau', 'Panel uji mencium asap pada 5 menit pertama.', 'Tanpa bau'],
            ['Uji Asap', 'Diamati visual pada ruang uji berventilasi.', 'Minim asap'],
            ['Uji Percikan', 'Briket diamati selama 30 menit pertama.', 'Tanpa percikan'],
            ['Kepadatan (Density)', 'Diukur dengan metode perpindahan volume.', 'Min 1,0 g/cm3'],
            ['Kekerasan Briket', 'Uji jatuh dari ketinggian 1 meter.', 'Tidak pecah'],
            ['Keseragaman Ukuran', 'Diukur dengan jangka sorong pada 20 sampel.', 'Toleransi 0,5 mm'],
            ['Keseragaman Berat', 'Ditimbang pada 20 sampel per batch.', 'Toleransi 2%'],
            ['Sisa Abu Putih', 'Diamati setelah pembakaran penuh.', 'Abu putih merata'],
            ['Kadar Debu Kemasan', 'Inner box dibuka dan diperiksa sisa debu.', 'Maks 1%'],
            ['Uji Logam Berat', 'Dikirim ke laboratorium terakreditasi tiap semester.', 'Di bawah ambang'],
            ['Uji Perekat Food Grade', 'Sertifikat bahan baku diverifikasi tiap pengadaan.', 'Food grade'],
            ['Kalibrasi Timbangan', 'Dikalibrasi lembaga terakreditasi tiap 6 bulan.', 'Terkalibrasi'],
            ['Kalibrasi Oven', 'Sensor suhu diverifikasi tiap bulan.', 'Deviasi maks 2 derajat'],
            ['Kebersihan Jalur Produksi', 'Diperiksa tiap pergantian shift.', 'Bersih'],
            ['Kondisi Gudang Bahan', 'Kelembapan gudang dicatat harian.', 'Maks 65% RH'],
            ['Kondisi Gudang Jadi', 'Suhu dan kelembapan dipantau harian.', 'Maks 60% RH'],
            ['Integritas Kemasan', 'Karton diperiksa sebelum palletisasi.', 'Tanpa sobek'],
            ['Akurasi Label', 'Label dicocokkan dengan packing list.', '100% sesuai'],
            ['Verifikasi Batch Number', 'Nomor batch dicocokkan dengan catatan produksi.', 'Sesuai'],
            ['Retensi Sampel', 'Sampel tiap batch disimpan 12 bulan.', 'Tersimpan'],
            ['Inspeksi Pra-Muat', 'Surveyor independen memeriksa sebelum stuffing.', 'Lolos'],
            ['Audit Internal Bulanan', 'Seluruh protokol ditinjau tiap bulan.', 'Terdokumentasi'],
        ];

        $locale = Locale::default();

        foreach ($protocols as $position => [$title, $description, $result]) {
            $protocol = QcProtocol::query()->updateOrCreate(
                ['title->'.$locale => $title],
                [
                    'title' => [$locale => $title],
                    'description' => [$locale => $description],
                    'result_label' => [$locale => 'Standar internal'],
                    'result_value' => $result,
                    'sort_order' => $position,
                    'is_active' => true,
                ],
            );

            if ($protocol->image_path === null) {
                $protocol->forceFill([
                    'image_path' => $this->assets->image('laboratory', $position, 'qc-protocols', Str::slug($title)),
                ])->save();
            }
        }
    }

    private function features(): void
    {
        $features = [
            ['Kadar Abu Rendah', 'Rata-rata di bawah 2,5 persen, sisa abu putih dan tidak mengotori shisha.'],
            ['Bakar Bersih Tanpa Bau', 'Perekat food grade, tidak meninggalkan aroma pada tembakau atau daging.'],
            ['Lama Bakar Panjang', 'Briket cube 26 mm bertahan di atas dua jam pada kondisi normal.'],
            ['Nilai Kalor Tinggi', 'Di atas 7.000 kcal/kg untuk kelas premium.'],
            ['Bahan Baku Terlacak', 'Batok kelapa dibeli dari pemasok terdaftar dengan catatan asal.'],
            ['Uji Laboratorium Internal', 'Tiap batch diuji sebelum dikemas, hasilnya masuk COA.'],
            ['COA per Pengiriman', 'Buyer menerima hasil uji batch yang benar-benar dikirim.'],
            ['Kemasan OEM', 'Desain kemasan buyer dicetak dan dipasang di pabrik.'],
            ['Kapasitas Stabil', 'Produksi bulanan cukup untuk kontrak berulang sepanjang tahun.'],
            ['Waktu Produksi Terjadwal', 'Jadwal produksi dikonfirmasi sebelum pembayaran uang muka.'],
            ['Dokumen Ekspor Lengkap', 'COO, phytosanitary, dan fumigasi diurus tim dokumentasi sendiri.'],
            ['Fumigasi Pallet', 'Pallet kayu difumigasi sesuai ISPM 15.'],
            ['Stuffing Diawasi', 'Proses muat direkam foto dan dikirim ke buyer.'],
            ['Segel Kontainer Tercatat', 'Nomor segel dicantumkan di dokumen pengapalan.'],
            ['Mitra Pelayaran Utama', 'Bekerja dengan jalur pelayaran besar untuk jadwal yang dapat diandalkan.'],
            ['Harga FOB dan CIF', 'Penawaran tersedia dalam dua incoterms sesuai kebutuhan buyer.'],
            ['MOQ Fleksibel', 'Trial order satu kontainer 20FT diterima untuk buyer baru.'],
            ['Sample Gratis', 'Sampel dikirim tanpa biaya produk, ongkos kirim ditanggung buyer.'],
            ['Respons Cepat', 'Permintaan penawaran dibalas dalam 24 jam kerja.'],
            ['Tim Multibahasa', 'Komunikasi dalam bahasa Inggris, Arab, dan Rusia.'],
            ['Perwakilan Resmi Terverifikasi', 'Daftar perwakilan bisa dicek di halaman verifikasi.'],
            ['Pembayaran Aman', 'Menerima T/T dan L/C at sight melalui bank devisa.'],
            ['Kontrak Jangka Panjang', 'Harga dikunci untuk kontrak enam sampai dua belas bulan.'],
            ['Konsistensi Antar Batch', 'Formulasi dan mesh dijaga sama sepanjang kontrak.'],
            ['Bebas Percikan', 'Diuji agar tidak memercik saat dinyalakan.'],
            ['Tanpa Bahan Kimia Tambahan', 'Tidak memakai bahan pemercepat nyala.'],
            ['Ramah Lingkungan', 'Memanfaatkan limbah batok kelapa, bukan kayu hutan.'],
            ['Gudang Kering Terkontrol', 'Kelembapan gudang dipantau harian.'],
            ['Pengiriman Tepat Waktu', 'Jadwal keberangkatan dikonfirmasi ulang tiap minggu.'],
            ['Layanan Purna Kirim', 'Keluhan mutu ditindaklanjuti dengan investigasi batch.'],
        ];

        $this->sortableWithImage(Feature::class, $features, 'charcoal', 'features', icon: true);
    }

    private function teamMembers(): void
    {
        $locale = Locale::default();

        $members = [
            ['Hartono Wijaya', 'Direktur Utama'], ['Siti Nurhaliza', 'Direktur Operasional'],
            ['Bambang Susilo', 'Direktur Keuangan'], ['Adi Wicaksono', 'Export Sales Manager'],
            ['Rina Kusuma', 'Business Development Manager'], ['Bayu Pratama', 'Quality Control Lead'],
            ['Dewi Anggraini', 'Production Manager'], ['Fajar Nugroho', 'Plant Supervisor'],
            ['Sinta Larasati', 'Laboratory Analyst'], ['Hendra Gunawan', 'Laboratory Analyst'],
            ['Maya Puspita', 'Export Documentation Officer'], ['Rizky Ramadhan', 'Logistics Coordinator'],
            ['Nadia Safitri', 'Procurement Officer'], ['Yoga Saputra', 'Warehouse Supervisor'],
            ['Intan Permata', 'Marketing Specialist'], ['Arif Setiawan', 'Digital Marketing'],
            ['Laras Wulandari', 'Customer Success'], ['Bagus Santosa', 'Sales Support'],
            ['Putri Handayani', 'Finance Staff'], ['Dimas Prasetyo', 'Accounting Staff'],
            ['Ayu Lestari', 'HR Officer'], ['Reza Maulana', 'IT Support'],
            ['Kirana Dewi', 'Maintenance Engineer'], ['Galih Wibowo', 'Maintenance Technician'],
            ['Sari Melati', 'Packing Supervisor'], ['Aditya Ramadhan', 'Shift Leader Produksi'],
            ['Tari Anjani', 'Shift Leader Produksi'], ['Bimo Aryo', 'Safety Officer'],
            ['Wulan Cahyani', 'Environmental Officer'], ['Panji Nugraha', 'General Affairs'],
        ];

        foreach ($members as $position => [$name, $role]) {
            $member = TeamMember::query()->updateOrCreate(
                ['name' => $name],
                [
                    'position' => [$locale => $role],
                    'bio' => [$locale => "{$name} menangani {$role} di PT Nusa Carbon Indonesia."],
                    'linkedin_url' => 'https://www.linkedin.com/company/nusacarbon',
                    'sort_order' => $position,
                    'is_active' => true,
                ],
            );

            if ($member->photo_path === null) {
                $member->forceFill([
                    'photo_path' => $this->assets->portrait($name, 'team', 'tim-'.Str::slug($name)),
                ])->save();
            }
        }
    }

    private function shippingPartners(): void
    {
        // Kolom ketiga adalah kata kunci pencarian logo di Wikimedia Commons.
        $partners = [
            ['Maersk', 'https://www.maersk.com', 'Maersk Line logo'],
            ['MSC', 'https://www.msc.com', 'Mediterranean Shipping Company logo'],
            ['CMA CGM', 'https://www.cma-cgm.com', 'CMA CGM logo'],
            ['Hapag-Lloyd', 'https://www.hapag-lloyd.com', 'Hapag-Lloyd logo'],
            ['ONE', 'https://www.one-line.com', 'Ocean Network Express logo'],
            ['Evergreen Line', 'https://www.evergreen-line.com', 'Evergreen Marine logo'],
            ['COSCO Shipping', 'https://www.coscoshipping.com', 'COSCO Shipping logo'],
            ['HMM', 'https://www.hmm21.com', 'HMM company logo'],
            ['Yang Ming', 'https://www.yangming.com', 'Yang Ming Marine Transport logo'],
            ['ZIM', 'https://www.zim.com', 'ZIM Integrated Shipping Services logo'],
            ['OOCL', 'https://www.oocl.com', 'Orient Overseas Container Line logo'],
            ['PIL', 'https://www.pilship.com', 'Pacific International Lines logo'],
            ['Wan Hai Lines', 'https://www.wanhai.com', 'Wan Hai Lines logo'],
            ['SITC', 'https://www.sitc.com', 'SITC International logo'],
            ['TS Lines', 'https://www.tslines.com', 'TS Lines logo'],
            ['Sinokor', 'https://www.sinokor.co.kr', 'Sinokor Merchant Marine logo'],
            ['KMTC', 'https://www.ekmtc.com', 'Korea Marine Transport logo'],
            ['Namsung Shipping', 'https://www.namsung.co.kr', 'Namsung Shipping logo'],
            ['Interasia Lines', 'https://www.interasia.cc', 'Interasia Lines logo'],
            ['X-Press Feeders', 'https://www.x-pressfeeders.com', 'X-Press Feeders logo'],
            ['Samudera Shipping', 'https://www.samudera.com', 'Samudera Indonesia logo'],
            ['Meratus Line', 'https://www.meratusline.com', 'Meratus Line logo'],
            ['Tanto Intim Line', 'https://www.tantonet.com', 'Tanto Intim Line logo'],
            ['SPIL', 'https://www.spil.co.id', 'Salam Pacific Indonesia Lines logo'],
            ['Temas Line', 'https://www.temasline.com', 'Temas Line logo'],
            ['Pelni Logistik', 'https://www.pelni.co.id', 'Pelni logo'],
            ['DHL Global Forwarding', 'https://www.dhl.com', 'DHL logo'],
            ['Kuehne+Nagel', 'https://home.kuehne-nagel.com', 'Kuehne Nagel logo'],
            ['DB Schenker', 'https://www.dbschenker.com', 'DB Schenker logo'],
            ['Agility Logistics', 'https://www.agility.com', 'Agility Logistics logo'],
        ];

        foreach ($partners as $position => [$name, $website, $logoQuery]) {
            $partner = ShippingPartner::query()->updateOrCreate(
                ['name' => $name],
                [
                    'website_url' => $website,
                    'sort_order' => $position,
                    'is_active' => true,
                ],
            );

            if ($partner->logo_path === null) {
                $partner->forceFill([
                    'logo_path' => $this->assets->brandLogo($name, $logoQuery, 'shipping-partners', 'logo-'.Str::slug($name)),
                ])->save();
            }
        }
    }

    /**
     * Modul berurut dengan judul, deskripsi, dan satu gambar.
     *
     * @param  class-string<ProductionStep|Feature>  $model
     * @param  array<int, array{0: string, 1: string}>  $rows
     */
    private function sortableWithImage(string $model, array $rows, string $topic, string $directory, bool $icon = false): void
    {
        $locale = Locale::default();

        foreach ($rows as $position => [$title, $description]) {
            $attributes = [
                'title' => [$locale => $title],
                'description' => [$locale => $description],
                'sort_order' => $position,
                'is_active' => true,
            ];

            if ($icon) {
                $attributes['icon'] = 'check';
            }

            /** @var ProductionStep|Feature $record */
            $record = $model::query()->updateOrCreate(['title->'.$locale => $title], $attributes);

            if ($record->image_path === null) {
                $record->forceFill([
                    'image_path' => $this->assets->image($topic, $position, $directory, Str::slug($title)),
                ])->save();
            }
        }
    }
}
