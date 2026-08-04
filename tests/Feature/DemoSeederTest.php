<?php

declare(strict_types=1);

use App\Models\ContactMessage;
use App\Models\Product;
use App\Models\RfqNote;
use App\Models\RfqRequest;
use App\Models\User;
use App\Support\Demo\DemoPdf;
use Database\Seeders\DemoInboxSeeder;
use Database\Seeders\DemoSeeder;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');

    // Catatan tindak lanjut RFQ butuh satu pengguna sebagai penulisnya.
    User::factory()->create();
});

test('seeder kotak masuk mengisi tiga puluh permintaan dan pesan kontak', function () {
    app(DemoInboxSeeder::class)->run();

    expect(RfqRequest::count())->toBeGreaterThanOrEqual(30)
        ->and(ContactMessage::count())->toBeGreaterThanOrEqual(30)
        // Status baru sengaja tidak diberi catatan, jadi jumlahnya di bawah 30.
        ->and(RfqNote::count())->toBeGreaterThan(0);
});

test('seeder kotak masuk aman dijalankan dua kali', function () {
    app(DemoInboxSeeder::class)->run();
    $first = RfqRequest::count();

    app(DemoInboxSeeder::class)->run();

    expect(RfqRequest::count())->toBe($first);
});

test('demo seeder dilewati di produksi', function () {
    app()->detectEnvironment(fn () => 'production');

    app(DemoSeeder::class)->run();

    expect(Product::count())->toBe(0);
});

test('pdf yang dihasilkan sah dan memuat teksnya', function () {
    (new DemoPdf)->write('demo/coa.pdf', 'CERTIFICATE OF ANALYSIS', 'Batch SP-2409', [
        ['Fixed Carbon', '80 % min'],
    ]);

    $pdf = Storage::disk('public')->get('demo/coa.pdf');

    expect($pdf)->toStartWith('%PDF-1.4')
        ->and($pdf)->toContain('CERTIFICATE OF ANALYSIS')
        ->and($pdf)->toContain('startxref')
        ->and($pdf)->toEndWith('%%EOF');
});
