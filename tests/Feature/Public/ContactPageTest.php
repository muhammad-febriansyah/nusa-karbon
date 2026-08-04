<?php

declare(strict_types=1);

use App\Enums\RfqStatus;
use App\Models\Product;
use App\Models\RfqRequest;
use App\Notifications\NewRfqReceived;
use App\Services\SettingService;
use Database\Seeders\SiteSettingSeeder;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed(SiteSettingSeeder::class);
    RateLimiter::clear('rfq:127.0.0.1');
});

/**
 * @return array<string, string>
 */
function rfqPayload(array $overrides = []): array
{
    return [
        'company_name' => 'Al Noor Trading',
        'contact_name' => 'Yusuf',
        'email' => 'yusuf@example.com',
        'country_code' => 'SA',
        ...$overrides,
    ];
}

test('halaman kontak mengirim pilihan produk, kontainer, dan incoterms', function () {
    Product::factory()->create(['is_active' => true]);
    Product::factory()->create(['is_active' => false]);

    $this->get(route('public.contact'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('public/contact')
            ->has('products', 1)
            ->has('incoterms'),
        );
});

test('permintaan penawaran tersimpan dengan nomor referensi dan status baru', function () {
    Notification::fake();

    $this->post(route('public.contact.store'), rfqPayload())
        ->assertRedirect()
        ->assertSessionHas('success');

    $rfq = RfqRequest::query()->firstOrFail();

    expect($rfq->company_name)->toBe('Al Noor Trading')
        ->and($rfq->status)->toBe(RfqStatus::New)
        ->and($rfq->is_read)->toBeFalse()
        // Nomor referensi dibuat server, bukan dikirim form.
        ->and($rfq->reference_no)->toStartWith('RFQ-');
});

test('nomor referensi dikirim ke halaman setelah permintaan terkirim', function () {
    Notification::fake();

    $this->post(route('public.contact.store'), rfqPayload());

    $reference = RfqRequest::query()->value('reference_no');

    // Pengunjung mendarat kembali di formulir lewat GET; nomornya ikut sekali.
    $this->get(route('public.contact'))
        ->assertInertia(fn (Assert $page) => $page->where('reference', $reference));

    // Flash hanya bertahan satu request — kunjungan berikutnya bersih kembali.
    $this->get(route('public.contact'))
        ->assertInertia(fn (Assert $page) => $page->where('reference', null));
});

test('kode negara disimpan dalam huruf besar apa pun cara pengunjung mengetiknya', function () {
    Notification::fake();

    $this->post(route('public.contact.store'), rfqPayload(['country_code' => 'sa']))
        ->assertSessionHasNoErrors();

    expect(RfqRequest::query()->value('country_code'))->toBe('SA');
});

test('pemberitahuan dikirim ke alamat pada pengaturan situs', function () {
    Notification::fake();

    $this->post(route('public.contact.store'), rfqPayload());

    Notification::assertSentOnDemand(NewRfqReceived::class);
});

test('umpan robot yang terisi ditolak sebelum menyentuh basis data', function () {
    Notification::fake();

    $this->post(route('public.contact.store'), rfqPayload([
        // Bidang ini disembunyikan dari pengunjung; hanya robot yang mengisinya.
        'website' => 'https://spam.example.com',
    ]))->assertSessionHasErrors('website');

    expect(RfqRequest::query()->count())->toBe(0);
});

test('isian wajib divalidasi', function () {
    $this->post(route('public.contact.store'), [])
        ->assertSessionHasErrors([
            'company_name',
            'contact_name',
            'email',
            'country_code',
        ]);

    expect(RfqRequest::query()->count())->toBe(0);
});

test('pengiriman melebihi batas per jam ditolak', function () {
    Notification::fake();

    $limit = (int) app(SettingService::class)->get('rfq_rate_limit_per_hour');

    // Batas bawaan pengaturan situs adalah lima per jam per alamat IP.
    for ($i = 0; $i < $limit; $i++) {
        $this->post(route('public.contact.store'), rfqPayload([
            'email' => "buyer{$i}@example.com",
        ]))->assertSessionHasNoErrors();
    }

    $this->post(route('public.contact.store'), rfqPayload([
        'email' => 'buyer-terakhir@example.com',
    ]))->assertSessionHasErrors('email');

    expect(RfqRequest::query()->count())->toBe($limit);
});

test('produk nonaktif tidak bisa dilampirkan ke permintaan', function () {
    Notification::fake();

    $nonaktif = Product::factory()->create(['is_active' => false]);

    $this->post(route('public.contact.store'), rfqPayload([
        'product_id' => $nonaktif->id,
    ]))->assertSessionHasErrors('product_id');

    expect(RfqRequest::query()->count())->toBe(0);
});

test('halaman kontak mengirim daftar negara yang sudah diterjemahkan dan diurutkan', function () {
    $this->get(route('public.contact'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            // Dua ratus lima puluh: seluruh kode ISO 3166-1 yang masih terpakai
            // plus XK. Kode usang seperti SU dan pengelompokan seperti EU tidak
            // pernah ikut — keduanya tampak sah di daftar tetapi bukan tujuan
            // pengiriman yang bisa dipakai.
            ->has('countries', 250)
            ->where('countries.0.label', 'Afganistan')
            ->has('countries.0.value'),
        );
});

test('negara tujuan hanya menerima kode yang ada di daftar', function () {
    $this->post(route('public.contact.store'), rfqPayload(['country_code' => 'XX']))
        ->assertSessionHasErrors(['country_code']);

    expect(RfqRequest::query()->count())->toBe(0);
});

test('kode negara usang ditolak meski dua huruf dan dikenal ICU', function () {
    // `SU` (Uni Soviet) lolos aturan `alpha` lama dan tersimpan sebagai negara
    // tujuan yang sah, padahal tidak ada kapal yang bisa berangkat ke sana.
    $this->post(route('public.contact.store'), rfqPayload(['country_code' => 'SU']))
        ->assertSessionHasErrors(['country_code']);
});
